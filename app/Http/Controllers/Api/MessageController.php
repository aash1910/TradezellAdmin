<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function getMessages(Request $request, $userId)
    {
        $user = $request->user();
        
        $messages = Message::where(function($query) use ($user, $userId) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($user, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $user->id);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        return response()->json([
            'status' => 'success',
            'messages' => $messages
        ]);
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }
} 