<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\User;

class ResetPasswordController extends Controller
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'string', PasswordRule::min(8)->mixedCase()->numbers(), 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if there's a valid reset token for this email
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token',
                'errors' => ['token' => ['No password reset request found for this email.']]
            ], 422);
        }

        // Verify the token matches
        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token',
                'errors' => ['token' => ['The password reset token is invalid.']]
            ], 422);
        }

        // Check if the token has expired (default 60 minutes)
        $tokenCreatedAt = strtotime($resetRecord->created_at);
        $tokenExpiresAt = $tokenCreatedAt + (config('auth.passwords.users.expire', 60) * 60);
        
        if (time() > $tokenExpiresAt) {
            // Delete expired token
            DB::table('password_resets')
                ->where('email', $request->email)
                ->delete();
                
            return response()->json([
                'status' => 'error',
                'message' => 'Expired token',
                'errors' => ['token' => ['The password reset token has expired.']]
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Delete the reset token after successful reset
                DB::table('password_resets')
                    ->where('email', $user->email)
                    ->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully']);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
} 