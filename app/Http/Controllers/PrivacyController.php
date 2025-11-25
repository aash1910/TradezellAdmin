<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\DataDeletionRequest;

class PrivacyController extends Controller
{
    /**
     * Display the privacy policy page
     */
    public function privacyPolicy()
    {
        return view('privacy-policy');
    }

    /**
     * Display the terms of service page
     */
    public function termsOfService()
    {
        return view('terms-of-service');
    }

    /**
     * Display the support page
     */
    public function support()
    {
        return view('support');
    }

    /**
     * Display data deletion information
     * This endpoint is used by Google Play Store to verify the data deletion URL
     * 
     * @author Ashraful Islam
     */
    public function showDataDeletionInfo()
    {
        return response()->json([
            'title' => 'Data Deletion Request',
            'description' => 'You can request deletion of your account and all associated data through the following methods:',
            'methods' => [
                [
                    'method' => 'API Request',
                    'description' => 'Send a DELETE request to this endpoint with your registered email address in the request body.',
                    'endpoint' => config('app.url') . '/api/data-deletion',
                    'example' => [
                        'method' => 'DELETE',
                        'url' => config('app.url') . '/api/data-deletion',
                        'body' => [
                            'email' => 'your-email@example.com'
                        ]
                    ]
                ],
                [
                    'method' => 'Email Request',
                    'description' => 'Send an email to our support team with your deletion request.',
                    'email' => 'support@piqdrop.com',
                    'subject' => 'Data Deletion Request'
                ],
                [
                    'method' => 'In-App',
                    'description' => 'Open the app, go to Account/Profile → Delete Account, and confirm the deletion request.'
                ]
            ],
            'timeline' => [
                'account_deactivation' => 'Immediate',
                'data_deletion' => 'Within 30 days',
                'confirmation' => 'Email sent when deletion is complete'
            ],
            'note' => 'Legal retention data may be retained only as required by law.',
            'contact' => [
                'email' => 'support@piqdrop.com',
                'privacy_policy' => config('app.url') . '/privacy-policy'
            ]
        ]);
    }

    /**
     * Handle data deletion requests (soft delete for payment data retention)
     */
    public function deleteUserData(Request $request)
    {
        try {
            // If authenticated, use the authenticated user
            if ($request->user()) {
                $user = $request->user();
            } else {
                // Otherwise, require email
                $request->validate([
                    'email' => 'required|email'
                ]);
                $user = User::where('email', $request->email)->first();
            }

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Soft delete the user (preserves payment data)
            $user->delete(); // This will set deleted_at timestamp

            // Delete user's tokens
            $user->tokens()->delete();

            // Note: We're NOT deleting images/documents to preserve payment records
            // These can be cleaned up later via a scheduled job if needed

            return response()->json([
                'message' => 'Account deleted successfully. Your data will be permanently removed within 30 days as per our privacy policy.'
            ]);

        } catch (\Exception $e) {
            Log::error('Data deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete account'], 500);
        }
    }

    public function handleFacebookDataDeletion(Request $request)
    {
        try {
            Log::info('Facebook data deletion request received', $request->all());

            // Verify the request is from Facebook
            if (!$request->has('signed_request')) {
                Log::error('Facebook data deletion request missing signed_request');
                return response()->json(['error' => 'Invalid request'], 400);
            }

            // Generate a unique confirmation code
            $confirmationCode = Str::random(32);

            // Store the deletion request
            $deletionRequest = DataDeletionRequest::create([
                'confirmation_code' => $confirmationCode,
                'facebook_user_id' => $request->input('user_id'),
                'status' => DataDeletionRequest::STATUS_PENDING,
                'request_data' => $request->all()
            ]);

            Log::info('Facebook data deletion request stored', [
                'confirmation_code' => $confirmationCode,
                'facebook_user_id' => $request->input('user_id'),
                'request_time' => now()
            ]);

            // Return the required response format
            return response()->json([
                'url' => config('app.url') . '/api/data-deletion-status',
                'confirmation_code' => $confirmationCode
            ]);
        } catch (\Exception $e) {
            Log::error('Facebook data deletion callback failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process data deletion request'], 500);
        }
    }

    public function checkDeletionStatus(Request $request)
    {
        try {
            // Check both query parameter and request input
            $confirmationCode = $request->query('confirmation_code') ?? $request->input('confirmation_code');
            
            if (!$confirmationCode) {
                return response()->json(['error' => 'Confirmation code is required'], 400);
            }

            // Find the deletion request, including soft-deleted records
            $deletionRequest = DataDeletionRequest::withTrashed()
                ->where('confirmation_code', $confirmationCode)
                ->first();

            if (!$deletionRequest) {
                return response()->json(['error' => 'Invalid confirmation code'], 404);
            }

            return response()->json([
                'status' => $deletionRequest->status,
                'confirmation_code' => $deletionRequest->confirmation_code,
                'deleted_at' => $deletionRequest->deleted_at ? $deletionRequest->deleted_at->toIso8601String() : null
            ]);
        } catch (\Exception $e) {
            Log::error('Deletion status check failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to check deletion status'], 500);
        }
    }

    // Optional: Add a method to process pending deletion requests
    public function processPendingDeletions()
    {
        try {
            $pendingRequests = DataDeletionRequest::pending()->get();
            Log::info('Found pending deletion requests', ['count' => $pendingRequests->count()]);

            foreach ($pendingRequests as $request) {
                Log::info('Processing deletion request', [
                    'confirmation_code' => $request->confirmation_code,
                    'facebook_user_id' => $request->facebook_user_id
                ]);

                // Update status to processing
                $request->update(['status' => DataDeletionRequest::STATUS_PROCESSING]);

                try {
                    // Find user by Facebook ID if available
                    $user = null;
                    if ($request->facebook_user_id) {
                        $user = User::where('facebook_id', $request->facebook_user_id)->first();
                        Log::info('Found user for deletion', [
                            'user_id' => $user ? $user->id : null,
                            'facebook_user_id' => $request->facebook_user_id
                        ]);
                    }

                    if ($user) {
                        // Delete user data
                        $user->delete();
                        Log::info('User deleted successfully', ['user_id' => $user->id]);
                    } else {
                        Log::info('No user found for deletion', ['facebook_user_id' => $request->facebook_user_id]);
                    }

                    // Mark request as completed
                    $request->update([
                        'status' => DataDeletionRequest::STATUS_COMPLETED,
                        'deleted_at' => now()
                    ]);
                    Log::info('Deletion request marked as completed', [
                        'confirmation_code' => $request->confirmation_code
                    ]);
                } catch (\Exception $e) {
                    // Mark request as failed
                    $request->update([
                        'status' => DataDeletionRequest::STATUS_FAILED
                    ]);
                    Log::error('Failed to process deletion request', [
                        'confirmation_code' => $request->confirmation_code,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'message' => 'Processed pending deletion requests',
                'processed_count' => $pendingRequests->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process pending deletions: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process pending deletions'], 500);
        }
    }
} 