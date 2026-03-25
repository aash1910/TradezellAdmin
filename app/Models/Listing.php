<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Listing extends Model
{
    use SoftDeletes;
    use CrudTrait;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'condition',
        'category',
        'price',
        'currency',
        'images',
        'status',
        'lat',
        'lng',
    ];

    protected $casts = [
        'images' => 'array',
        'price'  => 'decimal:2',
        'lat'    => 'decimal:7',
        'lng'    => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function swipes()
    {
        return $this->hasMany(ListingSwipe::class);
    }
}
