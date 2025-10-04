<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a notification sent to a user.
 *
 * @author Ashraful Islam
 */
class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'is_read',
        'type',
        'data'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 