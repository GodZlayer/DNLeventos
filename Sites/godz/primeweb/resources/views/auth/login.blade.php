<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="{{ $favicon ?? url('assets/images/logo/logo.png') }}" type="image/x-icon">
        <title>Login</title>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('assets/css/main/app.css') }}">
        <link rel="stylesheet" href=" {{ asset('assets/css/pages/auth.css') }}">
        <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
        @php
            $theme_primary_color = getSettings('theme_color');
            $theme_Background_color = getSettings('backgroundcolor');
            $admin_logo = getadminSettings('admin_logo');
            $favicon = getadminSettings('favicon');
            $theme_primary_color = $theme_primary_color['theme_color'];
            $theme_Background_color = $theme_Background_color['backgroundcolor'];
            $admin_logo = $admin_logo['admin_logo'];
            $favicon = $favicon['favicon'];
        @endphp
        <style>
            :root {
                --bs-primary: <?=$theme_primary_color ?>;
                --primary-color: <?=$theme_primary_color ?>;
            }
        </style>
    </head>
    <body>
        <div id="auth" class="login_bg" style="background-image: url('{{$login_bg_image??''}}');">
            <img src="{{$login_bg_image ?? ''}}" data-custom-image="{{asset('assets/images/bg/login.jpg')}}" alt="" style="display: none" id="bg_image">
            
            <div class="justify-content-md-center justify-content-sm-center login-box d-flex align-items-center">
                
                <div class="col-lg-3 col-12 card" id="auth-box">
                    <div class="auth-logo mb-5 d-block">
                        <img id="admin_logo" src="{{ $admin_logo ?? asset('assets/images/logo/logo1.png') }}"
                            alt="Logo">
                    </div>
                    <div class="center mtop-75">
                        <div class='login_heading'>
                            <h3>Hi, Welcome Back!</h3>
                            <p>Enter your details to sign in to your account.</p>
                        </div>
                        <div class="pt-4">
                            <form method="POST" action="{{ route('login') }}" id="frmLogin">
                                @csrf
                                <div class="form-group position-relative  mb-4">
                                    <input id="email" type="email" placeholder="{{ __('Email address') }}"
                                        class="form-control login-border form-input @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" required autocomplete="email"
                                        autofocus>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-group position-relative  has-icon-right mb-4" id="pwd">
                                    <input id="password" type="password" placeholder="Password"
                                        class="form-control login-border form-input @error('password') is-invalid @enderror"
                                        name="password" required autocomplete="current-password">
                                    @error('password')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                    <div class="form-control-icon icon-right">
                                        <i class="bi bi-eye" id='toggle_pass'></i>
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-block btn-sm shadow-lg mt-3 login_btn">Log
                                    in</button>
                                @if (config('app.demo_mode'))
                                    <div class="text-danger text-center mt-2" role="alert">
                                        If you cannot login, then
                                        <a class="text-decoration-underline" target="_blank"
                                            href="{{ Request::root() }}">
                                            Click Here.
                                        </a>
                                        <br>
                                    </div>
                                @endif
                                @if (config('app.demo_mode'))
                                    <div class="row mt-3">
                                        <hr class="w-100">
                                        <div class="col-12 text-center text-black-50">Demo Credentials</div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button class="btn w-100 btn-primary mt-2" id="admin_btn">Admin</button>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $("#toggle_pass").on('click', function() {
                $(this).toggleClass("bi bi-eye bi-eye-slash");
                let input = $('[name="password"]');
                if (input.attr("type") == "password") {
                    input.attr("type", "text");
                } else {
                    input.attr("type", "password");
                }
            });
            $('#bg_image').on('error', function() {
                this.src = $(this).data('custom-image');
                $('.login_bg').css('background-image', "url(" + $(this).data('custom-image') + ")");
            });
            $('#admin_logo').on('error', function() {
                this.src = $(this).data('custom-image');
            });
            @if (config('app.demo_mode'))
                $('#admin_btn').on('click', function() {
                    $('#email').val('admin@gmail.com');
                    $('#password').val('admin123');
                    $('.login_btn').attr('disabled', true);
                    $(this).attr('disabled', true);
                    $('#frmLogin').submit();
                })
            @endif
        </script>
    </body>
</html>
