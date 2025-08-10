<?php

namespace App\Http\Controllers\Admin;

use App\Models\Message;
use App\User;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MessageCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MessageCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Message::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/message');
        CRUD::setEntityNameStrings('message', 'messages');
        
        // Only allow support staff (user_id = 1) to access this
        $this->middleware(function ($request, $next) {
            if (auth()->id() != 1) {
                abort(403, 'Access denied. Only support staff can access messaging.');
            }
            return $next($request);
        });
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // remove preview button 
        //$this->crud->denyAccess('show');
        
        CRUD::addColumn([
            'name' => 'conversation',
            'label' => 'Conversation',
            'type' => 'closure',
            'function' => function ($entry) {
                $sender = User::find($entry->sender_id);
                $receiver = User::find($entry->receiver_id);
                
                $senderName = $sender ? ($sender->id == 1 ? 'Support Service' : $sender->full_name) : 'Unknown';
                $receiverName = $receiver ? ($receiver->id == 1 ? 'Support Service' : $receiver->full_name) : 'Unknown';
                
                return "<strong>{$senderName}</strong> → <strong>{$receiverName}</strong>";
            }
        ]);

        CRUD::column('message')->label('Message')->limit(100);
        CRUD::column('created_at')->label('Sent At')->type('datetime');

        // Add sender-only filter
        CRUD::addFilter([
            'type' => 'select2',
            'name' => 'sender_only_filter',
            'label' => 'Filter by Sender'
        ], function () {
            return $this->getUsersByRole('sender');
        }, function ($value) {
            CRUD::addClause('where', 'sender_id', $value);
        });

        // Add dropper-only filter
        CRUD::addFilter([
            'type' => 'select2',
            'name' => 'dropper_only_filter',
            'label' => 'Filter by Dropper'
        ], function () {
            return $this->getUsersByRole('dropper');
        }, function ($value) {
            CRUD::addClause('where', 'sender_id', $value);
        });

        // Disable create, update, delete operations in list view
        CRUD::denyAccess(['create', 'update', 'delete']);
    }

    /**
     * Define what happens when the Show operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation()
    {
        CRUD::column('id')->label('ID');
        
        CRUD::addColumn([
            'name' => 'sender_id',
            'label' => 'Sender',
            'type' => 'closure',
            'function' => function ($entry) {
                $sender = User::find($entry->sender_id);
                return $sender ? ($sender->id == 1 ? 'Support Service' : $sender->full_name) : 'Unknown';
            }
        ]);

        CRUD::addColumn([
            'name' => 'sender_role',
            'label' => 'Sender Role',
            'type' => 'closure',
            'function' => function ($entry) {
                $sender = User::find($entry->sender_id);
                return $sender && $sender->roles->first() ? $sender->roles->first()->name : 'Unknown';
            }
        ]);

        CRUD::addColumn([
            'name' => 'receiver_id',
            'label' => 'Receiver',
            'type' => 'closure',
            'function' => function ($entry) {
                $receiver = User::find($entry->receiver_id);
                return $receiver ? ($receiver->id == 1 ? 'Support Service' : $receiver->full_name) : 'Unknown';
            }
        ]);

        CRUD::addColumn([
            'name' => 'receiver_role',
            'label' => 'Receiver Role',
            'type' => 'closure',
            'function' => function ($entry) {
                $receiver = User::find($entry->receiver_id);
                return $receiver && $receiver->roles->first() ? $receiver->roles->first()->name : 'Unknown';
            }
        ]);

        CRUD::column('message')->label('Message')->type('textarea');
        CRUD::column('is_read')->label('Is Read')->type('boolean');
        CRUD::column('created_at')->label('Sent At')->type('datetime');
        CRUD::column('updated_at')->label('Updated At')->type('datetime');
    }

    /**
     * Show conversations list for admin
     */
    public function conversations()
    {
        try {
            // Log the request for debugging
            \Log::info('Conversations method called', [
                'is_ajax' => request()->ajax(),
                'user_id' => auth()->id(),
                'url' => request()->url()
            ]);
            
            $conversations = $this->getConversations();
            
            // Log the result for debugging
            \Log::info('Conversations retrieved', [
                'count' => count($conversations),
                'conversations' => $conversations
            ]);
            
            // Check if this is an AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'conversations' => $conversations
                ]);
            }
            
            return view('vendor.backpack.crud.messages.conversations', [
                'conversations' => $conversations,
                'crud' => $this->crud
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in conversations method: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Check if this is an AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to load conversations. Please check the logs.',
                    'conversations' => []
                ], 500);
            }
            
            // Return a simple error view
            return view('vendor.backpack.crud.messages.conversations', [
                'conversations' => [],
                'crud' => $this->crud,
                'error' => 'Unable to load conversations. Please check the logs.'
            ]);
        }
    }

    /**
     * Show conversation with a specific user
     */
    public function showConversation($userId)
    {
        $user = User::findOrFail($userId);
        $messages = $this->getMessages($userId);
        
        return view('vendor.backpack.crud.messages.conversation', [
            'user' => $user,
            'messages' => $messages,
            'crud' => $this->crud
        ]);
    }

    /**
     * Send message from admin to user
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000'
        ]);

        $message = Message::create([
            'sender_id' => 1, // Support staff ID
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Get all conversations for admin
     */
    private function getConversations()
    {
        try {
            // Get conversation partners - users who have exchanged messages with admin (user_id = 1)
            $conversationPartners = Message::selectRaw('
                CASE 
                    WHEN sender_id = 1 THEN receiver_id 
                    ELSE sender_id 
                END as other_user_id
            ')
            ->where(function($query) {
                $query->where('sender_id', 1)
                      ->orWhere('receiver_id', 1);
            })
            ->whereRaw('(sender_id != receiver_id)') // Exclude self-messages
            ->distinct()
            ->pluck('other_user_id');

            $conversationList = [];
            
            foreach ($conversationPartners as $otherUserId) {
                if ($otherUserId == 1) continue; // Skip self
                
                try {
                    // Get the last message for this conversation
                    $lastMessage = Message::where(function($query) use ($otherUserId) {
                        $query->where('sender_id', 1)
                              ->where('receiver_id', $otherUserId);
                    })->orWhere(function($query) use ($otherUserId) {
                        $query->where('sender_id', $otherUserId)
                              ->where('receiver_id', 1);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                    if ($lastMessage) {
                        $otherUser = User::find($otherUserId);
                        
                        if ($otherUser) {
                            // Filter out external URLs and provide proper fallback
                            $userImage = $otherUser->image;
                            if ($userImage && !filter_var($userImage, FILTER_VALIDATE_URL)) {
                                // If it's a local path, make sure it's accessible
                                if (strpos($userImage, 'http') === 0) {
                                    $userImage = null; // External URL, use fallback
                                }
                            }
                            
                            $conversationList[] = [
                                'user_id' => $otherUser->id,
                                'name' => ($otherUser->id == 1 ? 'Support Service' : ($otherUser->full_name ?? 'Unknown User')),
                                'role' => $otherUser->roles->first() ? $otherUser->roles->first()->name : 'Unknown',
                                'image' => $userImage,
                                'mobile' => $otherUser->mobile ?? null,
                                'last_message' => $lastMessage->message ?? 'No message content',
                                'last_message_time' => $lastMessage->created_at ?? now(),
                                'unread_count' => $this->getUnreadCount($otherUserId)
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error processing conversation for user ' . $otherUserId . ': ' . $e->getMessage());
                    continue;
                }
            }

            // Sort by last message time (most recent first)
            usort($conversationList, function($a, $b) {
                try {
                    $timeA = is_string($a['last_message_time']) ? strtotime($a['last_message_time']) : $a['last_message_time']->getTimestamp();
                    $timeB = is_string($b['last_message_time']) ? strtotime($b['last_message_time']) : $b['last_message_time']->getTimestamp();
                    return $timeB - $timeA;
                } catch (\Exception $e) {
                    return 0; // If sorting fails, maintain original order
                }
            });

            return $conversationList;
        } catch (\Exception $e) {
            \Log::error('Error in getConversations: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get messages between admin and specific user
     */
    public function getMessages($userId)
    {
        return Message::where(function($query) use ($userId) {
            $query->where('sender_id', 1)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', 1);
        })
        ->orderBy('created_at', 'asc')
        ->get();
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount($userId)
    {
        try {
            return Message::where('sender_id', $userId)
                         ->where('receiver_id', 1)
                         ->where('is_read', false)
                         ->count();
        } catch (\Exception $e) {
            // If is_read column doesn't exist yet, return 0
            return 0;
        }
    }

    /**
     * Get users by role for filtering
     */
    public function getUsersByRole($role)
    {
        return User::where('id', '!=', 1)
                  ->whereHas('roles', function($query) use ($role) {
                      $query->where('name', $role);
                  })
                  ->select('id', 'first_name', 'last_name')
                  ->get()
                  ->mapWithKeys(function ($user) use ($role) {
                      return [$user->id => $user->first_name . ' ' . $user->last_name];
                  })
                  ->toArray();
    }

    /**
     * Get message statistics by role
     */
    public function getMessageStatsByRole()
    {
        try {
            $stats = [
                'sender' => [
                    'total_users' => User::whereHas('roles', function($query) {
                        $query->where('name', 'sender');
                    })->count(),
                    'total_messages' => Message::whereHas('sender.roles', function($query) {
                        $query->where('name', 'sender');
                    })->count()
                ],
                'dropper' => [
                    'total_users' => User::whereHas('roles', function($query) {
                        $query->where('name', 'dropper');
                    })->count(),
                    'total_messages' => Message::whereHas('sender.roles', function($query) {
                        $query->where('name', 'dropper');
                    })->count()
                ]
            ];

            return response()->json([
                'status' => 'success',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error getting statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test method for debugging
     */
    public function testDatabase()
    {
        try {
            $messageCount = Message::count();
            $userCount = User::count();
            
            return response()->json([
                'status' => 'success',
                'message_count' => $messageCount,
                'user_count' => $userCount,
                'auth_user' => auth()->id(),
                'database_connection' => 'OK'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'database_connection' => 'FAILED'
            ], 500);
        }
    }

    /**
     * Clean up external image URLs in user profiles
     */
    public function cleanupExternalImages()
    {
        try {
            $usersWithExternalImages = User::where('image', 'like', 'http%')->get();
            $cleanedCount = 0;
            
            foreach ($usersWithExternalImages as $user) {
                $user->update(['image' => null]);
                $cleanedCount++;
            }
            
            return response()->json([
                'status' => 'success',
                'message' => "Cleaned up {$cleanedCount} external image URLs",
                'cleaned_count' => $cleanedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error cleaning up external images: ' . $e->getMessage()
            ], 500);
        }
    }
} 