<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SendOtpNotification;
use Twilio\Rest\Client;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\RestException;

class PhoneAuthController extends Controller
{
    protected $twilioClient;

    public function __construct()
    {
        try {
            // Log Twilio configuration
            Log::info('Twilio Configuration:', [
                'sid' => config('services.twilio.sid'),
                'auth_token_exists' => !empty(config('services.twilio.auth_token')),
                'verify_service_sid' => config('services.twilio.verify_service_sid')
            ]);

            // Initialize Twilio client
            $this->twilioClient = new Client(
                config('services.twilio.sid'),
                config('services.twilio.auth_token')
            );
        } catch (ConfigurationException $e) {
            Log::error('Twilio Configuration Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function login(Request $request)
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

            // Clean phone number (remove spaces and ensure it starts with +)
            $mobile = preg_replace('/\s+/', '', $request->phone);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+' . $mobile;
            }

            Log::info('Processing phone login:', [
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

            // Send verification code via Twilio Verify
            try {
                $verifyServiceSid = config('services.twilio.verify_service_sid');
                
                Log::info('Attempting to send verification code via Twilio Verify:', [
                    'to' => $mobile,
                    'service_sid' => $verifyServiceSid
                ]);

                if (empty($verifyServiceSid)) {
                    throw new \Exception('Twilio Verify Service SID is not configured');
                }

                // Use the exact endpoint format you provided
                $verification = $this->twilioClient->verify->v2->services($verifyServiceSid)
                    ->verifications
                    ->create($mobile, "sms");

                Log::info('Verification code sent successfully:', [
                    'status' => $verification->status,
                    'service_sid' => $verifyServiceSid
                ]);

                return response()->json([
                    'message' => 'Verification code sent successfully',
                    'phone' => $mobile
                ]);

            } catch (RestException $e) {
                Log::error('Twilio REST Error:', [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'more_info' => $e->getMoreInfo()
                ]);
                return response()->json([
                    'error' => 'Failed to send verification code: ' . $e->getMessage(),
                    'message' => 'Failed to send verification code: ' . $e->getMessage()
                ], 500);
            } catch (\Exception $e) {
                Log::error('Twilio General Error:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to send verification code. Please try again.',
                    'message' => 'Failed to send verification code. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Phone Login Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred. Please try again.',
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            // Log the incoming request
            Log::info('Verify OTP Request:', [
                'phone' => $request->phone,
                'otp' => $request->otp
            ]);

            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
                'otp' => 'required|string|size:6'
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

            // Log the cleaned phone number
            Log::info('Cleaned phone number:', ['mobile' => $mobile]);

            $user = User::where('mobile', $mobile)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'User not found'
                ], 404);
            }

            // Verify the code with Twilio
            try {
                $verifyServiceSid = config('services.twilio.verify_service_sid');
                
                // Log the Twilio configuration
                Log::info('Twilio Configuration for Verification:', [
                    'verify_service_sid' => $verifyServiceSid,
                    'sid' => config('services.twilio.sid'),
                    'auth_token_exists' => !empty(config('services.twilio.auth_token'))
                ]);

                if (empty($verifyServiceSid)) {
                    throw new \Exception('Twilio Verify Service SID is not configured');
                }

                Log::info('Attempting to verify code with Twilio:', [
                    'to' => $mobile,
                    'code' => $request->otp,
                    'service_sid' => $verifyServiceSid
                ]);

                // Use the correct endpoint for verification check
                $verificationCheck = $this->twilioClient->verify->v2->services($verifyServiceSid)
                    ->verificationChecks
                    ->create([
                        'to' => $mobile,
                        'code' => $request->otp
                    ]);

                Log::info('Verification check response:', [
                    'status' => $verificationCheck->status,
                    'valid' => $verificationCheck->valid
                ]);

                if ($verificationCheck->status !== 'approved') {
                    return response()->json([
                        'error' => 'Invalid verification code'
                    ], 422);
                }

            } catch (RestException $e) {
                Log::error('Twilio Verification Check Error:', [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'more_info' => $e->getMoreInfo(),
                    'status' => $e->getStatusCode()
                ]);
                return response()->json([
                    'error' => 'Failed to verify code: ' . $e->getMessage()
                ], 500);
            }

            // Update user verification status
            $user->update([
                'is_verified' => true
            ]);

            // Create token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Phone number verified successfully',
                'access_token' => $token,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Phone OTP Verification Error: ' . $e->getMessage());
            Log::error('Error Details: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    public function checkPhoneExists(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }

            // Clean phone number (remove spaces and ensure it starts with +)
            $mobile = preg_replace('/\s+/', '', $request->phone);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+' . $mobile;
            }

            Log::info('Checking phone existence:', [
                'original_phone' => $request->phone,
                'formatted_phone' => $mobile
            ]);

            // Check if user exists with this phone number
            $user = User::where('mobile', $mobile)->first();
            
            return response()->json([
                'exists' => $user ? true : false,
                'phone' => $mobile
            ]);

        } catch (\Exception $e) {
            Log::error('Check Phone Exists Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred while checking phone number.',
                'exists' => false
            ], 500);
        }
    }
} 