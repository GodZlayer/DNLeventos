@extends('layouts.main')
@section('title')
    {{ __('Privacy Policy') }}
@endsection
@section('content')
    <section class="section">
        <form action="{{ route('setting.store') }}" method="post" class="create-form">
            @csrf
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <p class="card-header-new-style"> {{ __('Privacy Policy') }}</p>
                    <a href="{{ url('page/privacy-policy') }}" class="btn btn-primary me-1 mb-1" target="_blank">
                        <i class="fas fa-eye me-1"></i>{{ __('Preview') }}
                    </a>
                </div>
                <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 form-group mandatory">
                            <textarea name="privacy_policy" id="tinymce_editor" class=" form-control form-label col-12"
                                data-parsley-required= 'true'>
                                {{ isset($settings['privacy_policy']) ? $settings['privacy_policy'] : '' }}
                            </textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mt-2 d-flex justify-content-end">
                            <button class="btn btn-primary me-1 mb-1" type="submit" name="submit">{{ __('Save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </form>
    </section>
@endsection
