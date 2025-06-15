<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'accessToken' => 'required|string',
                'role' => 'required|string|in:dropper,sender',
                'remember' => 'boolean'
            ]);

            // Get user data from Facebook
            $facebookUser = Socialite::driver('facebook')->userFromToken($request->accessToken);

            // Check if user exists
            $user = User::where('email', $facebookUser->getEmail())->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'first_name' => $facebookUser->user['first_name'] ?? explode(' ', $facebookUser->getName())[0],
                    'last_name' => $facebookUser->user['last_name'] ?? explode(' ', $facebookUser->getName())[1] ?? '',
                    'email' => $facebookUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'role' => $request->role,
                    'is_verified' => 1, // Facebook verified emails are considered verified
                    'facebook_id' => $facebookUser->getId(),
                ]);
            }

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Facebook login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 