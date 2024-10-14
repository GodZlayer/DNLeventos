@extends('layouts.main')
@section('title')
    {{ __('General Settings') }}
@endsection
@section('content')
    <section class="section">
        <form action="{{ route('setting.store') }}" method="post" class="create-form">
            @csrf
        <div class="card">
            <div class="card-header">
                <p class="card-header-new-style"> {{ __('Setting') }}</p>
            </div>
            <div class="card-body mt-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mandatory">
                            <label for="admin_logo" class="mandatory form-label">{{ __('Admin Logo') }}</label>
                            <div class="cs_field_img">
                                <div class="img_input"><i class="fas fa-upload"></i> Upload</div>
                                <input type="file" name="admin_logo" class="image" style="display: none" accept=".jpg, .jpeg, .png, .svg">
                                <img src="{{ $settings['admin_logo'] ?  $settings['admin_logo'] : asset('assets/images/image_preview1.png') }}" alt="" class="img preview-image">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mandatory">
                            <label for="favicon" class="mandatory form-label">{{ __('Favicon') }}</label>
                            <div class="cs_field_img">
                                <div class="img_input"><i class="fas fa-upload"></i> Upload</div>
                                <input type="file" name="favicon" class="image" style="display: none" accept=".jpg, .jpeg, .png, .svg">
                                <img src="{{ $settings['favicon'] ? $settings['favicon'] : asset('assets/images/image_preview1.png') }}" alt="" class="img preview-image">
                            </div>
                        </div>
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
