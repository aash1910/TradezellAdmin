<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class FacebookController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Log the incoming request data for debugging
            \Log::info('Facebook login request data:', $request->all());

            $request->validate([
                'id' => 'required|string',
                'name' => 'required|string',
                'email' => 'required|email',
                'picture' => 'nullable|string|url',
                'role' => 'required|string|in:dropper,sender',
                'remember' => 'boolean'
            ]);

            // Get Facebook ID from request
            $facebookId = $request->id;
            $email = $request->email;
            $name = $request->name;
            $picture = $request->picture;

            \Log::info('Facebook login processing:', [
                'facebookId' => $facebookId,
                'email' => $email,
                'name' => $name,
                'picture' => $picture
            ]);

            // Check if user exists by Facebook ID first
            $user = User::where('facebook_id', $facebookId)->first();

            // If not found by Facebook ID, check by email
            if (!$user) {
                $user = User::where('email', $email)->first();
            }

            if (!$user) {
                // Create new user
                $nameParts = explode(' ', $name, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                $userData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                    'is_verified' => 1,
                    'status' => 'active',
                    'facebook_id' => $facebookId,
                ];

                // Add profile picture if available
                if ($picture) {
                    $userData['image'] = $this->downloadAndConvertImage($picture);
                }

                \Log::info('Creating new user with data:', $userData);

                $user = User::create($userData);

                // Assign role to user
                $roleName = $request->role;
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->assignRole($role);
                }

            } else {
                // Update Facebook ID if not set
                if (!$user->facebook_id) {
                    $user->update(['facebook_id' => $facebookId]);
                }

                // Update profile picture if not set and available
                if (!$user->image && $picture) {
                    $user->update(['image' => $this->downloadAndConvertImage($picture)]);
                }

                // Check role if provided
                if ($request->has('role') && !$user->roles->contains('name', $request->role)) {
                    return response()->json(['message' => 'User does not have the specified role'], 403);
                }
            }

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            \Log::info('Facebook login successful for user:', ['user_id' => $user->id, 'email' => $user->email]);

            // Return user with role
            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Facebook login validation error:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Facebook login failed',
                'error' => 'The given data was invalid.',
                'validation_errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Facebook login error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Facebook login failed',
                'error' => $e->getMessage()
            ], 500);
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
            
            \Log::warning('Failed to download Facebook profile picture', ['url' => $url]);
            return null;
            
        } catch (\Exception $e) {
            \Log::error('Error downloading Facebook profile picture', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
} 