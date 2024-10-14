<?php

namespace App\Http\Controllers\FrontEnd\Shop\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Shop\OrderController;
use App\Models\BasicSettings\Basic;
use App\Models\ShopManagement\ShippingCharge;
use Illuminate\Http\Request;
use Config\Iyzipay;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class IyzipayController extends Controller
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
        if ($currencyInfo->base_currency_text != 'TRY') {
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

            'method' => 'Iyzico',
            'gateway_type' => 'online',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'tnxid' => '',
        );
        /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~ Booking End ~~~~~~~~~~~~~~
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/

        /*````````````````````````````````````````````
        ````````````Payment gateway info start`````````
        ---------------------------------------------*/
        $notifyURL = route('shop.iyzico.notify');
        $options = Iyzipay::options();
        $conversion_id = uniqid(9999, 999999);
        # create request class
        $request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $request->setLocale(\Iyzipay\Model\Locale::EN);
        $request->setConversationId($conversion_id);
        $request->setPrice($total);
        $request->setPaidPrice($grand_total);
        $request->setCurrency(\Iyzipay\Model\Currency::TL);
        $request->setBasketId("B67832");
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $request->setCallbackUrl($notifyURL);
        $request->setEnabledInstallments(array(2, 3, 6, 9));

        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId(uniqid());
        $buyer->setName($arrData['fname']);
        $buyer->setSurname($arrData['lname']);
        $buyer->setGsmNumber("+905350000000");
        $buyer->setEmail($arrData['email']);
        $buyer->setIdentityNumber("74300864791");
        $buyer->setLastLoginDate("");
        $buyer->setRegistrationDate("");
        $buyer->setRegistrationAddress($arrData['address']);
        $buyer->setIp("");
        $buyer->setCity($arrData['city']);
        $buyer->setCountry($arrData['country']);
        $buyer->setZipCode($arrData['zip_code']);
        $request->setBuyer($buyer);

        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($arrData['fname']);
        $shippingAddress->setCity($arrData['city']);
        $shippingAddress->setCountry($arrData['country']);
        $shippingAddress->setAddress($arrData['address']);
        $shippingAddress->setZipCode($arrData['zip_code']);
        $request->setShippingAddress($shippingAddress);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($arrData['fname']);
        $billingAddress->setCity($arrData['city']);
        $billingAddress->setCountry($arrData['country']);
        $billingAddress->setAddress($arrData['address']);
        $billingAddress->setZipCode($arrData['zip_code']);
        $request->setBillingAddress($billingAddress);

        $q_id = uniqid(999, 99999);
        $basketItems = array();
        $firstBasketItem = new \Iyzipay\Model\BasketItem();
        $firstBasketItem->setId($q_id);
        $firstBasketItem->setName("Booking Id " . $q_id);
        $firstBasketItem->setCategory1("Purchase or Booking");
        $firstBasketItem->setCategory2("");
        $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
        $firstBasketItem->setPrice($total);
        $basketItems[0] = $firstBasketItem;

        // $secondBasketItem = new \Iyzipay\Model\BasketItem();
        // $secondBasketItem->setId("BI102");
        // $secondBasketItem->setName("Game code");
        // $secondBasketItem->setCategory1("Game");
        // $secondBasketItem->setCategory2("Online Game Items");
        // $secondBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
        // $secondBasketItem->setPrice("0.5");
        // $basketItems[1] = $secondBasketItem;

        // $thirdBasketItem = new \Iyzipay\Model\BasketItem();
        // $thirdBasketItem->setId("BI103");
        // $thirdBasketItem->setName("Usb");
        // $thirdBasketItem->setCategory1("Electronics");
        // $thirdBasketItem->setCategory2("Usb / Cable");
        // $thirdBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
        // $thirdBasketItem->setPrice("0.2");
        // $basketItems[2] = $thirdBasketItem;
        $request->setBasketItems($basketItems);

        # make request
        $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($request, $options);

        $paymentResponse = (array)$payWithIyzicoInitialize;
        foreach ($paymentResponse as $key => $data) {
            $paymentInfo = json_decode($data, true);
            if ($paymentInfo['status'] == 'success') {
                if (!empty($paymentInfo['payWithIyzicoPageUrl'])) {
                    Cache::forget('conversation_id');
                    Session::put('iyzico_token', $paymentInfo['token']);
                    Session::put('conversation_id', $conversion_id);
                    Cache::put('conversation_id', $conversion_id, 60000);

                    // put some data in session before redirect to paypal url
                    Session::put('arrData', $arrData);

                    return redirect($paymentInfo['payWithIyzicoPageUrl']);
                }
            }
            return redirect()->route('shop.checkout')->with(['alert-type' => 'error', 'message' => $paymentInfo['errorMessage']]);
        }
    }

    public function notify(Request $request)
    {
        $conversation_id = Cache::get('conversation_id');
        // get the information from session
        $arrData = Session::get('arrData');
        $arrData['conversation_id'] = $conversation_id;
        $store = new OrderController();

        // store the course enrolment information in database
        $arrData['t_payment_status'] = 0;
        $orderInfo = $store->storeData($arrData);
        //store data to oder items table
        $store->storeOders($orderInfo);

        // remove all session data
        Session::forget('arrData');
        return redirect()->route('product_order.complete');
    }
}
