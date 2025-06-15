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
     * Handle data deletion requests
     */
    public function deleteUserData(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Delete user's profile image if exists
            if ($user->image) {
                Storage::delete('public/' . $user->image);
            }

            // Delete user's document if exists
            if ($user->document) {
                Storage::delete('public/' . $user->document);
            }

            // Delete user's tokens
            $user->tokens()->delete();

            // Delete the user
            $user->delete();

            return response()->json([
                'message' => 'User data deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Data deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete user data'], 500);
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