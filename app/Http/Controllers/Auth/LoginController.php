<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User; 
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

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
            'role' => 'sometimes|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        // Check role if provided
        if ($request->has('role') && !$user->roles->contains('name', $request->role)) {
            return response()->json(['message' => 'User does not have the specified role'], 403);
        }

        // if (! $user->is_verified) {
        //     return response()->json(['message' => 'Please verify your OTP before logging in.'], 403);
        // }

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

    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'role' => 'required|string|in:sender,dropper'
        ]);

        try {
            $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json(['message' => 'Invalid Google token'], 401);
            }

            $email = $payload['email'];
            $firstName = $payload['given_name'] ?? '';
            $lastName = $payload['family_name'] ?? '';
            $image = $payload['picture'] ?? '';

            // Find or create user
            $user = \App\User::where('email', $email)->first();
            if (!$user) {
                $userData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
                    'is_verified' => 1,
                    'status' => 'active',
                ];

                // Add profile picture if available
                if ($image) {
                    $userData['image'] = $this->downloadAndConvertImage($image);
                }

                $user = \App\User::create($userData);
                
                // Assign role
                $roleName = $request->role ?? 'user';
                $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                if ($role) {
                    $user->assignRole($role);
                }
            } else {
                // Update profile picture if not set and available
                if (!$user->image && $image) {
                    $user->update(['image' => $this->downloadAndConvertImage($image)]);
                }

                // Check role if provided
                if ($request->has('role') && !$user->roles->contains('name', $request->role)) {
                    return response()->json(['message' => 'User does not have the specified role'], 403);
                }
            }

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Google login failed',
                'error' => $e->getMessage()
            ], 500);
        }
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

    /**
     * Download image from URL and convert to base64 format
     * 
     * @param string $url
     * @return string|null
     */
    private function downloadAndConvertImage($url)
    {
        try {
            // Download the image
            $response = Http::timeout(10)->get($url);
            
            if ($response->successful()) {
                $imageData = $response->body();
                $mimeType = $response->header('Content-Type');
                
                // Validate that it's an image
                if (strpos($mimeType, 'image/') === 0) {
                    // Convert to base64
                    $base64 = base64_encode($imageData);
                    $extension = explode('/', $mimeType)[1];
                    
                    // Return in the format expected by the User model
                    return "data:{$mimeType};base64,{$base64}";
                }
            }
            
            \Log::warning('Failed to download Google profile picture', ['url' => $url]);
            return null;
            
        } catch (\Exception $e) {
            \Log::error('Error downloading Google profile picture', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
