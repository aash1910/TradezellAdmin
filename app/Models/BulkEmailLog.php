<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkEmailLog extends Model
{
    protected $table = 'bulk_email_logs';

    protected $fillable = ['campaign_id', 'user_id', 'email', 'status', 'error_message', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the campaign.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BulkEmailCampaign::class, 'campaign_id');
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
