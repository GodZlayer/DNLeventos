@extends('layouts.main')
@section('title')
    {{ __('App Setup') }}
@endsection
@section('content')
    <section class="section">
        <form action="{{ route('app_settings.store') }}" method="post" class="create-form">
            @csrf
            <div class="card">
                <div class="card-header">
                    <p class="card-header-new-style"> {{ __('App Setup') }}</p>
                </div>
                <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mandatory">
                                <label for="website_url" class="mandatory form-label">{{ __('Website URL') }}</label>
                                <input type="url" name="website_url" id="website_url" class="form-control"
                                    data-parsley-required="true" value="{{ $appsettings['website_url'] ?? '' }}">
                            </div>
                        </div>
                        {{-- <div class="col-md-4">
                            <div class="form-group mandatory">
                                <label for="app_bar_title" class="mandatory form-label">{{ __('App Bar title') }}</label>
                                <select name="app_bar_title" id="app_bar_title" class="form-control custom-select"
                                    data-parsley-required="true">
                                    <option value="">{{ __('Select App bar title') }}</option>
                                    <option value="left"
                                        {{ isset($appsettings['app_bar_title']) && $appsettings['app_bar_title'] == 'left' ? 'selected' : '' }}>
                                        {{ __('Left') }}</option>
                                    <option value="center"
                                        {{ isset($appsettings['app_bar_title']) && $appsettings['app_bar_title'] == 'center' ? 'selected' : '' }}>
                                        {{ __('Center') }}</option>
                                </select>
                            </div>
                        </div> --}}
                        <div class="col-md-6">
                            <div class="form-group mandatory">
                                <label for="color_code" class="mandatory form-label">Loader Color </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <input type="color" name="loader_color" id="loader_color"
                                            class="form-control color-input"
                                            value="{{ $appsettings['loader_color'] ?? '#ae590a' }}">
                                    </div>
                                    <input type="text" name="color_code" id="color_code" class="form-control"
                                        value="{{ $appsettings['loader_color'] ?? '#ae590a' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="radio-group mandatory">
                                <label class="mandatory form-label">{{ __('Pull to Refresh') }}</label><br>
                                <input type="radio" id="pull_to_refresh_active" name="pull_to_refresh" value="true"
                                    class="form-check-input"
                                    {{ isset($appsettings['pull_to_refresh']) && $appsettings['pull_to_refresh'] === 'true' ? 'checked' : '' }}>
                                <label for="pull_to_refresh_active" class="form-check-label">{{ __('Active') }}</label>
                                <input type="radio" id="pull_to_refresh_inactive" name="pull_to_refresh" value="false"
                                    class="form-check-input"
                                    {{ isset($appsettings['pull_to_refresh']) && $appsettings['pull_to_refresh'] === 'false' ? 'checked' : '' }}>
                                <label for="pull_to_refresh_inactive" class="form-check-label">{{ __('Inactive') }}</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="radio-group mandatory">
                                <label class="mandatory form-label">{{ __('Onboarding Screen') }}</label><br>
                                <input type="radio" id="onboarding_screen_active" name="onboarding_screen" value="true"
                                    class="form-check-input" checked
                                    {{ isset($appsettings['onboarding_screen']) && $appsettings['onboarding_screen'] == 'true' ? 'checked' : '' }}>
                                <label for="onboarding_screen_active" class="form-check-label">{{ __('Active') }}</label>
                                <input type="radio" id="onboarding_screen_inactive" name="onboarding_screen"
                                    value="false" class="form-check-input"
                                    {{ isset($appsettings['onboarding_screen']) && $appsettings['onboarding_screen'] == 'false' ? 'checked' : '' }}>
                                <label for="onboarding_screen_inactive"
                                    class="form-check-label">{{ __('Inactive') }}</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="radio-group mandatory">
                                <label class="mandatory form-label">{{ __('Exit Popup Screen') }}</label><br>
                                <input type="radio" id="exit_popup_screen_active" name="exit_popup_screen" value="true"
                                    class="form-check-input" checked
                                    {{ isset($appsettings['exit_popup_screen']) && $appsettings['exit_popup_screen'] == 'true' ? 'checked' : '' }}>
                                <label for="exit_popup_screen_active" class="form-check-label">{{ __('Active') }}</label>
                                <input type="radio" id="exit_popup_screen_inactive" name="exit_popup_screen"
                                    value="false" class="form-check-input"
                                    {{ isset($appsettings['exit_popup_screen']) && $appsettings['exit_popup_screen'] == 'false' ? 'checked' : '' }}>
                                <label for="exit_popup_screen_inactive"
                                    class="form-check-label">{{ __('Inactive') }}</label>
                            </div>
                        </div>
                        {{-- <div class="col-md-3">
                            <div class="radio-group mandatory">
                                <label class="mandatory form-label">{{ __('App Drawer') }}</label><br>
                                <input type="radio" id="app_drawer_active" name="app_drawer" value="true"
                                    class="form-check-input" checked
                                    {{ isset($appsettings['app_drawer']) && $appsettings['app_drawer'] == 'true' ? 'checked' : '' }}>
                                <label for="app_drawer_active" class="form-check-label">{{ __('Active') }}</label>
                                <input type="radio" id="app_drawer_inactive" name="app_drawer" value="false"
                                    class="form-check-input"
                                    {{ isset($appsettings['app_drawer']) && $appsettings['app_drawer'] == 'false' ? 'checked' : '' }}>
                                <label for="app_drawer_inactive" class="form-check-label">{{ __('Inactive') }}</label>
                            </div>
                        </div> --}}
                        <div class="col-md-3">
                            <div class="radio-group mandatory">
                                <label class="mandatory form-label">{{ __('Show Bottom Navigation') }}</label><br>
                                <input type="radio" id="show_bottom_navigation_active" name="show_bottom_navigation"
                                    value="true" class="form-check-input"
                                    {{ isset($appsettings['show_bottom_navigation']) && $appsettings['show_bottom_navigation'] == 'true' ? 'checked' : '' }}>
                                <label for="show_bottom_navigation_active"
                                    class="form-check-label">{{ __('Active') }}</label>
                                <input type="radio" id="show_bottom_navigation_inactive" name="show_bottom_navigation"
                                    value="false" class="form-check-input"
                                    {{ isset($appsettings['show_bottom_navigation']) && $appsettings['show_bottom_navigation'] == 'false' ? 'checked' : '' }}>
                                <label for="show_bottom_navigation_inactive"
                                    class="form-check-label">{{ __('Inactive') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <p class="card-header-new-style"> {{ __('Notification Setting') }}</p>
                </div>
                <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-6 mt-2">
                            <label class="form-label col-md-12 col-sm-12">Project Id</label>
                            <input class="form-control" type="text" name="project_id"
                                value="{{ $appsettings['project_id'] ?? '' }}" placeholder="Enter Project Id" autofocus
                                required />
                        </div>
                        <div class="col-md-6 ">
                            <label class="d-flex col-sm-12 col-form-label align-items-center">
                                {{ __('Service File') }}
                                @if (isset($appsettings['service_file']) ? $appsettings['service_file'] : '')
                                    <p style="margin-left: 10px; margin-bottom: 0;"><label
                                            class="rounded-pill-success">File Exists</label></p>
                                @else
                                    <p style="margin-left: 10px; margin-bottom: 0;"><label class="rounded-pill-danger">Not
                                            Exists</label></p>
                                @endif
                            </label>
                            <div class="col-sm-12">
                                <input type="file" name="service_file" class="form-control mb-2" />
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-2 d-flex justify-content-end">
                        <button class="btn btn-primary me-1 mb-1" type="submit"
                            name="submit">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
@section('script')
    <script>
        document.getElementById('color').addEventListener('input', function() {
            document.getElementById('color_code').value = this.value;
        });
    </script>
@endsection
