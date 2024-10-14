@extends('layouts.main')
@section('title')
    {{ __('Change Password') }}
@endsection
@section('content')
    <section class="section col-sm-12 col-xs-12">
        <div class="row">
            <div class="col-md-6">
                <div class="card ">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <p class="card-header-new-style"> {{ __('Change Profile') }}</p>
                            </div>
                        </div>
                    </div>
                    {{ Form::open(['url' => route('change-profile.update'), 'class' => 'create-form-without-reset', 'files' => true]) }}
                    <div class="row mt-1">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mt-2">
                                    <div class="image-container">
                                        <input type="file" name="profile" id="profileImageInput" class="image"
                                            style="display: none" accept=".jpg, .jpeg, .png, .svg">
                                        <img id="previewImage" style="height:100px;width:100%;border-radius:8px"
                                            src="{{ empty(Auth::user()->profile) ? asset('assets/images/faces/2.jpg') : Auth::user()->profile }}"
                                            alt="" class="img preview-image">
                                        <button type="button" id="uploadButton"
                                            class="btn btn-primary mt-2 w-100">{{ __('Upload') }}</button>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="col-md-12">
                                        <label for="name" class="col-form-label ">{{ __('Name') }}</label>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="text" name="name" id="name"
                                            class="form-control form-control-lg form-control-solid mb-2"
                                            placeholder={{ __('Name') }} value="{{ Auth::user()->name }}" required />
                                    </div>
                                    <div class="col-md-12">
                                        <label for="name" class="col-form-label ">{{ __('Email') }}</label>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="email" name="{{ __('email') }}" id="{{ __('email') }}"
                                            class="form-control form-control-lg form-control-solid mb-2"
                                            placeholder="{{ __('Email') }}" value="{{ Auth::user()->email }}"
                                            required />
                                    </div>
                                <div class="col-sm-12 text-end mt-4">
                                    <button type="submit" name="btnadd" value="btnadd"
                                        class="btn btn-primary float-right">{{ __('Update Profile') }}</button>
                                </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="card ">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <p class="card-header-new-style"> {{ __('Change Password') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-content">
                        {!! Form::open([
                            'url' => route('change-password.update'),
                            'data-parsley-validate',
                            'class' => 'create-form',
                            'data-parsley-validate',
                        ]) !!}
                        <div class="row mt-1">
                            <div class="card-body">


                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="old_password" class="form-label">{{ __('Current Password') }}</label>
                                        <div class="form-group position-relative has-icon-right mb-4 mandatory">
                                            <input type="password" name="old_password" id="old_password"
                                                class="form-control form-control-solid mb-2" value=""
                                                placeholder="{{ __('Current Password') }}" required />
                                            <div class="form-control-icon lh-1 top-0 mt-2">
                                                <i class="bi bi-eye toggle-password"></i>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label">{{ __('New Password') }}</label>
                                        <div class="form-group position-relative has-icon-right mb-4 mandatory">
                                            <input type="password" name="new_password" id="new_password"
                                                class="form-control form-control-solid" value=""
                                                placeholder="{{ __('New Password') }}" data-parsley-minlength="8"
                                                data-parsley-uppercase="1" data-parsley-lowercase="1"
                                                data-parsley-number="1" data-parsley-special="1" data-parsley-required />
                                            <div class="form-control-icon lh-1 top-0 mt-2">
                                                <i class="bi bi-eye toggle-password"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password"
                                            class="form-label">{{ __('Confirm Password') }}</label>
                                        <div class="form-group position-relative has-icon-right mb-4 mandatory">
                                            <input type="password" id="confirm_password" name="confirm_password"
                                                class="form-control form-control-solid" value=""
                                                placeholder="{{ __('Confirm Password') }}"
                                                data-parsley-equalto="#new_password" required />
                                            <div class="form-control-icon lh-1 top-0 mt-2">
                                                <i class="bi bi-eye toggle-password"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>




                                <div class="form-group">
                                    <div class="col-sm-12 text-end">
                                        <button type="submit"
                                            class="btn btn-primary float-right">{{ __('Change Password') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadButton = document.getElementById('uploadButton');
        const fileInput = document.getElementById('profileImageInput');
        const previewImage = document.getElementById('previewImage');
        uploadButton.addEventListener('click', function() {
            fileInput.click();
        });
        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
