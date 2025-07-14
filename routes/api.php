<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\Auth\PhoneAuthController;
use Illuminate\Support\Facades\Artisan;

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

Route::get('/fix-config', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    return 'Fixed';
});

// Public routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-otp', [OtpController::class, 'verify']);
Route::post('/resend-otp', [OtpController::class, 'resend']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/facebook-login', [FacebookController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.reset');
Route::get('/faqs', [FaqController::class, 'index']);
Route::delete('/data-deletion', [PrivacyController::class, 'deleteUserData']);

// Facebook Data Deletion Callback
Route::post('/facebook-data-deletion', [PrivacyController::class, 'handleFacebookDataDeletion']);
Route::get('/data-deletion-status', [PrivacyController::class, 'checkDeletionStatus']);
Route::post('/process-pending-deletions', [PrivacyController::class, 'processPendingDeletions']);

// Phone Login
Route::post('/phone-login', [PhoneAuthController::class, 'login']);
Route::post('/verify-phone-otp', [PhoneAuthController::class, 'verifyOtp']);



Route::get('/send_email_quote', function (Request $request) {
    try {
        $data = $request->validate([
            'to' => 'required|email',
            'email' => 'required|email',
            'name' => 'required|string',
        ]);

        $text = "";
        foreach ($request->except(['to']) as $key => $content) {
            $text .= ucfirst($key) . ": {$content}\n";
        }

        // Set email headers
        $headers = [
            'From' => $data['email'],
            'Reply-To' => $data['email'],
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/plain; charset=UTF-8'
        ];

        // Convert headers array to string
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "$key: $value\r\n";
        }

        // Send email using PHP's mail function
        $subject = 'Get a free quote';
        if (mail($data['to'], $subject, $text, $headerString)) {
            return response()->json(['status' => 'success', 'message' => 'Email sent successfully']);
        } else {
            throw new \Exception('Failed to send email');
        }
    } catch (\Exception $e) {
        Log::error('Email sending failed: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send email. Please try again later.',
            'debug' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
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
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/update-settings', [UserProfileController::class, 'updateSettings']);
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

    // Review routes
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Message routes
    Route::get('/messages/{userId}', [MessageController::class, 'getMessages']);
    Route::post('/messages', [MessageController::class, 'sendMessage']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);

    // Payment routes
    Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);
    Route::post('/payments/create-package', [PaymentController::class, 'createPackageAfterPayment']);
    Route::get('/payments/status/{paymentIntentId}', [PaymentController::class, 'getPaymentStatus']);
    Route::post('/payments/refund', [PaymentController::class, 'requestRefund']);
    Route::get('/payments/history', [PaymentController::class, 'getPaymentHistory']);
    Route::get('/payments/package/{packageId}', [PaymentController::class, 'getPackagePayment']);
    Route::post('/payments/release/{packageId}', [PaymentController::class, 'releasePaymentFromEscrow']);

    // Wallet routes (for droppers)
    Route::get('/wallet/balance', [WalletController::class, 'getBalance']);
    Route::get('/wallet/transactions', [WalletController::class, 'getTransactions']);
    Route::get('/wallet/pending-payments', [WalletController::class, 'getPendingPayments']);
    Route::post('/wallet/withdraw', [WalletController::class, 'requestWithdrawal']);
    Route::get('/wallet/withdrawals', [WalletController::class, 'getWithdrawalHistory']);
    Route::get('/wallet/stripe-account', [WalletController::class, 'getStripeAccountStatus']);
    Route::post('/wallet/setup-stripe-account', [WalletController::class, 'setupStripeAccount']);
    Route::get('/wallet/stripe-dashboard', [WalletController::class, 'getStripeDashboardLink']);
    Route::get('/wallet/minimum-withdrawal', [WalletController::class, 'getMinimumWithdrawalAmount']);
    Route::get('/wallet/main-account-balance', [WalletController::class, 'getMainStripeAccountBalance']);
    Route::post('/wallet/release-payment/{orderId}', [WalletController::class, 'releasePayment']);
});

// Stripe webhook (no authentication required)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// Escrow refund processing endpoint
Route::get('/escrow/process-refunds', function () {
    try {
        $output = Artisan::call('escrow:process-refunds');
        $result = Artisan::output();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Escrow refund processing completed',
            'output' => $result,
            'exit_code' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to process escrow refunds',
            'error' => $e->getMessage()
        ], 500);
    }
});
