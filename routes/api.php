<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\LandingPage;
use App\Models\Page;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\LoginController;


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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api'); 

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

Route::post('/verify-otp', [OtpController::class, 'verify']);
Route::post('/resend-otp', [OtpController::class, 'resend']);

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);

// Add this route before your logout route
Route::get('/check-auth', function (Request $request) {
    $token = $request->bearerToken();
    $tokenId = explode('|', $token)[0] ?? null;
    $tokenValue = explode('|', $token)[1] ?? null;
    
    $tokenExists = null;
    if ($tokenId && $tokenValue) {
        $tokenExists = PersonalAccessToken::where('id', $tokenId)->exists();
    }
    
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->user(),
        'token_present' => !empty($token),
        'token' => $token,
        'token_id' => $tokenId,
        'token_exists' => $tokenExists ? 'yes' : 'no'
    ]);
})->middleware('auth:sanctum');

// Add this route to test authentication
Route::get('/test-auth', function (Request $request) {
    return response()->json([
        'message' => 'Authenticated successfully',
        'user' => $request->user()
    ]);
})->middleware('auth:sanctum');