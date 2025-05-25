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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json([
                    'message' => 'No bearer token found',
                    'debug' => 'Token missing in request'
                ], 401);
            }

            [$id, $token] = explode('|', $token);
            
            $tokenModel = PersonalAccessToken::find($id);
            if (!$tokenModel) {
                return response()->json([
                    'message' => 'Invalid token',
                    'debug' => ['token_id' => $id, 'exists' => false]
                ], 401);
            }

            // Try to find the user directly from the token
            $user = $tokenModel->tokenable;
            if (!$user) {
                return response()->json([
                    'message' => 'Token exists but no associated user',
                    'debug' => [
                        'token_id' => $id,
                        'tokenable_type' => $tokenModel->tokenable_type,
                        'tokenable_id' => $tokenModel->tokenable_id
                    ]
                ], 401);
            }

            // Delete the token
            $tokenModel->delete();
            
            return response()->json([
                'message' => 'Logged out successfully',
                'debug' => [
                    'user_id' => $user->id,
                    'token_id' => $id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to logout',
                'error' => $e->getMessage(),
                'debug' => [
                    'token' => $request->bearerToken(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}
