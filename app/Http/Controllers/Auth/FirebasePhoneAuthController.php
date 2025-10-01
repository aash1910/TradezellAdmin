<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Cache;

class FirebasePhoneAuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct()
    {
        try {
            // Initialize Firebase Auth
            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'));
            
            $this->firebaseAuth = $factory->createAuth();
            
            Log::info('Firebase Auth initialized successfully');
        } catch (\Exception $e) {
            Log::error('Firebase Auth initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send SMS OTP using Firebase Authentication
     */
    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
                'role' => 'required|string|in:sender,dropper'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }

            // Clean phone number (ensure it starts with +)
            $mobile = preg_replace('/\s+/', '', $request->phone);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+' . $mobile;
            }

            Log::info('Processing Firebase phone login:', [
                'original_phone' => $request->phone,
                'formatted_phone' => $mobile
            ]);

            // Find existing user
            $user = User::where('mobile', $mobile)->first();
            
            if (!$user) {
                return response()->json([
                    'error' => 'No account found with this phone number. Please register first.'
                ], 404);
            }

            // Check if user has the requested role
            if (!$user->hasRole($request->role)) {
                return response()->json([
                    'error' => 'You do not have permission to login as ' . $request->role,
                    'message' => 'You do not have permission to login as ' . $request->role
                ], 403);
            }

            try {
                // Get Firebase API key from config
                $apiKey = config('services.firebase.api_key');
                
                if (empty($apiKey)) {
                    Log::error('Firebase API key not configured');
                    return response()->json([
                        'error' => 'Firebase configuration error'
                    ], 500);
                }

                // Send verification code via Firebase Auth REST API
                $firebaseUrl = "https://identitytoolkit.googleapis.com/v1/accounts:sendVerificationCode?key={$apiKey}";
                
                $requestData = [
                    'phoneNumber' => $mobile,
                    'recaptchaToken' => '' // For testing, can be empty
                ];

                Log::info('Sending Firebase SMS verification:', [
                    'phone' => $mobile,
                    'url' => $firebaseUrl
                ]);

                // Make HTTP request to Firebase
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $firebaseUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($curlError) {
                    Log::error('Firebase cURL Error:', ['error' => $curlError]);
                    return response()->json([
                        'error' => 'Network error occurred'
                    ], 500);
                }

                $firebaseResponse = json_decode($response, true);
                
                Log::info('Firebase SMS Response:', [
                    'http_code' => $httpCode,
                    'response' => $firebaseResponse
                ]);

                if ($httpCode === 200 && isset($firebaseResponse['sessionInfo'])) {
                    // Store session info in cache for verification
                    $sessionKey = 'firebase_session_' . md5($mobile . time());
                    Cache::put($sessionKey, [
                        'phone' => $mobile,
                        'sessionInfo' => $firebaseResponse['sessionInfo'],
                        'created_at' => now()
                    ], now()->addMinutes(10));

                    return response()->json([
                        'message' => 'SMS sent successfully via Firebase',
                        'phone' => $mobile,
                        'session_key' => $sessionKey
                    ]);
                } else {
                    // Handle Firebase errors
                    $errorMessage = 'Failed to send SMS';
                    if (isset($firebaseResponse['error']['message'])) {
                        $errorMessage = $firebaseResponse['error']['message'];
                    }
                    
                    Log::error('Firebase SMS Error:', [
                        'error' => $errorMessage,
                        'full_response' => $firebaseResponse
                    ]);

                    return response()->json([
                        'error' => $errorMessage
                    ], 400);
                }

            } catch (\Exception $e) {
                Log::error('Firebase Service Error:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to send SMS. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Firebase Phone Login Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP using Firebase Authentication
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
                'otp' => 'required|string|size:6',
                'session_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid verification code',
                    'error' => $validator->errors()->first()
                ], 422);
            }

            // Clean phone number
            $mobile = preg_replace('/\s+/', '', $request->phone);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+' . $mobile;
            }

            Log::info('Firebase OTP verification:', [
                'phone' => $mobile,
                'session_key' => $request->session_key
            ]);

            $user = User::where('mobile', $mobile)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'User not found'
                ], 404);
            }

            // Retrieve session info from cache
            $sessionInfo = Cache::get($request->session_key);
            if (!$sessionInfo) {
                return response()->json([
                    'error' => 'Session expired. Please request a new OTP.'
                ], 422);
            }

            try {
                // Get Firebase API key
                $apiKey = config('services.firebase.api_key');
                
                // Verify OTP with Firebase Auth REST API
                $firebaseUrl = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber?key={$apiKey}";
                
                $requestData = [
                    'sessionInfo' => $sessionInfo['sessionInfo'],
                    'code' => $request->otp
                ];

                Log::info('Verifying Firebase OTP:', [
                    'phone' => $mobile,
                    'session_info' => $sessionInfo['sessionInfo']
                ]);

                // Make HTTP request to Firebase
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $firebaseUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($curlError) {
                    Log::error('Firebase Verification cURL Error:', ['error' => $curlError]);
                    return response()->json([
                        'error' => 'Network error occurred'
                    ], 500);
                }

                $firebaseResponse = json_decode($response, true);
                
                Log::info('Firebase Verification Response:', [
                    'http_code' => $httpCode,
                    'response' => $firebaseResponse
                ]);

                if ($httpCode === 200 && isset($firebaseResponse['idToken'])) {
                    // Verification successful
                    
                    // Clear the session from cache
                    Cache::forget($request->session_key);

                    // Update user verification status
                    $user->update([
                        'is_verified' => true
                    ]);

                    // Create token for the user
                    $token = $user->createToken('auth_token')->plainTextToken;

                    Log::info('Firebase OTP verification successful:', [
                        'phone' => $mobile,
                        'user_id' => $user->id
                    ]);

                    return response()->json([
                        'message' => 'Phone number verified successfully',
                        'access_token' => $token,
                        'user' => $user,
                        'firebase_token' => $firebaseResponse['idToken'] // Firebase ID token
                    ]);
                } else {
                    // Handle Firebase verification errors
                    $errorMessage = 'Invalid verification code';
                    if (isset($firebaseResponse['error']['message'])) {
                        $errorMessage = $firebaseResponse['error']['message'];
                    }
                    
                    Log::error('Firebase Verification Error:', [
                        'error' => $errorMessage,
                        'full_response' => $firebaseResponse
                    ]);

                    return response()->json([
                        'error' => $errorMessage
                    ], 422);
                }

            } catch (\Exception $e) {
                Log::error('Firebase OTP Verification Error:', [
                    'phone' => $mobile,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Invalid verification code'
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('Firebase OTP Verification Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Alternative method using Firebase Custom Claims for direct SMS
     */
    public function sendCustomOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
                'role' => 'required|string|in:sender,dropper'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }

            // Clean phone number
            $mobile = preg_replace('/\s+/', '', $request->phone);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+' . $mobile;
            }

            // Find existing user
            $user = User::where('mobile', $mobile)->first();
            
            if (!$user) {
                return response()->json([
                    'error' => 'No account found with this phone number. Please register first.'
                ], 404);
            }

            // Check role permission
            if (!$user->hasRole($request->role)) {
                return response()->json([
                    'error' => 'You do not have permission to login as ' . $request->role
                ], 403);
            }

            // Generate 6-digit OTP
            $otp = sprintf('%06d', mt_rand(0, 999999));
            
            // Store OTP in cache for 5 minutes
            $otpKey = 'firebase_otp_' . md5($mobile);
            Cache::put($otpKey, $otp, now()->addMinutes(5));

            // Use Firebase Cloud Messaging to send notification
            $messaging = (new Factory)
                ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
                ->createMessaging();

            // Note: This requires the user to have Firebase token stored
            // For SMS, you'd still need to integrate with SMS gateway
            // This is just for demonstration of Firebase integration

            Log::info('Custom Firebase OTP generated:', [
                'phone' => $mobile,
                'otp_key' => $otpKey
            ]);

            // Here you would integrate with SMS gateway of choice
            // For now, return OTP for testing (remove in production)
            return response()->json([
                'message' => 'OTP generated successfully',
                'phone' => $mobile,
                'otp' => $otp, // Remove this in production
                'otp_key' => $otpKey
            ]);

        } catch (\Exception $e) {
            Log::error('Firebase Custom OTP Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
