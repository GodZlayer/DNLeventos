<?php

namespace Config;

use App\Models\PaymentGateway\OnlineGateway;

class Iyzipay
{
  public static function options()
  {
    $data = OnlineGateway::where('keyword', 'iyzico')->first();
    $information = json_decode($data->information, true);

    $options = new \Iyzipay\Options();
    $options->setApiKey("sandbox-nhwvNYFN8EdyUm0MXVon9u9wNt6HTKrl");
    $options->setSecretKey("sandbox-nZ69wQYaUbxqKbOoHJmc9CjQZtgcSloC");
    $options->setBaseUrl("https://sandbox-api.iyzipay.com");
    $options->setApiKey($information['api_key']);
    $options->setSecretKey($information['secret_key']);
    if ($information['sandbox_status'] == 1) {
      $options->setBaseUrl("https://sandbox-api.iyzipay.com");
    } else {
      $options->setBaseUrl("https://api.iyzipay.com"); // production mode
    }

    return $options;
  }
}
