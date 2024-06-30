<?php

use App\Http\Controllers\CustomizationController;
use App\Http\Controllers\WebHookController;
use App\Http\Controllers\PaymentCustomizationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupportController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// shopify webhook 
Route::group(['prefix' => 'webhooks','middleware' => ['verify.hmac']],function () { 
    Route::post('/customer_request', [WebHookController::class, 'customerRequest']);
    Route::post('/customer_deletion', [WebHookController::class, 'customerRedact']);
    Route::post('/deletion', [WebHookController::class, 'shopRedact']);
});

Route::middleware(['verify.shopify'])->group(function () {

    // customization
    Route::get('/getCustomizations', [CustomizationController::class, 'getCustomizations']);

    // payment-customization
    Route::post('/createPaymentCustomization', [PaymentCustomizationController::class, 'createPaymentCustomization']);
    Route::post('/getAllPaymentCustomizations', [PaymentCustomizationController::class, 'getAllPaymentCustomizations']);
    Route::get('/getPaymentCustomization/{id}', [PaymentCustomizationController::class, 'getPaymentCustomization']);
    Route::post('/editPaymentCustomization', [PaymentCustomizationController::class, 'editPaymentCustomization']);
    Route::post('/deletePaymentCustomization/{id}', [PaymentCustomizationController::class, 'deletePaymentCustomization']);
    Route::get('/payment-methods', [PaymentCustomizationController::class, 'getPaymentMethods']);
    Route::get('/countries', [PaymentCustomizationController::class, 'getCountryList']);
    Route::get('/languages', [PaymentCustomizationController::class, 'getLanguageList']);

    // settings
    Route::post('/saveSettings', [SettingsController::class, 'saveSettings']);
    Route::get('/getSettings', [SettingsController::class, 'getSettings']);

    // support
    Route::post('/saveSupport', [SupportController::class, 'saveSupport']);

});
