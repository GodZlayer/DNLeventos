<?php

namespace App\Providers;

use App\Services\CachingService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        /*** Header File ***/

        View::composer('layouts.sidebar', static function (\Illuminate\View\View $view) {
            $settings = CachingService::getSystemSettings('admin_logo');
            $view->with('admin_logo', $settings ?? '');
        });

        View::composer('layouts.main', static function (\Illuminate\View\View $view) {
            $settings = CachingService::getSystemSettings('favicon');
            $view->with('favicon', $settings ?? '');
            $view->with('lang', Session::get('language'));
        });

        View::composer('auth.login', static function (\Illuminate\View\View $view) {
            $favicon = CachingService::getSystemSettings('favicon');
            $admin_logo = CachingService::getSystemSettings('admin_logo');
            $login_image = CachingService::getSystemSettings('login_image');
            $view->with('admin_logo', $admin_logo ?? '');
            $view->with('favicon', $favicon ?? '');
            $view->with('login_bg_image', $login_image ?? '');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        //
    }
}
