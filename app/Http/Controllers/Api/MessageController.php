<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\User;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function getConversations(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get unique conversation partners
            $conversationPartners = Message::selectRaw('
                CASE 
                    WHEN sender_id = ? THEN receiver_id 
                    ELSE sender_id 
                END as other_user_id
            ', [$user->id])
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->distinct()
            ->pluck('other_user_id');

            $conversationList = [];
            
            foreach ($conversationPartners as $otherUserId) {
                // Get the last message for this conversation
                $lastMessage = Message::where(function($query) use ($user, $otherUserId) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $otherUserId);
                })->orWhere(function($query) use ($user, $otherUserId) {
                    $query->where('sender_id', $otherUserId)
                          ->where('receiver_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->first();

                if ($lastMessage) {
                    // Get user details
                    $otherUser = User::find($otherUserId);
                    
                    if ($otherUser) {
                        $conversationList[] = [
                            'user_id' => $otherUser->id,
                            'name' => $otherUser->full_name,
                            'image' => $otherUser->image,
                            'mobile' => $otherUser->mobile,
                            'last_message' => $lastMessage->message,
                            'last_message_time' => $lastMessage->created_at,
                            'is_support' => $otherUser->id == 1
                        ];
                    }
                }
            }

            // Add support conversation if it doesn't exist
            $hasSupportConversation = collect($conversationList)->where('user_id', 1)->count() > 0;
            if (!$hasSupportConversation) {
                $conversationList[] = [
                    'user_id' => 1,
                    'name' => 'Support Service',
                    'image' => null,
                    'mobile' => null,
                    'last_message' => 'Welcome to PiqDrop Support! How can we help you today?',
                    'last_message_time' => now(),
                    'is_support' => true
                ];
            }

            // Sort by last message time (most recent first)
            usort($conversationList, function($a, $b) {
                return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
            });

            return response()->json([
                'status' => 'success',
                'conversations' => $conversationList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

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