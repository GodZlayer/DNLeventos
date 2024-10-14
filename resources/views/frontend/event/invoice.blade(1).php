<!DOCTYPE html>
<html>

<head lang="{{ $currentLanguageInfo->code }}" @if ($currentLanguageInfo->direction == 1) dir="rtl" @endif>
  {{-- required meta tags --}}
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  {{-- title --}}
  <title>{{ 'Invoice | ' . config('app.name') }}</title>

  {{-- fav icon --}}
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/admin/img/' . $websiteInfo->favicon) }}">

  {{-- styles --}}
  <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/invoice.css') }}">
  <style>
    body {
      font-size: 15px;
    }

    .border {
      border-color: #565656 !important;
    }

    .bg-primary {
      background: #{{ $basicInfo->primary_color }} !important;
    }

    p {
      font-size: 12px;
      margin-bottom: 10px;
    }
  </style>
</head>

<body>
  <div class="my-5">
    <div class="row">
      <div class="col-lg-12">
        <div class="logo text-center" style="margin-bottom: 35px;">
          <img src="{{ asset('assets/admin/img/' . $websiteInfo->logo) }}" alt="Company Logo">
        </div>

        @php
          $position = $bookingInfo ? $bookingInfo->currencyTextPosition : 'left';
          $currency = $bookingInfo ? $bookingInfo->currencyText : '';
        @endphp

        <div class="clearfix">
          {{-- enrolment details start --}}
          <div class="float-left px-1" style="width: 47%">
            <div class="p-3 border mt-5 mb-2">
              <h6 class="mt-2 mb-3">{{ __('Booking Details') }}</h6>
              @if ($bookingInfo)
                <p>
                  {{ __('Booking ID') . ': ' }} <span class="text-muted">{{ '#' . $bookingInfo->booking_id }}</span>
                </p>
                <p>
                  {{ __('Booking Date') . ': ' }} <span class="text-muted">{{ date_format($bookingInfo->created_at, 'M d, Y') }}</span>
                </p>

                <p>
                  {{ __('Event Name') . ': ' }} <span class="text-muted">{{ @$eventInfo->title }}</span>
                </p>

                <p>
                  {{ __('Event Start Date') . ': ' }} <span class="text-muted">
                    {{ FullDateTimeInvoice($bookingInfo->event_date) }}
                  </span>
                </p>
              @else
                <p>Informações da reserva não disponíveis.</p>
              @endif
            </div>
          </div>
          {{-- billing details start --}}
          <div class="float-right px-1" style="width: 47%">
            <div class="p-3 border mt-5 mb-2">
              <div class="logo text-center" style="margin-bottom: 35px;">
                @if (isset($qrCodePath) && file_exists($qrCodePath))
                  <img src="{{ asset('assets/admin/qrcodes/test.svg') }}" alt="">
                @else
                  <p>QR Code não disponível.</p>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
