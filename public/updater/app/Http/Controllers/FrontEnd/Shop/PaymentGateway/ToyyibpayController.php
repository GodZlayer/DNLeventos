<?php

namespace App\Http\Controllers\FrontEnd\Shop\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Shop\OrderController;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\ShopManagement\ShippingCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ToyyibpayController extends Controller
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
        if ($currencyInfo->base_currency_text != 'RM') {
            return redirect()->back()->with('error', 'Invalid currency for toyyibpay payment.')->withInput();
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

            'method' => 'Toyyibpay',
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
        $info = OnlineGateway::where('keyword', 'toyyibpay')->first();
        $information = json_decode($info->information, true);
        $ref = uniqid();
        session()->put('toyyibpay_ref_id', $ref);
        $bill_title = 'Product Purchase';
        $bill_description = 'Product Purchase via toyyibpay';

        $some_data = array(
            'userSecretKey' => $information['secret_key'],
            'categoryCode' => $information['category_code'],
            'billName' => $bill_title,
            'billDescription' => $bill_description,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $grand_total * 100,
            'billReturnUrl' => route('shop.toyyibpay.notify'),
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
            // put some data in session before redirect to paytm url
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
        } elseif ($request['status_id'] == 3 && $request['order_id'] == $ref) {
            return redirect()->route('shop.checkout')->with(['alert-type' => 'error', 'message' => 'Payment failed']);
        }
    }
}
