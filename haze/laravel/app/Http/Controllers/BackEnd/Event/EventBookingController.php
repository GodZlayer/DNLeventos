<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Earning;
use App\Models\EventContent;
use App\Models\Ticket;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;

class EventBookingController extends Controller
{
    public function updatePaymentStatus(Request $request, $id)
    {
        $booking = Booking::where('id', $id)->first();

        if ($request['payment_status'] == 'completed') {
            $booking->update([
                'paymentStatus' => 'completed'
            ]);

            // Atualizar o ganho
            $earning = Earning::first();
            $earning->total_revenue += ($booking->price + $booking->tax);
            $earning->total_earning += $booking->organizer_id != null ? ($booking->tax + $booking->commission) : ($booking['price'] + $booking->tax);
            $earning->save();

            // Gerar PDF e QR Code para cada item
            $items = json_decode($booking->variation, true); // Supondo que a variação contenha os tickets comprados
            foreach ($items as $item) {
                $ticket = Ticket::find($item['ticket_id']); // Recuperar cada ticket
                if ($ticket) {
                    // Gerar Invoice e QR Code para cada item
                    $this->generateInvoice($booking, $ticket);
                }
            }

            // Enviar e-mail de confirmação
            $this->sendMail($request, $booking, 'Booking approved');
        } 
        // O resto do código permanece inalterado
    }

    public function generateInvoice($bookingInfo, $ticket = null)
    {
        // O código de geração do PDF agora considera o ticket
        $fileName = $bookingInfo->booking_id . ($ticket ? '-' . $ticket->id : '') . '.pdf'; // Adiciona o ID do ticket ao nome do arquivo
        $directory = public_path('assets/admin/file/invoices/');

        @mkdir($directory, 0775, true);
        @mkdir(public_path('assets/admin/qrcodes/'), 0775, true);

        $fileLocated = $directory . $fileName;

        // Gerar QR Code para cada item
        $qrCodeFileName = $bookingInfo->booking_id . ($ticket ? '-' . $ticket->id : '') . '.svg';
        QrCode::size(200)->generate($bookingInfo->booking_id, public_path('assets/admin/qrcodes/') . $qrCodeFileName);

        // Obter título do evento
        $language = $this->getLanguage();
        $eventInfo = EventContent::where('event_id', $bookingInfo->event_id)->where('language_id', $language->id)->first();

        $width = "50%";
        $float = "right";
        $mb = "35px";
        $ml = "18px";

        // Passar informações do ticket para a view
        PDF::loadView('frontend.event.invoice', compact('bookingInfo', 'eventInfo', 'ticket', 'width', 'float', 'mb', 'ml'))->save($fileLocated);

        return $fileName;
    }

    // Restante do controlador...

    protected function sendMail($request, $booking, $subject)
    {
        // Implementação do envio de e-mail
    }

    protected function getLanguage()
    {
        // Implementação para obter o idioma
    }
}
