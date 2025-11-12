<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
use Illuminate\Http\Request;
use App\Models\LandingPage;
use App\Models\Page;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\Admin\CacheMaintenanceController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/privacy-policy', [PrivacyController::class, 'privacyPolicy']);
Route::get('/terms-of-service', [PrivacyController::class, 'termsOfService']);
Route::get('/support', [PrivacyController::class, 'support']);

// Stripe Connect wallet routes
Route::get('/wallet/connect/refresh', [App\Http\Controllers\WalletConnectController::class, 'refresh']);
Route::get('/wallet/connect/return', [App\Http\Controllers\WalletConnectController::class, 'return']);
Route::get('/wallet/connect/delete-account', [App\Http\Controllers\WalletConnectController::class, 'deleteAccount'])
    ->name('wallet.connect.delete-account');

// Temporary maintenance route for clearing application cache. Remove after use.
Route::get('/admin/maintenance/clear-cache', [CacheMaintenanceController::class, 'clear'])
    ->name('admin.maintenance.clear-cache');