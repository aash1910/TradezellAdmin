<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataDeletionRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'confirmation_code',
        'user_id',
        'facebook_user_id',
        'status',
        'request_data',
        'deleted_at'
    ];

    protected $casts = [
        'request_data' => 'array',
        'deleted_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Scope for pending requests
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Scope for completed requests
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Relationship with User model if needed
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 