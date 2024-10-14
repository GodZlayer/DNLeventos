<?php

namespace App\Http\Controllers\FrontEnd\Shop\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Shop\OrderController;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\ShopManagement\ShippingCharge;
use Basel\MyFatoorah\MyFatoorah;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MyFatoorahController extends Controller
{
    public $myfatoorah;

    public function __construct()
    {
        $info = OnlineGateway::where('keyword', 'myfatoorah')->first();
        $information = json_decode($info->information, true);
        $this->myfatoorah = MyFatoorah::getInstance($information['sandbox_status'] == 1 ? true : false);
    }

    public function purchaseProcess(Request $request)
    {
        try {
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
            $allowed_currency = array('KWD', 'SAR', 'BHD', 'AED', 'QAR', 'OMR', 'JOD');
            if (!in_array($currencyInfo->base_currency_text, $allowed_currency)) {
                return redirect()->back()->with('error', 'Invalid currency for myfatoorah  payment.')->withInput();
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

                'method' => 'Myfatoorah',
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
            $info = OnlineGateway::where('keyword', 'myfatoorah')->first();
            $information = json_decode(
                $info->information,
                true
            );
            $random_1 = rand(999, 9999);
            $random_2 = rand(9999, 99999);
            $result = $this->myfatoorah->sendPayment(
                $request->fname . ' ' . $request->lname,
                $grand_total,
                [
                    'CustomerMobile' => $information['sandbox_status'] == 1 ? '56562123544' : $request->phone,
                    'CustomerReference' => "$random_1",  //orderID
                    'UserDefinedField' => "$random_2", //clientID
                    "InvoiceItems" => [
                        [
                            "ItemName" => "Product Purchase",
                            "Quantity" => 1,
                            "UnitPrice" => $grand_total
                        ]
                    ]
                ]
            );
            if ($result && $result['IsSuccess'] == true) {
                $request->session()->put('myfatoorah_payment_type', 'shop');
                $request->session()->put('arrData', $arrData);
                return redirect($result['Data']['InvoiceURL']);
            }
        } catch (Exception $e) {
            return redirect()->route('check-out')
                ->with(['alert-type' => 'error', 'message' => 'Payment Cancel']);
        }
    }

    public function successCallback(Request $request)
    {
        if (!empty($request->paymentId)) {
            $result = $this->myfatoorah->getPaymentStatus('paymentId', $request->paymentId);
            if ($result && $result['IsSuccess'] == true && $result['Data']['InvoiceStatus'] == "Paid") {
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
                return [
                    'status' => 'success'
                ];
            } else {
                return [
                    'status' => 'fail'
                ];
            }
        } else {
            return [
                'status' => 'fail'
            ];
        }
    }

    public function failCallback(Request $request)
    {
        if (!empty($request->paymentId)) {
            $result = $this->myfatoorah->getPaymentStatus('paymentId', $request->paymentId);

            if ($result && $result['IsSuccess'] == true && $result['Data']['InvoiceStatus'] == "Pending") {
                return redirect()->route('shop.checkout')->with(['alert-type' => 'error', 'message' => 'Payment failed']);
            }
        }
    }
}
