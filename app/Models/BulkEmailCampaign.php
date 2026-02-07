<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkEmailCampaign extends Model
{
    protected $table = 'bulk_email_campaigns';

    protected $fillable = ['subject', 'body', 'recipient_count'];

    protected $casts = [
        'recipient_count' => 'integer',
    ];

    /**
     * Get the logs for this campaign.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(BulkEmailLog::class, 'campaign_id');
    }

    /**
     * Get the count of sent emails.
     */
    public function getSentCountAttribute(): int
    {
        return $this->logs()->where('status', 'sent')->count();
    }

    /**
     * Get the count of failed emails.
     */
    public function getFailedCountAttribute(): int
    {
        return $this->logs()->where('status', 'failed')->count();
    }
}
