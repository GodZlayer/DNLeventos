<?php

namespace App\Http\Controllers\BackEnd\Event;

use App\Exports\BookingExport;
use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\BasicSettings\MailTemplate;
use App\Models\Earning;
use App\Models\Event;
use App\Models\Event\Booking;
use App\Models\Event\EventContent;
use App\Models\Event\Ticket;
use App\Models\PaymentGateway\OfflineGateway;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventBookingController extends Controller
{
    public function index(Request $request)
    {
        $bookingId = $paymentStatus = null;
        $eventIds = [];
        if ($request->filled('booking_id')) {
            $bookingId = $request['booking_id'];
        }

        if ($request->filled('event_title')) {
            $event_contents = EventContent::where('title', 'like', '%' . $request->event_title . '%')->get();
            foreach ($event_contents as $event_content) {
                if (!in_array($event_content->event_id, $eventIds)) {
                    array_push($eventIds, $event_content->event_id);
                }
            }
        }

        if ($request->filled('status')) {
            $paymentStatus = $request['status'];
        }

        $bookings = Booking::when($bookingId, function ($query) use ($bookingId) {
            return $query->where('booking_id', 'like', '%' . $bookingId . '%');
        })->when($paymentStatus, function ($query, $paymentStatus) {
            return $query->where('paymentStatus', '=', $paymentStatus);
        })
            ->when($eventIds, function ($query) use ($eventIds) {
                return $query->whereIn('event_id', $eventIds);
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('backend.event.booking.index', compact('bookings'));
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->first();

        if ($request['payment_status'] == 'completed') {
            $booking->update(['paymentStatus' => 'completed']);
            Log::info('Pagamento atualizado para completado. ID da reserva: ' . $booking->booking_id);

            $generatedPDFs = $this->generateIndividualTickets($booking);

            if (empty($generatedPDFs)) {
                Log::error('Nenhum PDF foi gerado para a reserva: ' . $booking->booking_id);
                return redirect()->back()->with('error', 'Erro ao gerar os PDFs para os ingressos.');
            }

            $this->sendTicketMails($booking, $generatedPDFs);
        } else {
            Log::info('Pagamento não concluído para a reserva: ' . $booking->booking_id);
        }

        return redirect()->back();
    }

    public function generateIndividualTickets($bookingInfo)
    {
        try {
            // Diretório para armazenar PDFs e QR Codes
            $qrCodeDirectory = public_path('assets/admin/qrcodes/tickets/');
            $pdfDirectory = public_path('assets/admin/file/tickets/');

            // Verificação de diretórios
            if (!file_exists($qrCodeDirectory)) {
                mkdir($qrCodeDirectory, 0775, true);
                Log::info('Diretório de QR Codes criado: ' . $qrCodeDirectory);
            }

            if (!file_exists($pdfDirectory)) {
                mkdir($pdfDirectory, 0775, true);
                Log::info('Diretório de PDFs criado: ' . $pdfDirectory);
            }

            $generatedPDFs = [];

            // Recupera os detalhes dos ingressos comprados no evento com a quantidade
            $tickets = Ticket::where('event_id', $bookingInfo->event_id)->get();

            foreach ($tickets as $ticket) {
                // Gera um QR Code e PDF separado para cada unidade de ingresso comprada
                for ($i = 1; $i <= $bookingInfo->quantity; $i++) {
                    try {
                        // Gera um QR Code exclusivo para cada ingresso usando um identificador único (exemplo: ticket_id + número)
                        $qrCodeId = $ticket->id . '-' . $i;
                        $qrCodePath = $qrCodeDirectory . 'ticket_' . $qrCodeId . '.svg';
                        QrCode::size(200)->generate($qrCodeId, $qrCodePath);
                        Log::info('QR Code gerado: ' . $qrCodePath);

                        // Define o nome e caminho do arquivo PDF para cada ingresso
                        $pdfFileName = 'ticket_' . $qrCodeId . '.pdf';
                        $pdfFilePath = $pdfDirectory . $pdfFileName;

                        // Gera o PDF para cada ingresso com as informações detalhadas
                        PDF::loadView('frontend.event.invoice', [
                            'bookingInfo' => $bookingInfo, // Dados da reserva
                            'ticket' => $ticket,           // Detalhes do ticket
                            'qrCodePath' => $qrCodePath,   // Caminho do QR Code gerado
                            'ticketNumber' => $i,          // Número específico deste ingresso
                            'eventInfo' => EventContent::where('event_id', $bookingInfo->event_id)->first() // Informações do evento
                        ])->save($pdfFilePath);

                        Log::info('PDF gerado para o ingresso ' . $i . ': ' . $pdfFilePath);

                        // Adiciona o caminho do PDF à lista
                        $generatedPDFs[] = $pdfFilePath;
                    } catch (\Exception $e) {
                        Log::error('Erro ao gerar o PDF ou QR Code para o ingresso ' . $i . ': ' . $e->getMessage());
                    }
                }
            }

            return $generatedPDFs;
        } catch (\Exception $e) {
            Log::error('Erro ao gerar os ingressos: ' . $e->getMessage());
            return [];
        }
    }

    public function sendTicketMails($booking, $generatedPDFs)
    {
        try {
            $mail = new PHPMailer(true);
            $info = DB::table('basic_settings')->first();

            $mail->setFrom($info->from_mail, $info->from_name);
            $mail->addAddress($booking->email);

            foreach ($generatedPDFs as $pdf) {
                if (file_exists($pdf)) {
                    $mail->addAttachment($pdf);
                } else {
                    Log::warning('Arquivo PDF não encontrado: ' . $pdf);
                }
            }

            $mail->isHTML(true);
            $mail->Subject = "Ingressos para o evento " . $booking->event->title;
            $mail->Body = "Por favor, encontre os ingressos anexados para o evento que você adquiriu.";

            $mail->send();
            Log::info('E-mail enviado com os ingressos para: ' . $booking->email);
        } catch (Exception $e) {
            Log::error('Erro ao enviar e-mail com ingressos: ' . $e->getMessage());
        }
    }

    public function generateInvoice($bookingInfo)
    {
        $fileName = $bookingInfo->booking_id . '.pdf';
        $directory = public_path('assets/admin/file/invoices/');

        @mkdir($directory, 0775, true);
        @mkdir(public_path('assets/admin/qrcodes/'), 0775, true);

        $fileLocated = $directory . $fileName;

        // Gera QR Code
        QrCode::size(200)->generate($bookingInfo->booking_id, public_path('assets/admin/qrcodes/') . $bookingInfo->booking_id . '.svg');
        
        // Obtém informações do evento
        $eventInfo = EventContent::where('event_id', $bookingInfo->event_id)->first();

        PDF::loadView('frontend.event.invoice', compact('bookingInfo', 'eventInfo'))->save($fileLocated);

        return $fileName;
    }

    public function testManualPDFGeneration()
    {
        $qrCodePath = public_path('assets/admin/qrcodes/test.svg');
        $pdfPath = public_path('assets/admin/file/test.pdf');

        try {
            // Gerar QR Code manualmente
            QrCode::size(200)->generate('TestQRCode', $qrCodePath);
            Log::info('QR Code gerado manualmente: ' . $qrCodePath);

            // Verificar se o QR Code foi criado
            if (!file_exists($qrCodePath)) {
                throw new \Exception('Falha ao gerar o QR Code.');
            }

            // Simular uma reserva para passar para a view
            $bookingInfo = (object) [
                'booking_id' => 'TEST123',
                'currencyTextPosition' => 'left',
                'currencyText' => 'USD',
                'created_at' => Carbon::now(),
                'event_date' => Carbon::now()->addDays(5),
                'quantity' => 1
            ];

            // Simular as informações do evento
            $eventInfo = (object) ['title' => 'Evento de Teste'];

            // Gerar PDF manualmente
            PDF::loadView('frontend.event.invoice', [
                'bookingInfo' => $bookingInfo,
                'ticket' => null,
                'qrCodePath' => $qrCodePath,
                'ticketNumber' => 1,
                'eventInfo' => $eventInfo // Informações do evento de teste
            ])->save($pdfPath);
            Log::info('PDF gerado manualmente: ' . $pdfPath);

            if (!file_exists($pdfPath)) {
                throw new \Exception('Falha ao gerar o PDF.');
            }

            return 'QR Code e PDF gerados com sucesso. Verifique os arquivos em: ' . $qrCodePath . ' e ' . $pdfPath;
        } catch (\Exception $e) {
            Log::error('Erro ao gerar QR Code ou PDF manualmente: ' . $e->getMessage());
            return 'Erro: ' . $e->getMessage();
        }
    }

    public function destroy($id)
    {
        $Booking = Booking::find($id);
        @unlink(public_path('assets/admin/file/attachments/') . $Booking->attachment);
        @unlink(public_path('assets/admin/file/invoices/') . $Booking->invoice);
        $Booking->delete();

        return redirect()->back()->with('success', 'Booking deleted successfully!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $booking = Booking::find($id);
            @unlink(public_path('assets/admin/file/attachments/') . $booking->attachment);
            @unlink(public_path('assets/admin/file/invoices/') . $booking->invoice);
            $booking->delete();
        }
        Session::flash('success', 'Deleted Successfully');
        return response()->json(['status' => 'success'], 200);
    }

    public function report(Request $request)
    {
        $language = $this->getLanguage();

        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $paymentStatus = $request->payment_status;
        $paymentMethod = $request->payment_method;

        if (!empty($fromDate) && !empty($toDate)) {
            $bookings = Booking::join('event_contents', 'event_contents.event_id', 'bookings.event_id')
                ->join('customers', 'customers.id', 'bookings.customer_id')
                ->where('event_contents.language_id', $language->id)
                ->when($fromDate, function ($query, $fromDate) {
                    return $query->whereDate('bookings.created_at', '>=', Carbon::parse($fromDate));
                })->when($toDate, function ($query, $toDate) {
                    return $query->whereDate('bookings.created_at', '<=', Carbon::parse($toDate));
                })->when($paymentMethod, function ($query, $paymentMethod) {
                    return $query->where('bookings.paymentMethod', $paymentMethod);
                })->when($paymentStatus, function ($query, $paymentStatus) {
                    return $query->where('bookings.paymentStatus', '=', $paymentStatus);
                })
                ->select('event_contents.title', 'customers.fname as customerfname', 'customers.lname as customerlname', 'event_contents.slug', 'bookings.*')
                ->orderByDesc('id');

            Session::put('booking_report', $bookings->get());
            $data['bookings'] = $bookings->paginate(10);
        } else {
            Session::put('booking_report', []);
            $data['bookings'] = [];
        }

        $data['onPms'] = OnlineGateway::where('status', 1)->get();
        $data['offPms'] = OfflineGateway::where('status', 1)->get();
        $data['deLang'] = $language;
        $data['abs'] = Basic::select('base_currency_symbol_position', 'base_currency_symbol')->first();

        return view('backend.event.booking.report', $data);
    }

    public function export()
    {
        $bookings = Session::get('booking_report');
        if (empty($bookings) || count($bookings) == 0) {
            Session::flash('warning', 'There is no bookings to export');
            return back();
        }
        return Excel::download(new BookingExport($bookings), 'bookings.csv');
    }
}
