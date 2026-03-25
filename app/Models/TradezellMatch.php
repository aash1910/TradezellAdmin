<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class TradezellMatch extends Model
{
    use CrudTrait;
    protected $table = 'matches';

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'status',
        'unmatched_at',
        'conversation_id',
    ];

    protected $casts = [
        'unmatched_at' => 'datetime',
    ];

    public function userOne()
    {
        return $this->belongsTo(\App\User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(\App\User::class, 'user_two_id');
    }

    /**
     * Return the other participant in the match relative to the given user.
     */
    public function otherUser(int $userId): ?\App\User
    {
        if ($this->user_one_id === $userId) {
            return $this->userTwo;
        }
        return $this->userOne;
    }
}
