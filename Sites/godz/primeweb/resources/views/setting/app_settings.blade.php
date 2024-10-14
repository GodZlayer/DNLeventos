@extends('layouts.main')
@section('title')
    {{ __('App Settings') }}
@endsection
@section('content')
    <section class="section">
        <form action="{{ route('setting.store') }}" method="post" class="create-form">
            @csrf
            <div class="card">
                <div class="card-header">
                    <p class="card-header-new-style">{{ __('App Settings') }}</p>
                </div>
                <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mandatory">
                                <label for="android_app_version"
                                    class="mandatory form-label">{{ __('Android App Version') }}</label>
                                <input type="text" name="android_app_version" id="android_app_version"
                                    class="form-control"
                                    value="{{ isset($settings['android_app_version']) ? $settings['android_app_version'] : '' }}"
                                    placeholder="{{ __('App Version') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mandatory">
                                <label for="ios_app_version"
                                    class="mandatory form-label">{{ __('IOS App Version') }}</label>
                                <input type="text" name="ios_app_version" id="ios_app_version" class="form-control"
                                    value="{{ isset($settings['ios_app_version']) ? $settings['android_app_version'] : '' }}"
                                    placeholder="{{ __('IOS App Version') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mandatory">
                                <label for="android_app_link"
                                    class="mandatory form-label">{{ __('Android App Link') }}</label>
                                <input type="text" name="android_app_link" id="android_app_link"
                                    class="form-control"
                                    value="{{ isset($settings['android_app_link']) ? $settings['android_app_link'] : '' }}"
                                    placeholder="{{ __('Android App Link') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mandatory">
                                <label for="ios_app_link"
                                    class="mandatory form-label">{{ __('IOS App Link') }}</label>
                                <input type="text" name="ios_app_link" id="ios_app_link"
                                    class="form-control"
                                    value="{{ isset($settings['ios_app_link']) ? $settings['ios_app_link'] : '' }}"
                                    placeholder="{{ __('IOS App Link') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="mandatory form-label">{{ __('Force Update') }}</label><br>
                            <div class="form-check form-switch">
                                <input type="hidden" name="app_force_update" id="app_force_update"
                                    value="{{ isset($settings['app_force_update']) && $settings['app_force_update'] == 1 ? 1 : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    {{ isset($settings['app_force_update']) && $settings['app_force_update'] == 1 ? 'checked' : '' }}
                                    id="switch_app_force_update">
                                <label class="form-check-label" for="switch_app_force_update"></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="mandatory form-label">{{ __('App Maintenance Mode') }}</label><br>
                            <div class="form-check form-switch">
                                <input type="hidden" name="app_maintenance_mode" id="app_maintenance_mode"
                                    value="{{ isset($settings['app_maintenance_mode']) && $settings['app_maintenance_mode'] == 1 ? 1 : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    {{ isset($settings['app_maintenance_mode']) && $settings['app_maintenance_mode'] == 1 ? 'checked' : '' }}
                                    id="switch_app_maintenance_mode">
                                <label class="form-check-label" for="switch_app_maintenance_mode"></label>
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
@section('script')
    <script type="text/javascript">
        $("#switch_app_maintenance_mode").on('change', function() {
            $("#app_maintenance_mode").val($(this).is(':checked') ? 1 : 0);
        });
        $("#switch_app_force_update").on('change', function() {
            $("#app_force_update").val($(this).is(':checked') ? 1 : 0);
        });
    </script>
@endsection
