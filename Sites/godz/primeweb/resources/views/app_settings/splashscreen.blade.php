@extends('layouts.main')
@section('title')
    {{ __('Splash Screen') }}
@endsection
@section('content')
    <section class="section">
        <div class="card col-md-12">
            <div class="card-header">
                <p class="card-header-new-style">{{ __('Splash Screen') }}</p>
            </div>
            <form class="create-form" action="{{ route('app_settings.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="radio-group">
                                        <label class="form-label">{{ __('Select Background') }}</label>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="backgroundColor" name="backgroundType" value="color"
                                                class="form-check-input"
                                                {{ old('backgroundType', $appsettings['backgroundType'] ?? 'color') == 'color' ? 'checked' : '' }}>
                                            <label for="backgroundColor"
                                                class="form-check-label">{{ __('Color') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="backgroundImage" name="backgroundType" value="image"
                                                class="form-check-input"
                                                {{ old('backgroundType', $appsettings['backgroundType'] ?? '') == 'image' ? 'checked' : '' }}>
                                            <label for="backgroundImage"
                                                class="form-check-label">{{ __('Image') }}</label>
                                        </div>
                                    </div>

                                    <div id="colorFields" class="conditional-fields">
                                        <div class="col-md-12 col-12">
                                            <div class="form-group mandatory">
                                                <label for="color_code" class="form-label">{{ __('Primary Color') }}
                                                </label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <input type="color" name="primarycolor" id="primarycolor"
                                                            class="form-control color-input"
                                                            value="{{ $appsettings['primarycolor'] ?? '#ae590a' }}">
                                                    </div>
                                                    <input type="text" name="color_code" id="primaryColorCode"
                                                        class="form-control"
                                                        value="{{ $appsettings['primarycolor'] ?? '#ae590a' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-12">
                                            <div class="form-group mandatory">
                                                <label for="color_code" class="form-label">{{ __('Secondary Color') }}
                                                </label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <input type="color" name="secondaryColor" id="secondaryColor"
                                                            class="form-control color-input"
                                                            value="{{ $appsettings['secondaryColor'] ?? '#ae590a' }}">
                                                    </div>
                                                    <input type="text" name="color_code" id="secondaryColorCode"
                                                        class="form-control"
                                                        value="{{ $appsettings['secondaryColor'] ?? '#ae590a' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="imageFields" class="conditional-fields" style="display: none;">
                                        <div class="form-group mandatory">
                                            <div class="cs_field_img1">
                                                <div class="img_input"><i class="fas fa-upload "></i> Upload</div>
                                                <input type="file" id="backgroundImageUpload"
                                                    name="backgroundImageUpload" class="image" style="display: none"
                                                    accept=".jpg, .jpeg, .png, .svg">
                                                    <img src="{{ $appsettings['backgroundImageUpload'] ?? 'assets/images/image_preview.png' }}"
                                                    alt="" class="img preview-image" id="backgroundImageUpload1">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row align-items-center">
                                        <div class="form-check form-switch ms-3">
                                            <label class="form-label mb-0">{{ __('Add App Logo') }}</label>
                                            <input id="include_image" name="include_image" type="checkbox"
                                                class="form-check-input" role="switch"
                                                {{ old('include_image', $appsettings['include_image'] ?? false) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div id="appLogoFields" class="conditional-fields" style="display: none;">
                                        <div class="form-group mandatory">
                                            <div class="cs_field_img1">
                                                <div class="img_input"> <i class="fas fa-upload "></i>  {{ __('Upload') }}</div>
                                                <input type="file" id="applogo" name="applogo" class="image"
                                                    style="display: none" accept=".jpg, .jpeg, .png, .svg">
                                                <img src="{{ $appsettings['applogo'] ?? 'assets/images/image_preview.png' }}"
                                                    alt="" class="img preview-image" id="appLogoPreview">
                                            </div>
                                        </div>
                                    </div>
                                    <div class=" mt-4 d-flex justify-content-end">
                                        <button class="btn btn-primary me-1 mb-1" type="submit"
                                            name="submit">{{ __('Save') }}</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card splash-screen-preview" id="splashScreenPreview">
                                <img src= "{{ $appsettings['applogo'] ?? 'assets/images/image_preview.png' }}"
                                    alt="App Logo" id="previewAppLogo">
                                <img src="{{ $appsettings['backgroundImageUpload'] ?? 'assets/images/image_preview.png' }}" alt="" class="img preview-image"
                                    id="backgroundImagePreview">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            function toggleBackgroundFields() {
                if ($('input[name="backgroundType"]:checked').val() === 'color') {
                    $('#colorFields').show();
                    $('#imageFields').hide();
                } else if ($('input[name="backgroundType"]:checked').val() === 'image') {
                    $('#colorFields').hide();
                    $('#imageFields').show();
                }
            }
            $('input[name="backgroundType"]').on('change', function() {
                toggleBackgroundFields();
            });
            $('#include_image').on('change', function() {
                $('#appLogoFields').toggle(this.checked);
            }).trigger('change');

            function readURL(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#' + previewId).attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
            $('#backgroundImagePreview').on('click', function() {
                $('#backgroundImageUpload').click();
            });
            $('#appLogoPreview').on('click', function() {
                $('#applogo').click();
            });
            $('#backgroundImageUpload').on('change', function() {
                readURL(this, 'backgroundImagePreview');
            });
            $('#applogo').on('change', function() {
                readURL(this, 'appLogoPreview');
            });
            $('#primaryColor').on('input', function() {
                $('#primaryColorCode').val($(this).val());
            });
            $('#secondaryColor').on('input', function() {
                $('#secondaryColorCode').val($(this).val());
            });
            toggleBackgroundFields();
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const primaryColorInput = document.getElementById('primarycolor');
            const primaryColorCode = document.getElementById('primaryColorCode');
            const secondaryColorInput = document.getElementById('secondaryColor');
            const secondaryColorCode = document.getElementById('secondaryColorCode');
            const splashScreenPreview = document.getElementById('splashScreenPreview');
            const appLogoToggle = document.getElementById('appLogoToggle');
            const appLogoInput = document.getElementById('applogo');
            const previewAppLogo = document.getElementById('previewAppLogo');
            const backgroundImageUpload = document.getElementById('backgroundImageUpload');
            const backgroundImagePreview = document.getElementById('backgroundImagePreview');
            const backgroundColorRadio = document.getElementById('backgroundColor');
            const backgroundImageRadio = document.getElementById('backgroundImage'); // function updatePreview() {
            function updatePreview() {
                if (backgroundColorRadio.checked) {


                    const primaryColor = primaryColorInput.value;
                    const secondaryColor = secondaryColorInput.value;
                    splashScreenPreview.style.background =
                        `linear-gradient(315deg, ${primaryColor} -6.98%, ${secondaryColor} 99.08%)`;
                    backgroundImagePreview.style.display = 'none';
                } else if (backgroundImageRadio.checked) {
                    splashScreenPreview.style.background = 'none';
                    backgroundImagePreview.style.display = 'block';
                }
            }

            updatePreview();
            primaryColorInput.addEventListener('input', function() {
                primaryColorCode.value = primaryColorInput.value;
                updatePreview();
            });
            primaryColorCode.addEventListener('input', function() {
                primaryColorInput.value = primaryColorCode.value;
                updatePreview();
            });
            secondaryColorInput.addEventListener('input', function() {
                secondaryColorCode.value = secondaryColorInput.value;
                updatePreview();
            });
            secondaryColorCode.addEventListener('input', function() {
                secondaryColorInput.value = secondaryColorCode.value;
                updatePreview();
            });
            backgroundImageUpload.addEventListener('change', function() {
                const file = backgroundImageUpload.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        backgroundImagePreview.src = e.target.result;
                        updatePreview();
                    };
                    reader.readAsDataURL(file);
                }
            });

            appLogoInput.addEventListener('change', function() {
                const file = appLogoInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewAppLogo.src = e.target.result;
                        previewAppLogo.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewAppLogo.style.display = 'none';
                }
            });
        });
    </script>
@endsection