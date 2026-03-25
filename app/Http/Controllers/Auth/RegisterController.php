<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use App\Notifications\SendOtpNotification;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'     => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
            'nationality'  => ['nullable', 'string', 'max:255'],
            'gender'       => ['nullable', 'string', 'in:male,female,other,Male,Female,Other'],
            'account_role' => ['nullable', 'string', 'in:trader,seller,buyer'],
            'mobile'       => ['sometimes', 'string', 'regex:/^\+?[1-9]\d{1,14}$/', 'unique:users,mobile'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Clean mobile number if provided
        $mobile = null;
        if ($request->has('mobile') && $request->mobile) {
            $mobile = preg_replace('/\s+/', '', $request->mobile);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+' . $mobile;
            }
        }

        $accountRole = $request->account_role ?? 'trader';

        $user = User::create([
            'first_name'  => $request->first_name,
            'last_name'   => $request->last_name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'nationality' => $request->nationality ?? null,
            'gender'      => $request->gender
                ? (strtolower($request->gender) === 'male' ? 'male' : (strtolower($request->gender) === 'female' ? 'female' : 'other'))
                : null,
            'mobile'  => $mobile,
            'status'  => 'active',
            'settings' => json_encode(['account_role' => $accountRole]),
        ]);

        // Assign default Spatie role for permission management
        $role = Role::where('name', 'user')->first();
        if ($role) {
            $user->assignRole($role);
        }

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Generate and send OTP
        $otp = rand(1000, 9999);
        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'is_verified'    => false,
        ]);

        $user->notify(new SendOtpNotification($otp));

        return response()->json([
            'status'  => 'success',
            'message' => 'User registered successfully',
            'data'    => [
                'user'                 => $user,
                'account_role'         => $accountRole,
                'token'                => $token,
                'requires_verification' => true,
                'message'              => 'Please verify your account using the OTP sent to your email',
            ]
        ], 201);
    }
}
