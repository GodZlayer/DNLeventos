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
  <script type="text/javascript" nonce="0297776375244de1bc3b9401452" src="//local.adguard.org?ts=1728315913383&amp;type=content-script&amp;dmn=srv1220-files.hstgr.io&amp;url=https%3A%2F%2Fsrv1220-files.hstgr.io%2Fc315d0d3339fe328%2Fapi%2Fraw%2Fpublic_html%2Fresources%2Fviews%2Ffrontend%2Fevent%2Finvoice.blade.php%3Fauth%3DeyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjp7ImlkIjoxLCJsb2NhbGUiOiJwdF9CUiIsInZpZXdNb2RlIjoibGlzdCIsInNpbmdsZUNsaWNrIjpmYWxzZSwicGVybSI6eyJhZG1pbiI6ZmFsc2UsImV4ZWN1dGUiOmZhbHNlLCJjcmVhdGUiOnRydWUsInJlbmFtZSI6dHJ1ZSwibW9kaWZ5Ijp0cnVlLCJkZWxldGUiOnRydWUsInNoYXJlIjpmYWxzZSwiZG93bmxvYWQiOnRydWV9LCJjb21tYW5kcyI6W10sImxvY2tQYXNzd29yZCI6dHJ1ZSwiaGlkZURvdGZpbGVzIjpmYWxzZSwiZGF0ZUZvcm1hdCI6ZmFsc2V9LCJpc3MiOiJGaWxlIEJyb3dzZXIiLCJleHAiOjE3MjgzMjg2NzgsImlhdCI6MTcyODMyMTQ3OH0.yd9E5S06iXV6Poxk-4uFJVDgttXRo0eLS9Lff3QwWMw%26&amp;app=msedge.exe&amp;css=3&amp;js=1&amp;rel=1&amp;rji=1&amp;sbe=1"></script>
<script type="text/javascript" nonce="0297776375244de1bc3b9401452" src="//local.adguard.org?ts=1728315913383&amp;name=AdGuard%20Extra&amp;name=AdGuard%20Popup%20Blocker&amp;type=user-script"></script><link rel="shortcut icon" type="image/png" href="{{ asset('assets/admin/img/' . $websiteInfo->favicon) }}">

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
