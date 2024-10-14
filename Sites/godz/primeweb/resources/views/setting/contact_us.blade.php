@extends('layouts.main')
@section('title')
    {{ __('Contact Us') }}
@endsection
@section('content')
    <section class="section">
        <form action="{{ route('setting.store') }}" method="post" class="create-form">
            @csrf
            <div class="card">
                <div class="card-header">
                    <p class="card-header-new-style"> {{ __('Contact Us') }}</p>
                </div>
                <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 form-group mandatory">
                            <textarea name="contact_us" id="tinymce_editor" class=" form-control form-label col-12" data-parsley-required= 'true'>
                                {{isset($settings['contact_us'])?$settings['contact_us']:''}}
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
