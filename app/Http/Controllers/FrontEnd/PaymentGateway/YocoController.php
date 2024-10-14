<?php

namespace App\Http\Controllers\FrontEnd\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Event\BookingController;
use App\Models\BasicSettings\Basic;
use App\Models\Earning;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;


class YocoController extends Controller
{
    public function makePayment(Request $request, $event_id)
    {
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Booking Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $currencyInfo = $this->getCurrencyInfo();
        if ($currencyInfo->base_currency_text != 'ZAR') {
            return back()->with(['alert-type' => 'error', 'message' => 'Invalid Currency.']);
        }

        $rules = [
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'country' => 'required',
            'address' => 'required',
            'gateway' => 'required',
        ];

        $message = [];

        $message['fname.required'] = 'The first name feild is required';
        $message['lname.required'] = 'The last name feild is required';
        $message['gateway.required'] = 'The payment gateway feild is required';
        $request->validate($rules, $message);

        $total = Session::get('grand_total');
        $quantity = Session::get('quantity');
        $discount = Session::get('discount');

        //tax and commission end
        $basicSetting = Basic::select('commission')->first();

        $tax_amount = Session::get('tax');
        $commission_amount = ($total * $basicSetting->commission) / 100;

        $total_early_bird_dicount = Session::get('total_early_bird_dicount');
        // changing the currency before redirect to PayPal


        $arrData = array(
            'event_id' => $event_id,
            'price' => $total,
            'tax' => $tax_amount,
            'commission' => $commission_amount,
            'quantity' => $quantity,
            'discount' => $discount,
            'total_early_bird_dicount' => $total_early_bird_dicount,
            'currencyText' => $currencyInfo->base_currency_text,
            'currencyTextPosition' => $currencyInfo->base_currency_text_position,
            'currencySymbol' => $currencyInfo->base_currency_symbol,
            'currencySymbolPosition' => $currencyInfo->base_currency_symbol_position,
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'address' => $request->address,
            'paymentMethod' => 'Yoco',
            'gatewayType' => 'online',
            'paymentStatus' => 'completed',
        );

        $payable_amount = round($total + $tax_amount, 2);

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Send Request for payment start~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $info = OnlineGateway::where('keyword', 'yoco')->first();
        $information = json_decode($info->information, true);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $information['secret_key'],
        ])->post('https://payments.yoco.com/api/checkouts', [
            'amount' => $payable_amount * 100,
            'currency' => 'ZAR',
            'successUrl' => route('event_booking.yoco.notify')
        ]);

        $responseData = $response->json();
        if (array_key_exists('redirectUrl', $responseData)) {
            $request->session()->put('event_id', $event_id);
            $request->session()->put('yoco_id', $responseData['id']);
            $request->session()->put('s_key', $information['secret_key']);
            $request->session()->put('arrData', $arrData);
            return redirect($responseData["redirectUrl"]);
        } else {
            return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => 'Payment failed']);
        }
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Send Request for payment start~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }

    public function notify(Request $request)
    {
        $id = Session::get('yoco_id');
        $s_key = Session::get('s_key');
        $info = OnlineGateway::where('keyword', 'yoco')->first();
        $information = json_decode($info->information, true);

        if ($id && $information['secret_key'] == $s_key) {
            // get the information from session
            $event_id = Session::get('event_id');
            $arrData = Session::get('arrData');
            $booking = new BookingController();

            // store the course enrolment information in database
            $bookingInfo = $booking->storeData($arrData);
            // generate an invoice in pdf format
            $invoice = $booking->generateInvoice($bookingInfo, $event_id);
            //unlink qr code
            @unlink(public_path('assets/admin/qrcodes/') . $bookingInfo->booking_id . '.svg');
            //end unlink qr code

            // then, update the invoice field info in database
            $bookingInfo->update(['invoice' => $invoice]);

            //add blance to admin revinue
            $earning = Earning::first();
            $earning->total_revenue = $earning->total_revenue + $arrData['price'] + $bookingInfo->tax;
            if ($bookingInfo['organizer_id'] != null) {
                $earning->total_earning = $earning->total_earning + ($bookingInfo->tax + $bookingInfo->commission);
            } else {
                $earning->total_earning = $earning->total_earning + $arrData['price'] + $bookingInfo->tax;
            }
            $earning->save();

            //storeTransaction
            $bookingInfo['paymentStatus'] = 1;
            $bookingInfo['transcation_type'] = 1;

            storeTranscation($bookingInfo);

            //store amount to organizer
            $organizerData['organizer_id'] = $bookingInfo['organizer_id'];
            $organizerData['price'] = $arrData['price'];
            $organizerData['tax'] = $bookingInfo->tax;
            $organizerData['commission'] = $bookingInfo->commission;
            storeOrganizer($organizerData);

            // send a mail to the customer with the invoice
            $booking->sendMail($bookingInfo);

            // remove all session data
            Session::forget('event_id');
            Session::forget('selTickets');
            Session::forget('arrData');
            Session::forget('paymentId');
            Session::forget('discount');
            Session::forget('yoco_id');
            Session::forget('s_key');
            return redirect()->route('event_booking.complete', ['id' => $event_id, 'booking_id' => $bookingInfo->id]);
        } else {
            return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => 'Payment failed']);
        }
    }
}
