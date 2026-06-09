<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PhoneLookupController extends Controller
{
    public function checkPhoneExists(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first(),
                ], 422);
            }

            $mobile = preg_replace('/\s+/', '', $request->phone);
            if (!str_starts_with($mobile, '+')) {
                $mobile = '+'.$mobile;
            }

            Log::info('Checking phone existence:', [
                'original_phone' => $request->phone,
                'formatted_phone' => $mobile,
            ]);

            $user = User::where('mobile', $mobile)->first();

            return response()->json([
                'exists' => (bool) $user,
                'phone' => $mobile,
            ]);
        } catch (\Exception $e) {
            Log::error('Check Phone Exists Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An error occurred while checking phone number.',
                'exists' => false,
            ], 500);
        }
    }
}
