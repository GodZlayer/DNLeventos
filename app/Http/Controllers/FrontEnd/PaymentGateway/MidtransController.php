<?php

namespace App\Http\Controllers\FrontEnd\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Event\BookingController;
use App\Http\Controllers\FrontEnd\Shop\OrderController;
use App\Models\BasicSettings\Basic;
use App\Models\Earning;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Midtrans\Snap;
use Midtrans\Config as MidtransConfig;

class MidtransController extends Controller
{
    public function makePayment(Request $request, $event_id)
    {
        $currencyInfo = $this->getCurrencyInfo();
        if ($currencyInfo->base_currency_text != 'IDR') {
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


        $booking = new BookingController();

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
            'paymentMethod' => 'Midtrans',
            'gatewayType' => 'online',
            'paymentStatus' => 'completed',
        );

        $payable_amount = round($total + $tax_amount, 2);

        $info = OnlineGateway::where('keyword', 'midtrans')->first();
        $information = json_decode($info->information, true);

        // will come from database
        MidtransConfig::$serverKey = $information['server_key'];
        MidtransConfig::$isProduction = $information['is_production'] == 0 ? true : false;
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
        $token = uniqid();
        Session::put('token', $token);
        $params = [
            'transaction_details' => [
                'order_id' => $token,
                'gross_amount' => $payable_amount * 1000, // will be multiplied by 1000
            ],
            'customer_details' => [
                'first_name' => $request->fname . ' ' . $request->lname,
                'email' => $request->email,
                'phone' => $request->phone,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        // put some data in session before redirect to midtrans url
        $request->session()->put('event_id', $event_id);
        $request->session()->put('arrData', $arrData);
        if ($information['is_production'] == 1) {
            $is_production = $information['is_production'];
        }

        return view('payments.event_midtrans', compact('snapToken', 'is_production'));
    }

    public function ccNotify($orderId)
    {
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
    }

    public function bankNotify(Request $request)
    {
        $token = Session::get('token');
        if ($request->status_code == 200 && $token == $request->order_id) {
            if (Session::get('midtrans_payment_type') == 'event') {
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
                Session::forget('midtrans_payment_type');

                return redirect()->route('event_booking.complete', ['id' => $event_id, 'booking_id' => $bookingInfo->id]);
            } else {
                // get the information from session
                $arrData = Session::get('arrData');
                $store = new OrderController();

                // store the course enrolment information in database
                $orderInfo = $store->storeData($arrData);
                //store data to oder items table
                $store->storeOders($orderInfo);

                // generate an invoice in pdf format
                $invoice = $store->generateInvoice($orderInfo);

                // then, update the invoice field info in database
                $orderInfo->update(['invoice_number' => $invoice]);

                // send a mail to the customer with the invoice
                $store->sendMail($orderInfo);

                // remove all session data
                Session::forget('arrData');
                Session::forget('midtrans_payment_type');

                return redirect()->route('product_order.complete');
            }
        } else {
            if (Session::get('midtrans_payment_type') == 'event') {
                return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => 'Payment Canceled.']);
            } else {
                return redirect()->route('shop.checkout')->with(['alert-type' => 'error', 'message' => 'Payment Canceled.']);
            }
        }
    }
}
