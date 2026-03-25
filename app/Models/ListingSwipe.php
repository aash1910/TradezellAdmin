<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingSwipe extends Model
{
    protected $table = 'listing_swipes';

    protected $fillable = [
        'user_id',
        'listing_id',
        'owner_id',
        'direction',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function owner()
    {
        return $this->belongsTo(\App\User::class, 'owner_id');
    }
}
