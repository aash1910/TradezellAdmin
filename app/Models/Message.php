<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;
use Carbon\Carbon;

/**
 * Message Model
 * Handles messaging between users and support staff
 * 
 * @author Ashraful Islam
 */
class Message extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'messages';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Check if message is from support staff
     */
    public function isFromSupport()
    {
        return $this->sender_id == 1;
    }

    /**
     * Check if message is to support staff
     */
    public function isToSupport()
    {
        return $this->receiver_id == 1;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the sender of the message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to get messages between two users
     */
    public function scopeBetweenUsers($query, $user1Id, $user2Id)
    {
        return $query->where(function($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user1Id)
              ->where('receiver_id', $user2Id);
        })->orWhere(function($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user2Id)
              ->where('receiver_id', $user1Id);
        });
    }

    /**
     * Scope to get messages involving support staff (user_id = 1)
     */
    public function scopeInvolvingSupport($query)
    {
        return $query->where('sender_id', 1)
                     ->orWhere('receiver_id', 1);
    }

    /**
     * Scope to get unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get recent messages
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted time for display
     */
    public function getFormattedTimeAttribute()
    {
        $now = Carbon::now();
        $messageTime = Carbon::parse($this->created_at);
        
        if ($messageTime->isToday()) {
            return $messageTime->format('g:i A');
        } elseif ($messageTime->isYesterday()) {
            return 'Yesterday';
        } elseif ($messageTime->diffInDays($now) < 7) {
            return $messageTime->format('D');
        } else {
            return $messageTime->format('M j');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
} 