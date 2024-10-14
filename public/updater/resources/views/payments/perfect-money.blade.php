<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>{{ $websiteInfo->website_title }}</title>
</head>

<body>
  <form action="{{ $data['url'] }}" method="{{ $data['method'] }}" id="paymentForm">
    @foreach ($data['val'] as $key => $value)
      <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
    @endforeach
  </form>
  <script>
    "use strict";
    document.getElementById("paymentForm").submit();
  </script>
</body>

</html>
