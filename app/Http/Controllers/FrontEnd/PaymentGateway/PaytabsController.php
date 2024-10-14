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

class PaytabsController extends Controller
{
    public function makePayment(Request $request, $event_id)
    {
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

        $currencyInfo = $this->getCurrencyInfo();

        $total = Session::get('grand_total');
        $quantity = Session::get('quantity');
        $discount = Session::get('discount');

        //tax and commission end
        $basicSetting = Basic::select('commission')->first();

        $tax_amount = Session::get('tax');
        $commission_amount = ($total * $basicSetting->commission) / 100;

        $total_early_bird_dicount = Session::get('total_early_bird_dicount');

        $paytabInfo = paytabInfo();
        if ($currencyInfo->base_currency_text != $paytabInfo['currency']) {
            return redirect()->back()->with('currency_error', 'Invalid currency for paytabs payment.')->withInput();
        }

        $payTotal = $total + $tax_amount;

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
            'paymentMethod' => 'Paytabs',
            'gatewayType' => 'online',
            'paymentStatus' => 'completed',
        );
        $description = 'Event Booking via paytabs';
        try {
            $response = Http::withHeaders([
                'Authorization' => $paytabInfo['server_key'], // Server Key
                'Content-Type' => 'application/json',
            ])->post($paytabInfo['url'], [
                'profile_id' => $paytabInfo['profile_id'], // Profile ID
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => uniqid(),
                'cart_description' => $description,
                'cart_currency' => $paytabInfo['currency'], // set currency by region
                'cart_amount' => round($payTotal, 2),
                'return' => route('event_booking.paytabs.notify'),
            ]);

            $responseData = $response->json();
            $request->session()->put('event_id', $event_id);
            $request->session()->put('arrData', $arrData);
            return redirect()->to($responseData['redirect_url']);
        } catch (\Exception $e) {
            return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => 'Payment Canceled.']);
        }
    }

    public function notify(Request $request)
    {
        $resp = $request->all();
        if ($resp['respStatus'] == "A" && $resp['respMessage'] == 'Authorised') {
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
            Session::forget('token');
            return redirect()->route('event_booking.complete', ['id' => $event_id, 'booking_id' => $bookingInfo->id]);
        } else {
            return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => 'Payment Canceled.']);
        }
    }
}
