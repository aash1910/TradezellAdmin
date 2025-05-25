<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    public function verify(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|string'
            ]);

            $user = User::where('email', $request->email)->first();

            if ($user->is_verified) {
                return response()->json(['message' => 'User already verified'], 200);
            }

            if ($user->otp !== $request->otp) {
                return response()->json(['error' => 'Invalid OTP'], 422);
            }

            if (now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['error' => 'OTP expired'], 422);
            }

            $user->update([
                'is_verified' => true,
                'otp' => null,
                'otp_expires_at' => null,
            ]);

            return response()->json(['message' => 'OTP verified successfully']);
        } catch (\Exception $e) {
            Log::error('OTP Verification Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to verify OTP. Please try again.'], 500);
        }
    }

    public function resend(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if ($user->is_verified) {
                return response()->json(['message' => 'User already verified'], 200);
            }

            if ($user->otp_expires_at && now()->lessThan($user->otp_expires_at)) {
                $remainingSeconds = now()->diffInSeconds($user->otp_expires_at);
                return response()->json([
                    'error' => "Please wait {$remainingSeconds} seconds before requesting a new OTP"
                ], 429);
            }

            $otp = rand(1000, 9999);

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(1),
            ]);

            //$user->notify(new SendOtpNotification($otp));

            return response()->json(['message' => 'OTP resent successfully']);
        } catch (\Exception $e) {
            Log::error('OTP Resend Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP. Please try again.'], 500);
        }
    }
}
