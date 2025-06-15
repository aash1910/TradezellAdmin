<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
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
            
            // Get Facebook ID
            $facebookId = $facebookUser->getId();
            if (!$facebookId) {
                return response()->json([
                    'message' => 'Facebook login failed',
                    'error' => 'Could not get Facebook ID'
                ], 400);
            }

            // Check if user exists by Facebook ID first
            $user = User::where('facebook_id', $facebookId)->first();

            // If not found by Facebook ID, check by email if available
            if (!$user && $facebookUser->getEmail()) {
                $user = User::where('email', $facebookUser->getEmail())->first();
            }

            if (!$user) {
                // Create new user
                $userData = [
                    'first_name' => $facebookUser->user['first_name'] ?? explode(' ', $facebookUser->getName())[0],
                    'last_name' => $facebookUser->user['last_name'] ?? explode(' ', $facebookUser->getName())[1] ?? '',
                    'password' => Hash::make(Str::random(24)),
                    'is_verified' => 1,
                    'status' => 'active',
                    'facebook_id' => $facebookId,
                ];

                // Add email only if available
                if ($facebookUser->getEmail()) {
                    $userData['email'] = $facebookUser->getEmail();
                }

                $user = User::create($userData);

                // Assign role to user (default to 'user' if not specified)
                $roleName = $request->role ?? 'user';
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->assignRole($role);
                }

            } else {
                // Check role if provided
                if ($request->has('role') && !$user->roles->contains('name', $request->role)) {
                    return response()->json(['message' => 'User does not have the specified role'], 403);
                }
            }

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return user with role
            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Facebook login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 