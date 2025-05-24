<?php

use Illuminate\Http\Request;
use App\Models\LandingPage;
use App\Models\Page;
use App\Http\Controllers\Auth\OtpController;


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

    \Mail::raw($text, function ($message) use($data) {

        $message->to($data['to'])
            ->from($data['email'], $data['name'])
            ->subject('Get a free quote');
    });


    //echo "<pre>";print_r($data);exit;

    return ['response' => 'success'];
});

Route::get('/home', function () {
    $data = page_fields();
    return $data;
});

Route::post('/verify-otp', [OtpController::class, 'verify']);
Route::post('/resend-otp', [OtpController::class, 'resend']);