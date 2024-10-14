@extends('layouts.main')
@section('title')
    {{ __('Admob') }}
@endsection
@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <p class="card-header-new-style"> {{ __('Ads Setup') }}</p>
                    </div>
                </div>
            </div>
            <form action="{{ route('app_settings.store') }}" method="post" class="create-form">
                @csrf
                <div class="card-body">
                    <div class=row>
                        <div class="col-md-4 form-group mandatory">
                            <label for="admob_app_id_android"
                                class="form-check-label">{{ __('Admob App Id Android') }}</label>
                            <input id="admob_app_id_android" name="admob_app_id_android" type="text" class="form-control"
                                placeholder="{{ __('Admob App Id Android') }}"
                                value="{{ $appsettings['admob_app_id_android'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group mandatory">
                            <label for="banner_ad_id_android"
                                class="col-md-4 form-check-label">{{ __('Banner Ad Id Android') }}</label>
                            <input id="banner_ad_id_android" name="banner_ad_id_android" type="text" class="form-control"
                                placeholder="{{ __('Banner Ad Id Android') }}"
                                value="{{ $appsettings['banner_ad_id_android'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group mandatory">
                            <label for="interstitial_ad_id_android"
                                class="form-check-label">{{ __('Interstitial Ad Id Android') }}</label>
                            <input id="interstitial_ad_id_android" name="interstitial_ad_id_android" type="text"
                                class="form-control" placeholder="{{ __('Interstitial Ad Id Android') }}"
                                value="{{ $appsettings['interstitial_ad_id_android'] ?? '' }}">
                        </div>
                    </div>
                    <div class=row>
                        <div class="col-md-4 form-group mandatory">
                            <label for="admob_app_id_ios" class="form-check-label">{{ __('Admob App Id iOS') }}</label>
                            <input id="admob_app_id_ios" name="admob_app_id_ios" type="text" class="form-control"
                                placeholder="{{ __('Admob App Id iOS') }}"
                                value="{{ $appsettings['admob_app_id_ios'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group mandatory">
                            <label for="banner_ad_id_ios" class="form-check-label">{{ __('Banner Ad Id iOS') }}</label>
                            <input id="banner_ad_id_ios" name="banner_ad_id_ios" type="text" class="form-control"
                                placeholder="{{ __('Banner Ad Id iOS') }}"
                                value="{{ $appsettings['banner_ad_id_ios'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group mandatory">
                            <label for="interstitial_ad_id_ios"
                                class="form-check-label">{{ __('Interstitial Ad Id iOS') }}</label>
                            <input id="interstitial_ad_id_ios" name="interstitial_ad_id_ios" type="text"
                                class="form-control" placeholder="{{ __('Interstitial Ad Id iOS') }}"
                                value="{{ $appsettings['interstitial_ad_id_ios'] ?? '' }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-12">
                            <div class="radio-group mandatory">
                                <label class="mandatory form-label">{{ __('Admob App Ad Status') }}</label><br>
                                <input type="radio" id="admob_ad_status_active" name="admob_ad_status" value="true"
                                    class="form-check-input" checked
                                    {{ isset($appsettings['admob_ad_status']) && $appsettings['admob_ad_status'] == 'true' ? 'checked' : '' }}>
                                <label for="admob_ad_status_active" class="form-check-label">{{ __('Active') }}</label>
                                <input type="radio" name="admob_ad_status" id="admob_ad_status_deactive" value="false"
                                    class="form-check-input"
                                    {{ isset($appsettings['admob_ad_status']) && $appsettings['admob_ad_status'] == 'false' ? 'checked' : '' }}>
                                <label for="admob_ad_status_inactive" class="form-check-label">{{ __('Inactive') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="radio-group mandatory">
                                <label class="mandatory form-label">{{ __('Banner Ad Status') }}</label><br>
                                <input type="radio" name="banner_ad_status" id="banner_ad_status_active"value="true"
                                    class="form-check-input" checked
                                    {{ isset($appsettings['banner_ad_status']) && $appsettings['banner_ad_status'] == 'true' ? 'checked' : '' }}>
                                <label for="banner_ad_status_active" class="form-check-label">{{ __('Active') }}</label>
                                <input type="radio" name="banner_ad_status" id="banner_ad_status_deactive"
                                    value="false" class="form-check-input"
                                    {{ isset($appsettings['banner_ad_status']) && $appsettings['banner_ad_status'] == 'false' ? 'checked' : '' }}>
                                <label for="banner_ad_status_inactive"
                                    class="form-check-label">{{ __('Inactive') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="radio-group mandatory">
                                <label class="mandatory form-label">{{ __('Interstitial Ad Status') }}</label><br>
                                <input type="radio" name="interstitial_ad_status" id="interstitial_ad_status_active"
                                    value="true" class="form-check-input" checked
                                    {{ isset($appsettings['interstitial_ad_status']) && $appsettings['interstitial_ad_status'] == 'true' ? 'checked' : '' }}>
                                <label for="interstitial_ad_status_active"
                                    class="form-check-label">{{ __('Active') }}</label>
                                <input type="radio" name="interstitial_ad_status" id="interstitial_ad_status_deactive"
                                    value="false" class="form-check-input"
                                    {{ isset($appsettings['interstitial_ad_status']) && $appsettings['interstitial_ad_status'] == 'false' ? 'checked' : '' }}>
                                <label for="interstitial_ad_status_inactive"
                                    class="form-check-label">{{ __('Inactive') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-2 d-flex justify-content-end">
                        <button class="btn btn-primary me-1 mb-1" type="submit"
                            name="submit">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
