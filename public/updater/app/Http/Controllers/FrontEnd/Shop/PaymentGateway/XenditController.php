<?php

namespace App\Http\Controllers\FrontEnd\Shop\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Shop\OrderController;
use App\Models\BasicSettings\Basic;
use App\Models\ShopManagement\ShippingCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class XenditController extends Controller
{
    public function purchaseProcess(Request $request)
    {
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Purchase Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $currencyInfo = $this->getCurrencyInfo();
        $cart_items = Session::get('cart');

        $total = 0;
        $quantity = 0;
        foreach ($cart_items as $p) {
            $total += $p['price'] * $p['qty'];
            $quantity += $p['price'] * $p['qty'];
        }
        if ($request->shipping_method) {
            $shipping_cost = ShippingCharge::where('id', $request->shipping_method)->first();
            $shipping_charge = $shipping_cost->charge;
            $shipping_method = $shipping_cost->title;
        } else {
            $shipping_charge = 0;
            $shipping_method = NULL;
        }

        $discount = Session::get('Shop_discount');
        $tax = Basic::select('shop_tax')->first();
        $tax_percentage = $tax->shop_tax;
        $total_tax_amount = ($tax_percentage / 100) * ($total - $discount);
        $grand_total = ($shipping_charge + $total + $total_tax_amount) - $discount;

        // checking whether the currency is set to 'INR' or not
        $allowed_currency = array('IDR', 'PHP', 'USD', 'SGD', 'MYR');
        if (!in_array($currencyInfo->base_currency_text, $allowed_currency)) {
            return redirect()->back()->with('error', 'Invalid currency for xendit payment.')->withInput();
        }

        if (Auth::guard('customer')->user()) {
            $user_id = Auth::guard('customer')->user()->id;
        } else {
            $user_id = 0;
        }
        $arrData = array(
            'user_id' => $user_id,
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'address' => $request->address,

            's_fname' => $request->sameas_shipping == NULL ? $request->s_fname : $request->fname,
            's_lname' => $request->sameas_shipping == NULL ? $request->s_lname : $request->lname,
            's_email' => $request->sameas_shipping == NULL ? $request->s_email : $request->email,
            's_phone' => $request->sameas_shipping == NULL ? $request->s_phone : $request->phone,
            's_country' => $request->sameas_shipping == NULL ? $request->s_country : $request->country,
            's_state' => $request->sameas_shipping == NULL ? $request->s_state : $request->state,
            's_city' => $request->sameas_shipping == NULL ? $request->s_city : $request->city,
            's_zip_code' => $request->sameas_shipping == NULL ? $request->s_city : $request->city,
            's_address' => $request->sameas_shipping == NULL ? $request->s_address : $request->address,

            'cart_total' => $total,
            'discount' => $discount,
            'tax_percentage' => $tax_percentage,
            'tax' => $total_tax_amount,
            'grand_total' => $grand_total,
            'currency_code' => '',

            'shipping_charge' => $shipping_charge,
            'shipping_method' => $shipping_method,
            'order_number' => uniqid(),
            'charge_id' => $request->shipping_method,

            'method' => 'Xendit',
            'gateway_type' => 'online',
            'payment_status' => 'completed',
            'order_status' => 'pending',
            'tnxid' => '',
        );
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Booking End ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $external_id = Str::random(10);
        $secret_key = 'Basic ' . config('xendit.key_auth');

        $data_request = Http::withHeaders([
            'Authorization' => $secret_key
        ])->post('https://api.xendit.co/v2/invoices', [
            'external_id' => $external_id,
            'amount' => $grand_total,
            'currency' => $currencyInfo->base_currency_text,
            'success_redirect_url' => route('shop.xendit.notify')
        ]);
        $response = $data_request->object();
        $response = json_decode(json_encode($response), true);
        if (!empty($response['success_redirect_url'])) {
            $request->session()->put('arrData', $arrData);
            $request->session()->put('xendit_id', $response['id']);
            $request->session()->put('secret_key', config('xendit.key_auth'));
            $request->session()->put('xendit_payment_type', 'shop');

            return redirect($response['invoice_url']);
        } else {
            return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => $response['message']]);
        }
    }

    public function callback($request)
    {
        $data = $request->all();
        if ($data['status'] == 'PAID') {
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

            return redirect()->route('product_order.complete');
        } else {
            return redirect()->route('shop.checkout')->with(['alert-type' => 'error', 'message' => 'Payment failed']);
        }
    }

    // return to success page
    public function notify(Request $request)
    {
        $xendit_id = Session::get('xendit_id');
        $secret_key = Session::get('secret_key');
        if (!is_null($xendit_id) && $secret_key == config('xendit.key_auth')) {
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

            return redirect()->route('product_order.complete');
        } else {
            return redirect()->route('shop.checkout')->with(['alert-type' => 'error', 'message' => 'Payment failed']);
        }
    }
}
