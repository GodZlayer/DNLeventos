<?php
use App\Http\Controllers\AppsettingController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DrawerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemUpdateController;
use App\Services\CachingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes();
Route::get('/', static function () {
    if (Auth::user()) {
        return redirect('/app_settings');
    }
    return view('auth.login');
});
Route::get('page/privacy-policy', static function () {
    $privacy_policy = CachingService::getSystemSettings('privacy_policy');
    echo htmlspecialchars_decode($privacy_policy);
})->name('public.privacy-policy');
Route::get('page/terms-conditions', static function () {
    $terms_conditions = CachingService::getSystemSettings('terms_and_condition');
    echo htmlspecialchars_decode($terms_conditions);
})->name('public.terms-conditions');
/* Non-Authenticated Common Functions */
Route::group(['prefix' => 'common'], static function () {
    Route::get('/js/lang.js', [Controller::class, 'readLanguageFile'])->name('common.language.read');
});
Route::group(['prefix' => 'install'], static function () {
    Route::get('purchase-code', [InstallerController::class, 'purchaseCodeIndex'])->name('install.purchase-code.index');
    Route::post('purchase-code', [InstallerController::class, 'checkPurchaseCode'])->name('install.purchase-code.post');
});
Route::group(['middleware' => ['auth', 'language']], static function () {
    /*** Authenticated Common Functions ***/
    Route::group(['prefix' => 'common'], static function () {
        Route::put('/change-row-order', [Controller::class, 'changeRowOrder'])->name('common.row-order.change');
        Route::put('/change-status', [Controller::class, 'changeStatus'])->name('common.status.change');
    });
    Route::get('/app_settings', [HomeController::class, 'index'])->name('app_settings');
    Route::get('change-password', [HomeController::class, 'changePasswordIndex'])->name('change-password.index');
    Route::post('change-password', [HomeController::class, 'changePasswordUpdate'])->name('change-password.update');
    Route::get('change-profile', [HomeController::class, 'changeProfileIndex'])->name('change-profile.index');
    Route::post('change-profile', [HomeController::class, 'changeProfileUpdate'])->name('change-profile.update');
    Route::group(['prefix' => 'system-update'], static function () {
        Route::get('/', [SystemUpdateController::class, 'index'])->name('system-update.index');
        Route::post('/', [SystemUpdateController::class, 'update'])->name('system-update.update');
    });
    Route::get('/onboardingstyle',[AppsettingController::class,'onboardingstyleindex'])->name('onboardingstyle.index');
    Route::get('/admob',[AppsettingController::class,'adsbindex'])->name('admob.index');
    Route::get('/splashscreen',[AppsettingController::class,'splashscreenindex'])->name('splashscreen.index');
    Route::resource('app_settings',AppsettingController::class);
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::resource('onboarding',OnboardingController::class);
    Route::resource('draweritem',DrawerController::class);
    Route::group(['prefix' => 'notification'], static function () {
        Route::delete('/batch-delete', [NotificationController::class, 'batchDelete'])->name('notification.batch.delete');
    });
    Route::resource('notification', NotificationController::class);

    Route::resource('setting',SettingController::class);

    Route::get('about-us', [SettingController::class, 'about_us'])->name('about-us');
    Route::get('contact-us', [SettingController::class, 'contact_us'])->name('contact-us');
    Route::get('terms-and-conditions', [SettingController::class, 'terms_and_conditions'])->name('terms-and-conditions');
    Route::get('privacy-policy', [SettingController::class, 'privacy_policy'])->name('privacy-policy');
    Route::get('app-settings', [SettingController::class, 'app_settings'])->name('app-settings');

    
    
    
    Route::group(['prefix' => 'system-update'], static function () {
        Route::get('/', [SystemUpdateController::class, 'index'])->name('system-update.index');
        Route::post('/', [SystemUpdateController::class, 'update'])->name('system-update.update');
    });

});
Route::get('/migrate', static function () {
    Artisan::call('migrate');
    echo "Done";
});
Route::get('/migrate-rollback', static function () {
    Artisan::call('migrate:rollback');
    echo "done";
});
Route::get('/seeder', static function () {
    Artisan::call('db:seed --class=InstallationSeeder');
   return redirect()->back();
});
Route::get('clear', static function () {
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');
    Artisan::call('debugbar:clear');
    return redirect()->back();
});
Route::get('storage-link', static function () {
    Artisan::call('storage:link');
});
