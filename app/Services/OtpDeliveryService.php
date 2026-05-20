<?php

namespace App\Services;

use App\Notifications\SendOtpNotification;
use App\User;
use Illuminate\Support\Facades\Log;

class OtpDeliveryService
{
    /**
     * Send OTP email. Returns false if delivery fails (registration should still succeed).
     */
    public function send(User $user, int $otp): bool
    {
        try {
            $user->notify(new SendOtpNotification($otp));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Failed to send OTP email', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);

            if (app()->environment('local')) {
                Log::info('OTP (local — email not sent)', [
                    'email' => $user->email,
                    'otp'   => $otp,
                ]);
            }

            return false;
        }
    }
}
