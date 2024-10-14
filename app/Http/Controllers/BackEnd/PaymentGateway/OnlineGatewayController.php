<?php

namespace App\Http\Controllers\BackEnd\PaymentGateway;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway\OnlineGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class OnlineGatewayController extends Controller
{
  public function index()
  {
    $gatewayInfo['paypal'] = OnlineGateway::where('keyword', 'paypal')->first();
    $gatewayInfo['instamojo'] = OnlineGateway::where('keyword', 'instamojo')->first();
    $gatewayInfo['paystack'] = OnlineGateway::where('keyword', 'paystack')->first();
    $gatewayInfo['flutterwave'] = OnlineGateway::where('keyword', 'flutterwave')->first();
    $gatewayInfo['razorpay'] = OnlineGateway::where('keyword', 'razorpay')->first();
    $gatewayInfo['mercadopago'] = OnlineGateway::where('keyword', 'mercadopago')->first();
    $gatewayInfo['mollie'] = OnlineGateway::where('keyword', 'mollie')->first();
    $gatewayInfo['stripe'] = OnlineGateway::where('keyword', 'stripe')->first();
    $gatewayInfo['paytm'] = OnlineGateway::where('keyword', 'paytm')->first();
    $gatewayInfo['midtrans'] = OnlineGateway::where('keyword', 'midtrans')->first();
    $gatewayInfo['iyzico'] = OnlineGateway::where('keyword', 'iyzico')->first();
    $gatewayInfo['paytabs'] = OnlineGateway::where('keyword', 'paytabs')->first();
    $gatewayInfo['toyyibpay'] = OnlineGateway::where('keyword', 'toyyibpay')->first();
    $gatewayInfo['phonepe'] = OnlineGateway::where('keyword', 'phonepe')->first();
    $gatewayInfo['yoco'] = OnlineGateway::where('keyword', 'yoco')->first();
    $gatewayInfo['xendit'] = OnlineGateway::where('keyword', 'xendit')->first();
    $gatewayInfo['myfatoorah'] = OnlineGateway::where('keyword', 'myfatoorah')->first();
    $gatewayInfo['perfect_money'] = OnlineGateway::where('keyword', 'perfect_money')->first();

    return view('backend.payment-gateways.online-gateways', $gatewayInfo);
  }

  public function updatePayPalInfo(Request $request)
  {
    $rules = [
      'paypal_status' => 'required',
      'paypal_sandbox_status' => 'required',
      'paypal_client_id' => 'required',
      'paypal_client_secret' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['sandbox_status'] = $request->paypal_sandbox_status;
    $information['client_id'] = $request->paypal_client_id;
    $information['client_secret'] = $request->paypal_client_secret;

    $paypalInfo = OnlineGateway::where('keyword', 'paypal')->first();

    $paypalInfo->update([
      'information' => json_encode($information),
      'status' => $request->paypal_status
    ]);

    Session::flash('success', 'Updated Paypal Informaion Successfully');

    return redirect()->back();
  }

  public function updateInstamojoInfo(Request $request)
  {
    $rules = [
      'instamojo_status' => 'required',
      'instamojo_sandbox_status' => 'required',
      'instamojo_key' => 'required',
      'instamojo_token' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['sandbox_status'] = $request->instamojo_sandbox_status;
    $information['key'] = $request->instamojo_key;
    $information['token'] = $request->instamojo_token;

    $instamojoInfo = OnlineGateway::where('keyword', 'instamojo')->first();

    $instamojoInfo->update([
      'information' => json_encode($information),
      'status' => $request->instamojo_status
    ]);

    Session::flash('success', 'Updated Instamojo Informaion Successfully');

    return redirect()->back();
  }

  public function updatePaystackInfo(Request $request)
  {
    $rules = [
      'paystack_status' => 'required',
      'paystack_key' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['key'] = $request->paystack_key;

    $paystackInfo = OnlineGateway::where('keyword', 'paystack')->first();

    $paystackInfo->update([
      'information' => json_encode($information),
      'status' => $request->paystack_status
    ]);

    Session::flash('success', 'Updated Paystack Informaion Successfully');

    return redirect()->back();
  }

  public function updateFlutterwaveInfo(Request $request)
  {
    $rules = [
      'flutterwave_status' => 'required',
      'flutterwave_public_key' => 'required',
      'flutterwave_secret_key' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['public_key'] = $request->flutterwave_public_key;
    $information['secret_key'] = $request->flutterwave_secret_key;

    $flutterwaveInfo = OnlineGateway::where('keyword', 'flutterwave')->first();

    $flutterwaveInfo->update([
      'information' => json_encode($information),
      'status' => $request->flutterwave_status
    ]);

    $array = [
      'FLW_PUBLIC_KEY' => $request->flutterwave_public_key,
      'FLW_SECRET_KEY' => $request->flutterwave_secret_key
    ];

    setEnvironmentValue($array);
    Artisan::call('config:clear');

    Session::flash('success', 'Updated Flutterwave Informaion Successfully');

    return redirect()->back();
  }

  public function updateRazorpayInfo(Request $request)
  {
    $rules = [
      'razorpay_status' => 'required',
      'razorpay_key' => 'required',
      'razorpay_secret' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['key'] = $request->razorpay_key;
    $information['secret'] = $request->razorpay_secret;

    $razorpayInfo = OnlineGateway::where('keyword', 'razorpay')->first();

    $razorpayInfo->update([
      'information' => json_encode($information),
      'status' => $request->razorpay_status
    ]);

    Session::flash('success', 'Updated Razorpay Informaion Successfully');

    return redirect()->back();
  }

  public function updateMercadoPagoInfo(Request $request)
  {
    $rules = [
      'mercadopago_status' => 'required',
      'mercadopago_sandbox_status' => 'required',
      'mercadopago_token' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['sandbox_status'] = $request->mercadopago_sandbox_status;
    $information['token'] = $request->mercadopago_token;

    $mercadopagoInfo = OnlineGateway::where('keyword', 'mercadopago')->first();

    $mercadopagoInfo->update([
      'information' => json_encode($information),
      'status' => $request->mercadopago_status
    ]);

    Session::flash('success', 'Updated Mercadopago Informaion Successfully');

    return redirect()->back();
  }

  public function updateMollieInfo(Request $request)
  {
    $rules = [
      'mollie_status' => 'required',
      'mollie_key' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['key'] = $request->mollie_key;

    $mollieInfo = OnlineGateway::where('keyword', 'mollie')->first();

    $mollieInfo->update([
      'information' => json_encode($information),
      'status' => $request->mollie_status
    ]);

    $array = ['MOLLIE_KEY' => $request->mollie_key];

    setEnvironmentValue($array);
    Artisan::call('config:clear');

    Session::flash('success', 'Updated Mollie Informaion Successfully');

    return redirect()->back();
  }

  public function updateStripeInfo(Request $request)
  {
    $rules = [
      'stripe_status' => 'required',
      'stripe_key' => 'required',
      'stripe_secret' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['key'] = $request->stripe_key;
    $information['secret'] = $request->stripe_secret;

    $stripeInfo = OnlineGateway::where('keyword', 'stripe')->first();

    $stripeInfo->update([
      'information' => json_encode($information),
      'status' => $request->stripe_status
    ]);

    $array = [
      'STRIPE_KEY' => $request->stripe_key,
      'STRIPE_SECRET' => $request->stripe_secret
    ];

    setEnvironmentValue($array);
    Artisan::call('config:clear');

    Session::flash('success', 'Updated Stripe Informaion Successfully');

    return redirect()->back();
  }

  public function updatePaytmInfo(Request $request)
  {
    $rules = [
      'paytm_status' => 'required',
      'paytm_environment' => 'required',
      'paytm_merchant_key' => 'required',
      'paytm_merchant_mid' => 'required',
      'paytm_merchant_website' => 'required',
      'paytm_industry_type' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['environment'] = $request->paytm_environment;
    $information['merchant_key'] = $request->paytm_merchant_key;
    $information['merchant_mid'] = $request->paytm_merchant_mid;
    $information['merchant_website'] = $request->paytm_merchant_website;
    $information['industry_type'] = $request->paytm_industry_type;

    $paytmInfo = OnlineGateway::where('keyword', 'paytm')->first();

    $paytmInfo->update([
      'information' => json_encode($information),
      'status' => $request->paytm_status
    ]);

    $array = [
      'PAYTM_ENVIRONMENT' => $request->paytm_environment,
      'PAYTM_MERCHANT_KEY' => $request->paytm_merchant_key,
      'PAYTM_MERCHANT_ID' => $request->paytm_merchant_mid,
      'PAYTM_MERCHANT_WEBSITE' => $request->paytm_merchant_website,
      'PAYTM_INDUSTRY_TYPE' => $request->paytm_industry_type
    ];

    setEnvironmentValue($array);
    Artisan::call('config:clear');

    Session::flash('success', 'Updated Paytm Informaion Successfully');

    return redirect()->back();
  }

  public function updateMidtransInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'is_production' => 'required',
      'server_key' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['is_production'] = $request->is_production;
    $information['server_key'] = $request->server_key;

    $data = OnlineGateway::where('keyword', 'midtrans')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    Session::flash('success', 'Updated Midtrans Information Successfully');

    return redirect()->back();
  }

  public function updatePaytabsInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'server_key' => 'required',
      'profile_id' => 'required',
      'api_endpoint' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['server_key'] = $request->server_key;
    $information['profile_id'] = $request->profile_id;
    $information['api_endpoint'] = $request->api_endpoint;

    $data = OnlineGateway::where('keyword', 'paytabs')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    Session::flash('success', 'Updated Paytabs Information Successfully');

    return redirect()->back();
  }

  public function updateToyyibpayInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'sandbox_status' => 'required',
      'secret_key' => 'required',
      'category_code' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['sandbox_status'] = $request->sandbox_status;
    $information['secret_key'] = $request->secret_key;
    $information['category_code'] = $request->category_code;

    $data = OnlineGateway::where('keyword', 'toyyibpay')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    Session::flash('success', 'Updated Toyyibpay Information Successfully');

    return redirect()->back();
  }

  public function updateIyzicoInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'sandbox_status' => 'required',
      'api_key' => 'required',
      'secret_key' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['sandbox_status'] = $request->sandbox_status;
    $information['api_key'] = $request->api_key;
    $information['secret_key'] = $request->secret_key;

    $data = OnlineGateway::where('keyword', 'iyzico')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    Session::flash('success', 'Updated Iyzico Information Successfully');

    return redirect()->back();
  }
  public function updatePhonepeInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'sandbox_status' => 'required',
      'merchant_id' => 'required',
      'salt_key' => 'required',
      'salt_index' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['merchant_id'] = $request->merchant_id;
    $information['sandbox_status'] = $request->sandbox_status;
    $information['salt_key'] = $request->salt_key;
    $information['salt_index'] = $request->salt_index;

    $data = OnlineGateway::where('keyword', 'phonepe')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    Session::flash('success', 'Updated Phonepe Information Successfully');

    return redirect()->back();
  }
  public function updateYocoInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'secret_key' => 'required',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['secret_key'] = $request->secret_key;

    $data = OnlineGateway::where('keyword', 'yoco')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    Session::flash('success', 'Updated Yoco Information Successfully');

    return redirect()->back();
  }
  public function updateXenditInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'secret_key' => 'required',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information['secret_key'] = $request->secret_key;

    $data = OnlineGateway::where('keyword', 'xendit')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    $array = [
      'XENDIT_SECRET_KEY' => $request->secret_key,
    ];

    setEnvironmentValue($array);
    Artisan::call('config:clear');

    Session::flash('success', 'Updated Xendit Information Successfully');

    return redirect()->back();
  }
  public function updateMyFatoorahInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'sandbox_status' => 'required',
      'token' => 'required',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information = [
      'token' => $request->token,
      'sandbox_status' => $request->sandbox_status
    ];

    $data = OnlineGateway::where('keyword', 'myfatoorah')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    $array = [
      'MYFATOORAH_TOKEN' => $request->token,
      'MYFATOORAH_CALLBACK_URL' => route('myfatoorah_callback'),
      'MYFATOORAH_ERROR_URL' => route('myfatoorah_cancel'),
    ];

    setEnvironmentValue($array);
    Artisan::call('config:clear');

    Session::flash('success', 'Updated MyFatoorah Information Successfully');

    return redirect()->back();
  }

  public function updatePerfectMoneyInfo(Request $request)
  {
    $rules = [
      'status' => 'required',
      'perfect_money_wallet_id' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $information = [
      'perfect_money_wallet_id' => $request->perfect_money_wallet_id
    ];

    $data = OnlineGateway::where('keyword', 'perfect_money')->first();

    $data->update([
      'information' => json_encode($information),
      'status' => $request->status
    ]);

    Session::flash('success', 'Updated Perfect Money Information Successfully');

    return redirect()->back();
  }
}
