<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
  
    $admin_logo = getadminSettings('admin_logo');
    $favicon = getadminSettings('favicon');

    $admin_logo = $admin_logo['admin_logo'];
    $favicon = $favicon['favicon'];
  
@endphp
    <link rel="shortcut icon" href="{{ $favicon ?? url('assets/images/FavIcon.png') }}" type="image/x-icon">
    <title>@yield('title') || {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @include('layouts.include')
    @yield('css')
</head>
<body>
<div id="app">
    @include('layouts.sidebar')
    <div id="main" class='layout-navbar'>
        @include('layouts.topbar')
        <div id="main-content">
            <div class="page-heading">
                @yield('page-title')
            </div>
            @yield('content')
        </div>
    </div>
    <div class="wrapper mt-5">
        <div class="content">
            @include('layouts.footer')
        </div>
    </div>
</div>
@include('layouts.footer_script')
@yield('js')
@yield('script')
</body>
</html>

