<?php

namespace App\Http\Controllers\FrontEnd\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Event\BookingController;
use App\Models\BasicSettings\Basic;
use App\Models\Earning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PixController extends Controller
{
    private $pixUrl, $pixToken;

    public function __construct()
    {
        $this->pixUrl = config('services.pix.url');
        $this->pixToken = config('services.pix.token');
    }

    public function bookingProcess(Request $request, $eventId)
    {
        $rules = [
            'fname' => 'required',
            'lname' => 'required',
            'gateway' => 'required',
        ];
        $messages = [
            'fname.required' => 'The first name field is required',
            'lname.required' => 'The last name field is required',
            'gateway.required' => 'The payment gateway field is required',
        ];
        $request->validate($rules, $messages);

        $booking = new BookingController();

        $total = Session::get('grand_total');
        $quantity = Session::get('quantity');
        $discount = Session::get('discount');
        $tax_amount = Session::get('tax');
        $commission_amount = ($total * Basic::first()->commission) / 100;

        $arrData = [
            'event_id' => $eventId,
            'price' => $total,
            'tax' => $tax_amount,
            'commission' => $commission_amount,
            'quantity' => $quantity,
            'discount' => $discount,
            'fname' => $request->fname,
            'lname' => $request->lname,
            'paymentMethod' => 'Pix',
            'gatewayType' => 'online',
            'paymentStatus' => 'pending',
        ];

        $title = 'Event Booking';
        $notifyURL = route('event_booking.pix.notify');
        $cancelURL = route('event_booking.cancel', ['id' => $eventId]);

        $payload = [
            'calendario' => [
                'expiracao' => 3600
            ],
            'devedor' => [
                'nome' => $request->fname . ' ' . $request->lname,
                'cpf' => '12345678909' // CPF de exemplo, substituir pelo real
            ],
            'valor' => [
                'original' => number_format($total + $tax_amount, 2, '.', '')
            ],
            'chave' => 'SUA_CHAVE_PIX', // Substitua pela sua chave Pix
            'solicitacaoPagador' => 'Pagamento do evento ' . $title,
            'infoAdicionais' => [
                [
                    'nome' => 'Evento',
                    'valor' => $title
                ]
            ]
        ];

        $response = Http::withToken($this->pixToken)
            ->post($this->pixUrl . '/v2/cob', $payload);

        if ($response->failed()) {
            Session::flash('error', 'Erro ao criar cobrança Pix');
            return redirect()->route('check-out');
        }

        $responseInfo = $response->json();

        // Colocar dados na sessão antes de redirecionar
        $request->session()->put('eventId', $eventId);
        $request->session()->put('arrData', $arrData);

        // Redirecionar para o QR Code Pix
        return redirect()->route('event_booking.pix.qr', ['qrcode' => $responseInfo['pixCopiaECola']]);
    }

    public function showQrCode($qrcode)
    {
        return view('frontend.payment.qr-code', compact('qrcode'));
    }

    public function notify(Request $request)
    {
        $eventId = $request->session()->get('eventId');
        $arrData = $request->session()->get('arrData');

        $paymentInfo = $request->all();

        if ($paymentInfo['status'] == 'CONCLUIDA') {
            $enrol = new BookingController();

            $bookingInfo = $enrol->storeData($arrData);
            $invoice = $enrol->generateInvoice($bookingInfo, $eventId);

            $bookingInfo->update(['invoice' => $invoice]);

            $earning = Earning::first();
            $earning->total_revenue += $arrData['price'] + $bookingInfo->tax;
            if ($bookingInfo['organizer_id'] != null) {
                $earning->total_earning += ($bookingInfo->tax + $bookingInfo->commission);
            } else {
                $earning->total_earning += $arrData['price'] + $bookingInfo->tax;
            }
            $earning->save();

            $bookingInfo['paymentStatus'] = 1;
            storeTranscation($bookingInfo);

            $organizerData = [
                'organizer_id' => $bookingInfo['organizer_id'],
                'price' => $arrData['price'],
                'tax' => $bookingInfo->tax,
                'commission' => $bookingInfo->commission,
            ];
            storeOrganizer($organizerData);

            $enrol->sendMail($bookingInfo);

            $request->session()->forget(['eventId', 'arrData', 'paymentId', 'discount']);
            return redirect()->route('event_booking.complete', ['id' => $eventId, 'booking_id' => $bookingInfo->id]);
        } else {
            $request->session()->forget(['eventId', 'arrData', 'discount']);
            return redirect()->route('event_booking.cancel', ['id' => $eventId]);
        }
    }
}
