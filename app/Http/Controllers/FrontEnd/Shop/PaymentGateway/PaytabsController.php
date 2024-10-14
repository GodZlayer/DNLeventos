<?php

namespace App\Http\Controllers\FrontEnd\Shop\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Shop\OrderController;
use App\Models\BasicSettings\Basic;
use App\Models\ShopManagement\ShippingCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PaytabsController extends Controller
{
    public function purchaseProcess(Request $request)
    {
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

        //get data using helper functin in Helper.php
        $paytabInfo = paytabInfo();
        if ($currencyInfo->base_currency_text != $paytabInfo['currency']) {
            return redirect()->back()->with('currency_error', 'Invalid currency for paytabs payment.')->withInput();
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
            'method' => 'Paytabs',
            'gateway_type' => 'online',
            'payment_status' => 'completed',
            'order_status' => 'pending',
            'tnxid' => '',
        );

        $description = 'Product Purchase via paytabs';
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
                'cart_amount' => round($grand_total, 2),
                'return' => route('shop.paytabs.notify'),
            ]);

            $responseData = $response->json();
            $request->session()->put('arrData', $arrData);
            return redirect()->to($responseData['redirect_url']);
        } catch (\Exception $e) {
            return redirect()->route('shop.checkout')->with(['alert-type' => 'error', 'message' => 'Payment Canceled.']);
        }
    }

    public function notify(Request $request)
    {
        $resp = $request->all();
        if ($resp['respStatus'] == "A" && $resp['respMessage'] == 'Authorised') {
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
            return redirect()->route('shop.checkout')->with(['alert-type' => 'error', 'message' => 'Payment Canceled.']);
        }
    }
}
