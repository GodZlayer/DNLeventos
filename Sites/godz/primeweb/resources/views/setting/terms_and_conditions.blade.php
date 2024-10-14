@extends('layouts.main')
@section('title')
    {{ __('Terms & Conditions') }}
@endsection
@section('content')
    <section class="section">
        <form action="{{ route('setting.store') }}" method="post" class="create-form">
            @csrf
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <p class="card-header-new-style"> {{ __('Terms & Conditions') }}</p>
                    <a href="{{ url('page/terms-conditions') }}" class="btn btn-primary me-1 mb-1" target="_blank">
                        <i class="fas fa-eye me-1"></i>{{ __('Preview') }}
                    </a>
                </div>
                <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 form-group mandatory">
                            <textarea name="terms_and_condition" id="tinymce_editor" class=" form-control form-label col-12" data-parsley-required= 'true'>
                                {{isset($settings['terms_and_condition'])?$settings['terms_and_condition']:''}}
                            </textarea>
                        </div>
                        <div class="col-12 mt-2 d-flex justify-content-end">
                            <button class="btn btn-primary me-1 mb-1" type="submit"
                                name="submit">{{ __('Save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
