<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User; 
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    public function __construct()
    {
        // Apply auth:sanctum middleware to logout method only
        $this->middleware('auth:sanctum')->only('logout');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        if (! $user->is_verified) {
            return response()->json(['message' => 'Please verify your OTP before logging in.'], 403);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Your account is not active. Please contact support.'], 403);
        }

        // Delete existing tokens for this user
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token');
        
        if (!$token || !$token->plainTextToken) {
            return response()->json(['message' => 'Failed to create access token'], 500);
        }

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            // This ensures the token belongs to the authenticated user
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to logout'], 500);
        }
    }
}
