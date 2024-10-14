<?php

namespace App\Http\Controllers\FrontEnd\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\Event\BookingController;
use App\Models\BasicSettings\Basic;
use App\Models\Earning;
use App\Models\Event\Booking;
use Config\Iyzipay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class IyzipayController extends Controller
{
    public function makePayment(Request $request, $event_id)
    {
        //booking information
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

        $currencyInfo = $this->getCurrencyInfo();
        if ($currencyInfo->base_currency_text != 'TRY') {
            return back()->with(['alert-type' => 'error', 'message' => 'Invalid Currency.']);
        }

        $total = Session::get('grand_total');
        $quantity = Session::get('quantity');
        $discount = Session::get('discount');

        //tax and commission end
        $basicSetting = Basic::select('commission')->first();

        $tax_amount = Session::get('tax');
        $commission_amount = ($total * $basicSetting->commission) / 100;

        $total_early_bird_dicount = Session::get('total_early_bird_dicount');
        // changing the currency before redirect to PayPal
        if ($currencyInfo->base_currency_text !== 'TRY') {
            $rate = floatval($currencyInfo->base_currency_rate);
            $convertedTotal = round((($total + $tax_amount) / $rate), 2);
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
            'paymentMethod' => 'Iyzico',
            'gatewayType' => 'online',
            'paymentStatus' => 'pending',
        );

        $title = 'Event Booking';
        $notifyURL = route('event_booking.iyzico.notify');
        //booking information end

        $options = Iyzipay::options();
        $conversion_id = uniqid(9999, 999999);
        # create request class
        $request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $request->setLocale(\Iyzipay\Model\Locale::EN);
        $request->setConversationId($conversion_id);
        $request->setPrice($total);
        $request->setPaidPrice($payTotal);
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
                    Session::put('event_id', $event_id);
                    Session::put('arrData', $arrData);
                    return redirect($paymentInfo['payWithIyzicoPageUrl']);
                }
            }
            $notification = array('message' => $paymentInfo['errorMessage'], 'alert-type' => 'error');
            return redirect()->route('events')->with($notification);
        }
    }

    public function notify(Request $request)
    {
        $conversation_id = Cache::get('conversation_id');
        // get the information from session
        $event_id = Session::get('event_id');
        $arrData = Session::get('arrData');
        $booking = new BookingController();
        $arrData['conversation_id'] = $conversation_id;

        // store the course enrolment information in database
        $bookingInfo = $booking->storeData($arrData);

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

        $notification = array('message' => 'Payment Canceled', 'alert-type' => 'error');
        return redirect()->route('events')->with($notification);
    }
}
