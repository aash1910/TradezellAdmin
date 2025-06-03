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

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->gender = $request->gender;
        $user->nationality = $request->nationality;
        $user->date_of_birth = $request->date_of_birth;
        $user->address = $request->address;
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;
        $user->mobile = $request->mobile;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.language' => 'nullable|string|in:en,es,hi,ar,pt,ru,ja,fr,sv,de,zh',
            'settings.place' => 'nullable|array',
            'settings.place.pickup' => 'nullable|array',
            'settings.place.dropoff' => 'nullable|array',
            'settings.place.pickup.address' => 'nullable|string',
            'settings.place.pickup.latitude' => 'nullable|numeric',
            'settings.place.pickup.longitude' => 'nullable|numeric',
            'settings.place.dropoff.address' => 'nullable|string',
            'settings.place.dropoff.latitude' => 'nullable|numeric',
            'settings.place.dropoff.longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->settings = json_encode($request->settings); 
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully',
            'settings' => $user->settings
        ]);
    }
} 