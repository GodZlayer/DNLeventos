<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
  /**
   * The URIs that should be excluded from CSRF verification.
   *
   * @var array
   */
  protected $except = [
    '*/flutterwave/notify',
    '*/razorpay/notify',
    '*/mercadopago/notify',
    '*/paytm/notify',
    'organizer/check-qrcode/',
    '*/iyzico/notify',
    '*/paytabs/notify/',
    '*/phonepe/notify',
    '/xendit/callback'
  ];
}
