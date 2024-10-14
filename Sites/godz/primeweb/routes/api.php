<?php
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('get-system-settings', [ApiController::class, 'getSystemSettings']);
Route::get('get-drawer-items', [ApiController::class, 'getDrawerItems']);
Route::get('get-onboarding-list', [ApiController::class, 'getOnboardingList']);
Route::post('add-fcm', [ApiController::class, 'addFcm']);


