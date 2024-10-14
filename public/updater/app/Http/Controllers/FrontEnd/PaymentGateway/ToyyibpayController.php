<?php

namespace App\Http\Controllers\FrontEnd\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Event\BookingController;
use App\Models\BasicSettings\Basic;
use App\Models\Earning;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ToyyibpayController extends Controller
{
    public function makePayment(Request $request, $event_id)
    {
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Booking Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $currencyInfo = $this->getCurrencyInfo();
        if ($currencyInfo->base_currency_text != 'RM') {
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
            'paymentMethod' => 'Toyyibpay',
            'gatewayType' => 'online',
            'paymentStatus' => 'completed',
        );

        $payable_amount = round($total + $tax_amount, 2);
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Booking End ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $info = OnlineGateway::where('keyword', 'toyyibpay')->first();
        $information = json_decode($info->information, true);
        $ref = uniqid();
        session()->put('toyyibpay_ref_id', $ref);
        $bill_title = 'Event Booking';
        $bill_description = 'Event Booking via toyyibpay';

        $some_data = array(
            'userSecretKey' => $information['secret_key'],
            'categoryCode' => $information['category_code'],
            'billName' => $bill_title,
            'billDescription' => $bill_description,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $payable_amount * 100,
            'billReturnUrl' => route('event_booking.toyyibpay.notify'),
            'billExternalReferenceNo' => $ref,
            'billTo' => $request->fname . ' ' . $request->lname,
            'billEmail' => $request->email,
            'billPhone' => $request->phone,
        );

        if ($information['sandbox_status'] == 1) {
            $host = 'https://dev.toyyibpay.com/'; // for development environment
        } else {
            $host = 'https://toyyibpay.com/'; // for production environment
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $host . 'index.php/api/createBill');  // sandbox will be dev.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        $response = json_decode($result, true);
        if (!empty($response[0])) {
            $request->session()->put('event_id', $event_id);
            $request->session()->put('arrData', $arrData);
            return redirect($host . $response[0]["BillCode"]);
        } else {
            return redirect()->route('check-out')
                ->with(['alert-type' => 'error', 'message' => $response['msg']]);
        }

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info End~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }

    public function notify(Request $request)
    {
        $ref = session()->get('toyyibpay_ref_id');
        if ($request['status_id'] == 1 && $request['order_id'] == $ref) {
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
            Session::forget('toyyibpay_ref_id');
            return redirect()->route('event_booking.complete', ['id' => $event_id, 'booking_id' => $bookingInfo->id]);
        } elseif ($request['status_id'] == 3 && $request['order_id'] == $ref) {
            return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => 'Payment failed']);
        }
    }
}
