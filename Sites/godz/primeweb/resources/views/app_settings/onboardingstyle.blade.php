@extends('layouts.main')
@section('title')
@endsection
@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
            </div>
        </div>
    </div>
@endsection
@section('content')
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <form action="{{ route('app_settings.store') }}" method="post" class="create-form">
                        @csrf
                        <div class="card-header">
                            <h5>{{ __('Onboarding style') }}</h5>
                        </div>
                        <div class="card-body mt-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card onboarding-style">
                                        <div class="card-body onboarding-style-card {{ isset($appsettings['style']) && $appsettings['style'] == 'style1' ? 'onbording_style_image' : '' }}"
                                            style="">
                                            <h5>Style 1<input type="checkbox" name="style" value="style1"
                                                    class="d-flex form-check-input float-end onboarding-checkbox"
                                                    {{ isset($appsettings['style']) && $appsettings['style'] == 'style1' ? 'checked' : '' }}>
                                            </h5>
                                            <img src="{{ asset('assets/images/Style1.png') }}" class="onbording_style_image_img_thumbnail_img_fluid mt-2"
                                                alt="Style 1">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card onboarding-style">
                                        <div
                                            class="card-body  onboarding-style-card {{ isset($appsettings['style']) && $appsettings['style'] == 'style2' ? 'onbording_style_image' : '' }}">
                                            <h5>Style 2<input type="checkbox" name="style" value="style2"
                                                    class="d-flex form-check-input float-end onboarding-checkbox"
                                                    {{ isset($appsettings['style']) && $appsettings['style'] == 'style2' ? 'checked' : '' }}>
                                            </h5>
                                            <img src="{{ asset('assets/images/Style2.png') }}" class="onbording_style_image_img_thumbnail_img_fluid mt-2"
                                                alt="Style 2">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card onboarding-style">
                                        <div
                                            class="card-body onboarding-style-card {{ isset($appsettings['style']) && $appsettings['style'] == 'style3' ? 'onbording_style_image' : '' }}">
                                            <h5>Style 3 <input type="checkbox" name="style" value="style3"
                                                    class="form-check-input float-end onboarding-checkbox"
                                                    {{ isset($appsettings['style']) && $appsettings['style'] == 'style3' ? 'checked' : '' }}>
                                            </h5>
                                            <img src="{{ asset('assets/images/Style3.png') }}" class="onbording_style_image_img_thumbnail_img_fluid" alt="Style 3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <input type="submit" class="btn btn-primary" value="save">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.onboarding-checkbox').on('change', function() {
                $('.onboarding-checkbox').not(this).prop('checked', false);
                updateStyle();
            });
        });
        function updateStyle() {
            $('.card-body').removeClass('onbording_style_image');
            $('.onboarding-checkbox:checked').closest('.card-body').addClass('onbording_style_image');
        }
        updateStyle();
    </script>
@endsection
