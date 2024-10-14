@extends('layouts.main')
@section('title')
    {{ __('System Update') }}
@endsection

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <p class="card-header-new-style"> {{ __('System Update') }}</p>
                    </div>
                    
                    <div class="col-12 col-md-6 d-flex justify-content-end">
                        {{ __('System Version') }} : <span class="text-danger">{{ $system_version['system_version'] ?? '1.0.0' }}
                    </div>
                </div>
            </div>
            <form class="create-form" action="{{ route('system-update.index') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="card-body">
                    <div class="row mt-1">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="purchase_code"
                                        class="col-sm-2 col-md-12 col-form-label ">{{ __('Purchase Code') }}</label>
                                    <div class="col-sm-3 col-md-12">
                                        <input id="purchase_code" required name="purchase_code" type="text"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6"> 
                                    <label class="col-sm-2 col-form-label ">{{ __('Update File') }}</label>
                                    <div class="col-sm-3 col-md-12">
                                        <input required name="file" type="file" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-2 col-md-12 d-flex justify-content-end  mt-2">
                                    <button type="submit" name="btnAdd1" value="btnAdd"
                                        class="btn btn-primary me-1 mb-1">{{ __('Save') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
