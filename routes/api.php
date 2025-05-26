<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\LandingPage;
use App\Models\Page;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\OrderController;


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

// Public routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-otp', [OtpController::class, 'verify']);
Route::post('/resend-otp', [OtpController::class, 'resend']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.reset');

Route::get('/send_email_quote', function (Request $request) {
    $data = $request->toArray();
    $text = "";

    foreach ($data as $key => $content) {
        if($key == 'to') continue;
        $text.= ucfirst($key) . " : {$content} \n"; 
    }

    Mail::raw($text, function ($message) use($data) {
        $message->to($data['to'])
            ->from($data['email'], $data['name'])
            ->subject('Get a free quote');
    });

    return ['response' => 'success'];
});

Route::get('/home', function () {
    return ['data' => []]; // Replace with your actual data source
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'message' => 'User authenticated successfully',
            'user' => $request->user()
        ]);
    });
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/upload-image', [UserProfileController::class, 'uploadImage']);
    
    // Package routes
    Route::post('/packages', [PackageController::class, 'store']);
    Route::put('/packages/{id}', [PackageController::class, 'update']);
    Route::get('/packages/my-packages', [PackageController::class, 'myPackages']);
    Route::patch('/packages/{id}/cancel', [PackageController::class, 'cancel']);
    Route::get('/packages/search', [PackageController::class, 'searchPackages']);

    // Order routes
    Route::post('/orders', [OrderController::class, 'store']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('/orders/my-orders', [OrderController::class, 'myOrders']);
});


