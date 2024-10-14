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
use Ixudra\Curl\Facades\Curl;

class PhonepeController extends Controller
{
    public function purchaseProcess(Request $request)
    {
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Purchase Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
        $currencyInfo = $this->getCurrencyInfo();
        if ($currencyInfo->base_currency_text != 'INR') {
            return back()->with(['alert-type' => 'error', 'message' => 'Invalid Currency.']);
        }
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
            'method' => 'Phonepe',
            'gateway_type' => 'online',
            'payment_status' => 'completed',
            'order_status' => 'pending',
            'tnxid' => '',
        );
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Purchase End ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        $info = OnlineGateway::where('keyword', 'phonepe')->first();
        $information = json_decode($info->information, true);
        $randomNo = substr(uniqid(), 0, 3);
        $data = array(
            'merchantId' => $information['merchant_id'],
            'merchantTransactionId' => uniqid(),
            'merchantUserId' => 'MUID' . $randomNo, // it will be the ID of tenants / vendors from database
            'amount' => round($grand_total, 2) * 100,
            'redirectUrl' => route('shop.phonepe.notify'),
            'redirectMode' => 'POST',
            'callbackUrl' => route('shop.phonepe.notify'),
            'mobileNumber' => $request->phone ? $request->phone : '9999999999',
            'paymentInstrument' =>
            array(
                'type' => 'PAY_PAGE',
            ),
        );

        $encode = base64_encode(json_encode($data));

        $saltKey = $information['salt_key']; // salt key
        $saltIndex = $information['salt_index'];

        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);

        $finalXHeader = $sha256 . '###' . $saltIndex;

        if ($information['sandbox_status'] == 1) {
            $url = "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay"; // sandbox payment URL
        } else {
            $url = "https://api.phonepe.com/apis/hermes/pg/v1/pay"; // prod payment URL
        }

        $response = Curl::to($url)
            ->withHeader('Content-Type:application/json')
            ->withHeader('X-VERIFY:' . $finalXHeader)
            ->withData(json_encode(['request' => $encode]))
            ->post();

        $rData = json_decode($response);
        if ($rData->success == true) {
            if (!empty($rData->data->instrumentResponse->redirectInfo->url)) {
                $request->session()->put('arrData', $arrData);
                return redirect()->to($rData->data->instrumentResponse->redirectInfo->url);
            } else {
                return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => 'Payment Canceled.']);
            }
        } else {
            return redirect()->route('check-out')->with(['alert-type' => 'error', 'message' => 'Payment Canceled.']);
        }
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Payment Gateway Info End ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
    }

    public function notify(Request $request)
    {
        $info = OnlineGateway::where('keyword', 'phonepe')->first();
        $information = json_decode($info->information, true);
        if ($request->code == 'PAYMENT_SUCCESS' && $information['merchant_id'] == $request->merchantId) {
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
