<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\MomoPaymentController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\Auth\PhoneAuthController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Tradezell API Routes
|--------------------------------------------------------------------------
*/

Route::get('/fix-config', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    return 'Fixed';
});

// ── Public routes ──────────────────────────────────────────────────────────
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-otp', [OtpController::class, 'verify']);
Route::post('/resend-otp', [OtpController::class, 'resend']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/restore-account', [LoginController::class, 'restoreAccount']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.reset');
Route::get('/faqs', [FaqController::class, 'index']);

Route::get('/data-deletion', [PrivacyController::class, 'showDataDeletionInfo']);
Route::delete('/data-deletion', [PrivacyController::class, 'deleteUserData']);
Route::get('/data-deletion-status', [PrivacyController::class, 'checkDeletionStatus']);
Route::post('/process-pending-deletions', [PrivacyController::class, 'processPendingDeletions']);

// Phone Login (Twilio OTP)
Route::post('/phone-login', [PhoneAuthController::class, 'login']);
Route::post('/verify-phone-otp', [PhoneAuthController::class, 'verifyOtp']);
Route::post('/check-phone-exists', [PhoneAuthController::class, 'checkPhoneExists']);

// Social login
Route::post('/google-login', [LoginController::class, 'googleLogin']);
Route::post('/apple-login', [LoginController::class, 'appleLogin']);
Route::post('/apple-restore', [LoginController::class, 'restoreAppleAccount']);

Route::get('/home', function () {
    return ['data' => []];
});

// ── Protected routes ───────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'message' => 'User authenticated successfully',
            'user'    => $request->user(),
        ]);
    });

    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/update-settings', [UserProfileController::class, 'updateSettings']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/upload-image', [UserProfileController::class, 'uploadImage']);
    Route::delete('/account/delete', [PrivacyController::class, 'deleteUserData']);

    // ── Listings ────────────────────────────────────────────────────────────
    Route::get('/listings/feed',        [ListingController::class, 'feed']);
    Route::get('/listings/my',          [ListingController::class, 'myListings']);
    Route::get('/listings/{id}',        [ListingController::class, 'show']);
    Route::post('/listings',            [ListingController::class, 'store']);
    Route::put('/listings/{id}',        [ListingController::class, 'update']);
    Route::delete('/listings/{id}',     [ListingController::class, 'destroy']);
    Route::patch('/listings/{id}/status', [ListingController::class, 'updateStatus']);

    // Swipe on a listing
    Route::post('/listings/{id}/swipe', [ListingController::class, 'swipe']);

    // ── Matches ─────────────────────────────────────────────────────────────
    Route::get('/matches',              [MatchController::class, 'index']);
    Route::get('/matches/{id}',         [MatchController::class, 'show']);
    Route::delete('/matches/{id}',      [MatchController::class, 'unmatch']);

    // ── Reports ─────────────────────────────────────────────────────────────
    Route::post('/reports',                             [ReportController::class, 'store']);

    // ── Reviews ─────────────────────────────────────────────────────────────
    Route::post('/reviews',                             [ReviewController::class, 'store']);
    Route::get('/reviews/listing/{listingId}',          [ReviewController::class, 'getListingReview']);
    Route::get('/reviews/listing/{listingId}/received', [ReviewController::class, 'getReceivedReview']);

    // ── Messages ────────────────────────────────────────────────────────────
    Route::get('/conversations',                    [MessageController::class, 'getConversations']);
    Route::get('/messages/{userId}',                [MessageController::class, 'getMessages']);
    Route::post('/messages',                        [MessageController::class, 'sendMessage']);
    Route::post('/messages/{userId}/mark-read',     [MessageController::class, 'markMessagesAsRead']);

    // ── Notifications (API-polled, no push) ─────────────────────────────────
    Route::get('/notifications',                    [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read',         [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}',            [NotificationController::class, 'delete']);
    Route::post('/notifications/read-all',          [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count',       [NotificationController::class, 'getUnreadCount']);

    // ── Payments (Stripe) ────────────────────────────────────────────────────
    Route::post('/payments/create-intent',          [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/confirm',                [PaymentController::class, 'confirmPayment']);
    Route::post('/payments/create-listing',         [PaymentController::class, 'createListingAfterPayment']);
    Route::get('/payments/status/{paymentIntentId}',[PaymentController::class, 'getPaymentStatus']);
    Route::post('/payments/refund',                 [PaymentController::class, 'requestRefund']);
    Route::get('/payments/history',                 [PaymentController::class, 'getPaymentHistory']);
    Route::get('/payments/listing/{listingId}',     [PaymentController::class, 'getListingPayment']);
    Route::post('/payments/release/{listingId}',    [PaymentController::class, 'releasePaymentFromEscrow']);

    // ── MTN MoMo ────────────────────────────────────────────────────────────
    Route::post('/momo/request-to-pay',             [MomoPaymentController::class, 'requestToPay']);
    Route::get('/momo/status/{referenceId}',        [MomoPaymentController::class, 'getStatus']);
    Route::post('/momo/create-listing',             [MomoPaymentController::class, 'createListingAfterPayment']);
    Route::post('/momo/disburse',                   [MomoPaymentController::class, 'disburse']);
    Route::get('/momo/disbursement/status/{referenceId}', [MomoPaymentController::class, 'getDisbursementStatus']);

    // ── Wallet ───────────────────────────────────────────────────────────────
    Route::get('/wallet/balance',               [WalletController::class, 'getBalance']);
    Route::get('/wallet/transactions',          [WalletController::class, 'getTransactions']);
    Route::get('/wallet/pending-payments',      [WalletController::class, 'getPendingPayments']);
    Route::post('/wallet/withdraw',             [WalletController::class, 'requestWithdrawal']);
    Route::get('/wallet/withdrawals',           [WalletController::class, 'getWithdrawalHistory']);
    Route::get('/wallet/stripe-account',        [WalletController::class, 'getStripeAccountStatus']);
    Route::post('/wallet/setup-stripe-account', [WalletController::class, 'setupStripeAccount']);
    Route::get('/wallet/stripe-dashboard',      [WalletController::class, 'getStripeDashboardLink']);
    Route::get('/wallet/minimum-withdrawal',    [WalletController::class, 'getMinimumWithdrawalAmount']);
    Route::get('/wallet/main-account-balance',  [WalletController::class, 'getMainStripeAccountBalance']);
    Route::post('/wallet/release-payment/{matchId}', [WalletController::class, 'releasePayment']);
});

// ── Stripe webhook ──────────────────────────────────────────────────────────
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// ── MTN MoMo callbacks ──────────────────────────────────────────────────────
Route::post('/momo/callback',                [MomoPaymentController::class, 'callback']);
Route::post('/momo/callback/disbursement',   [MomoPaymentController::class, 'disbursementCallback']);
