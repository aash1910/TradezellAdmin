<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use App\User;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    public function uploadImage(Request $request)
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated. Please check your authentication token.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'type' => 'required|in:image,document'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $file = $request->file('image');
            
            if (!$file) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No file was uploaded or file field name is incorrect',
                ], 422);
            }

            $extension = $file->getClientOriginalExtension();
            
            // Process and save the image
            $image = Image::make($file);
            
            // If it's a profile image, resize it to maintain quality and reduce size
            if ($request->type === 'image') {
                $image->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Convert to base64
            $base64Image = 'data:image/' . $extension . ';base64,' . base64_encode($image->encode($extension, 90)->encoded);
            
            // Update user record using the mutator
            $user->update([
                $request->type => $base64Image
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => ucfirst($request->type) . ' uploaded successfully',
                'data' => [
                    $request->type => $user->{$request->type},
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload ' . $request->type,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 