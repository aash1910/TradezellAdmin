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
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

class LoginController extends Controller
{
    public function __construct()
    {
        // Apply auth:sanctum middleware to logout method only
        $this->middleware('auth:sanctum')->only('logout');
    }

    public function login(Request $request)
    {
        // Validate that either email or phone is provided
        $request->validate([
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string|regex:/^\+?[1-9]\d{1,14}$/',
            'password' => 'required|string',
            'role' => 'sometimes|string',
        ]);

        // Determine if we're using email or phone for login
        $isPhoneLogin = $request->has('phone');
        
        if ($isPhoneLogin) {
            // Clean phone number
            $mobile = preg_replace('/\s+/', '', $request->phone);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+' . $mobile;
            }
            
            // Check including soft-deleted users
            $user = User::withTrashed()->where('mobile', $mobile)->first();
        } else {
            // Check including soft-deleted users
            $user = User::withTrashed()->where('email', $request->email)->first();
        }

        // If user not found, return error
        if (! $user) {
            $errorMessage = $isPhoneLogin ? 'Invalid phone number or password' : 'Invalid email or password';
            return response()->json(['message' => $errorMessage], 401);
        }

        // Check if password is correct
        if (! Hash::check($request->password, $user->password)) {
            $errorMessage = $isPhoneLogin ? 'Invalid phone number or password' : 'Invalid email or password';
            return response()->json(['message' => $errorMessage], 401);
        }

        // Check if account is soft-deleted
        if ($user->trashed()) {
            return response()->json([
                'message' => 'Your account was previously deleted. Would you like to restore it?',
                'requires_restore_confirmation' => true,
                'deleted_at' => $user->deleted_at->toIso8601String(),
                'user_email' => $user->email,
            ], 200);
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

    public function appleLogin(Request $request)
    {
        $request->validate([
            'identity_token' => 'required|string',
            'role' => 'required|string|in:sender,dropper',
            'email' => 'nullable|email',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        try {
            $identityToken = $request->identity_token;
            
            // Step 1: Get Apple's public keys
            $applePublicKeys = $this->getApplePublicKeys();
            
            if (!$applePublicKeys) {
                Log::error('Failed to fetch Apple public keys');
                return response()->json(['message' => 'Failed to verify Apple token'], 500);
            }
            
            // Step 2: Decode token header to get key ID (kid)
            $tokenParts = explode('.', $identityToken);
            if (count($tokenParts) !== 3) {
                return response()->json(['message' => 'Invalid Apple token format'], 401);
            }
            
            $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0])), true);
            $kid = $header['kid'] ?? null;
            
            if (!$kid) {
                return response()->json(['message' => 'Invalid Apple token header'], 401);
            }
            
            // Step 3: Find the matching public key
            $publicKey = $this->getApplePublicKey($applePublicKeys, $kid);
            
            if (!$publicKey) {
                Log::error('Public key not found for kid: ' . $kid);
                return response()->json(['message' => 'Failed to verify Apple token'], 401);
            }
            
            // Step 4: Verify and decode the token
            // $publicKey is already a Key object from getApplePublicKey()
            try {
                $decoded = JWT::decode($identityToken, $publicKey);
            } catch (\Exception $e) {
                Log::error('JWT verification failed: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['message' => 'Invalid Apple token'], 401);
            }
            
            // Step 5: Verify token claims
            if ($decoded->iss !== 'https://appleid.apple.com') {
                return response()->json(['message' => 'Invalid token issuer'], 401);
            }
            
            // Step 6: Extract user information
            $appleUserId = $decoded->sub; // Unique Apple user ID
            $email = $decoded->email ?? $request->email;
            
            // Email might be null on subsequent logins if user chose "Hide My Email"
            if (!$email) {
                // Try to find existing user by Apple user ID (including soft-deleted)
                $user = User::withTrashed()->where('apple_id', $appleUserId)->first();
                if ($user) {
                    $email = $user->email;
                } else {
                    return response()->json([
                        'message' => 'Email is required for first-time sign in'
                    ], 400);
                }
            }
            
            $firstName = $request->first_name ?? $decoded->given_name ?? '';
            $lastName = $request->last_name ?? $decoded->family_name ?? '';
            
            // Step 7: Find or create user (check including soft-deleted users)
            $user = User::withTrashed()
                ->where(function($query) use ($email, $appleUserId) {
                    $query->where('email', $email)
                          ->orWhere('apple_id', $appleUserId);
                })
                ->first();
                
            if (!$user) {
                // Create new user
                try {
                    $userData = [
                        'first_name' => $firstName ?: 'Apple',
                        'last_name' => $lastName ?: 'User',
                        'email' => $email,
                        'password' => Hash::make(Str::random(24)),
                        'apple_id' => $appleUserId,
                        'is_verified' => 1,
                        'status' => 'active',
                    ];

                    $user = User::create($userData);
                    
                    // Assign role
                    $roleName = $request->role ?? 'user';
                    $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                    if ($role) {
                        $user->assignRole($role);
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle unique constraint violation (email already exists but soft-deleted)
                    if ($e->getCode() == 23000) {
                        // Try to find the soft-deleted user
                        $deletedUser = User::onlyTrashed()->where('email', $email)->first();
                        if ($deletedUser) {
                            // Return special response asking user to confirm account restoration
                            return response()->json([
                                'message' => 'Your account was previously deleted. Would you like to restore it?',
                                'requires_restore_confirmation' => true,
                                'deleted_at' => $deletedUser->deleted_at->toIso8601String(),
                                'user_email' => $deletedUser->email,
                            ], 200);
                        } else {
                            Log::error('Apple login: Email exists but cannot find soft-deleted user', [
                                'email' => $email,
                                'apple_id' => $appleUserId
                            ]);
                            return response()->json([
                                'message' => 'An account with this email already exists. Please contact support if you need to recover your account.'
                            ], 409);
                        }
                    } else {
                        throw $e;
                    }
                }
            } else {
                // User found (may be soft-deleted)
                if ($user->trashed()) {
                    // Return special response asking user to confirm account restoration
                    return response()->json([
                        'message' => 'Your account was previously deleted. Would you like to restore it?',
                        'requires_restore_confirmation' => true,
                        'deleted_at' => $user->deleted_at->toIso8601String(),
                        'user_email' => $user->email,
                    ], 200);
                }
                
                // Update existing user
                if (!$user->apple_id) {
                    $user->update(['apple_id' => $appleUserId]);
                }
                
                // Update name if not set
                if (!$user->first_name && $firstName) {
                    $user->update(['first_name' => $firstName]);
                }
                if (!$user->last_name && $lastName) {
                    $user->update(['last_name' => $lastName]);
                }

                // Check role if provided
                if ($request->has('role') && !$user->roles->contains('name', $request->role)) {
                    return response()->json(['message' => 'User does not have the specified role'], 403);
                }
            }

            // Step 8: Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (like unique constraint violations)
            if ($e->getCode() == 23000) {
                Log::error('Apple login: Database constraint violation', [
                    'error' => $e->getMessage(),
                    'email' => $request->email ?? 'not provided'
                ]);
                return response()->json([
                    'message' => 'An account with this email already exists. If you previously deleted your account, please contact support to restore it.'
                ], 409);
            }
            Log::error('Apple login database error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Unable to complete sign in. Please try again or contact support if the problem persists.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Apple login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'email' => $request->email ?? 'not provided'
            ]);
            return response()->json([
                'message' => 'Apple sign-in failed. Please try again or contact support if the problem persists.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted account for email/password login
     */
    public function restoreAccount(Request $request)
    {
        $request->validate([
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string|regex:/^\+?[1-9]\d{1,14}$/',
            'password' => 'required|string',
            'role' => 'sometimes|string',
        ]);

        try {
            $isPhoneLogin = $request->has('phone');
            
            if ($isPhoneLogin) {
                $mobile = preg_replace('/\s+/', '', $request->phone);
                if (!str_starts_with($mobile, '+')) {
                    $mobile = '+' . $mobile;
                }
                $user = User::onlyTrashed()->where('mobile', $mobile)->first();
            } else {
                $user = User::onlyTrashed()->where('email', $request->email)->first();
            }

            if (!$user) {
                return response()->json([
                    'message' => 'Deleted account not found. Please sign up again.'
                ], 404);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid password'
                ], 401);
            }

            // Restore the user
            $user->restore();
            $user->update([
                'status' => 'active',
                'deleted_at' => null,
            ]);

            // Check role if provided
            if ($request->has('role') && !$user->roles->contains('name', $request->role)) {
                return response()->json(['message' => 'User does not have the specified role'], 403);
            }

            // Delete existing tokens
            $user->tokens()->delete();

            // Generate token
            $token = $user->createToken('auth_token');

            return response()->json([
                'message' => 'Account restored successfully',
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            Log::error('Restore account error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to restore account. Please try again or contact support.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted account and complete Apple login
     */
    public function restoreAppleAccount(Request $request)
    {
        $request->validate([
            'identity_token' => 'required|string',
            'email' => 'nullable|email',
            'role' => 'required|string|in:sender,dropper',
        ]);

        try {
            $identityToken = $request->identity_token;
            $email = $request->email;
            
            // Verify the identity token (same as appleLogin)
            $applePublicKeys = $this->getApplePublicKeys();
            
            if (!$applePublicKeys) {
                return response()->json(['message' => 'Failed to verify Apple token'], 500);
            }
            
            $tokenParts = explode('.', $identityToken);
            if (count($tokenParts) !== 3) {
                return response()->json(['message' => 'Invalid Apple token format'], 401);
            }
            
            $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0])), true);
            $kid = $header['kid'] ?? null;
            
            if (!$kid) {
                return response()->json(['message' => 'Invalid Apple token header'], 401);
            }
            
            $publicKey = $this->getApplePublicKey($applePublicKeys, $kid);
            
            if (!$publicKey) {
                return response()->json(['message' => 'Failed to verify Apple token'], 401);
            }
            
            try {
                $decoded = JWT::decode($identityToken, $publicKey);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Invalid Apple token'], 401);
            }
            
            if ($decoded->iss !== 'https://appleid.apple.com') {
                return response()->json(['message' => 'Invalid token issuer'], 401);
            }
            
            $appleUserId = $decoded->sub;
            
            // Find the soft-deleted user by apple_id first (most reliable), then by email
            $user = User::onlyTrashed()
                ->where(function($query) use ($email, $appleUserId) {
                    $query->where('apple_id', $appleUserId);
                    if ($email) {
                        $query->orWhere('email', $email);
                    }
                })
                ->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Deleted account not found. Please sign up again.'
                ], 404);
            }
            
            // Restore the user
            $user->restore();
            $user->update([
                'status' => 'active',
                'deleted_at' => null,
                'apple_id' => $appleUserId,
            ]);
            
            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'message' => 'Account restored successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Restore Apple account error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to restore account. Please try again or contact support.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Fetch Apple's public keys
     */
    private function getApplePublicKeys()
    {
        try {
            $response = Http::timeout(10)->get('https://appleid.apple.com/auth/keys');
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Failed to fetch Apple public keys', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching Apple public keys: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get the public key for a specific key ID
     * Returns a Firebase\JWT\Key object that can be used directly with JWT::decode()
     */
    private function getApplePublicKey($publicKeys, $kid)
    {
        if (!isset($publicKeys['keys']) || !is_array($publicKeys['keys'])) {
            return null;
        }
        
        foreach ($publicKeys['keys'] as $key) {
            if (isset($key['kid']) && $key['kid'] === $kid) {
                // Convert JWK to Key object using Firebase JWT
                try {
                    $keys = ['keys' => [$key]];
                    $jwks = JWK::parseKeySet($keys);
                    $publicKey = reset($jwks);
                    
                    // Return the Key object directly - it can be used with JWT::decode()
                    return $publicKey;
                } catch (\Exception $e) {
                    Log::error('Failed to parse JWK: ' . $e->getMessage(), [
                        'kid' => $kid,
                        'error' => $e->getTraceAsString()
                    ]);
                }
            }
        }
        
        return null;
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
