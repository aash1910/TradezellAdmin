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

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/privacy-policy', [PrivacyController::class, 'privacyPolicy']);
Route::get('/terms-of-service', [PrivacyController::class, 'termsOfService']);

// Stripe Connect wallet routes
Route::get('/wallet/connect/refresh', [App\Http\Controllers\WalletConnectController::class, 'refresh']);
Route::get('/wallet/connect/return', [App\Http\Controllers\WalletConnectController::class, 'return']);