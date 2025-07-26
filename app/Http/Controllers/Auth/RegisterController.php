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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
            'nationality' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female,other,Male,Female,Other'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'mobile' => ['sometimes', 'string', 'regex:/^\+?[1-9]\d{1,14}$/', 'unique:users,mobile'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
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

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nationality' => $request->nationality,
            'gender' => ($request->gender == 'male' || $request->gender == 'Male') ? 'male' : (($request->gender == 'female' || $request->gender == 'Female') ? 'female' : 'other'),
            'mobile' => $mobile,
            'status' => 'active',
        ]);

        // Assign role to user (default to 'user' if not specified)
        $roleName = $request->role ?? 'user';
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->assignRole($role);
        }

        // Create token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Generate and send OTP
        $otp = rand(1000, 9999);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(1),
            'is_verified' => false,
        ]);

        $user->notify(new SendOtpNotification($otp));

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'role' => $roleName,
                'token' => $token,
                'requires_verification' => true,
                'message' => 'Please verify your account using the OTP sent to your email'
            ]
        ], 201);
    }
} 