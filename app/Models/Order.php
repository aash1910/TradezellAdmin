<?php

namespace App\Models;

use App\Models\Package;
use App\Models\Review;
use App\User;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 *
 * Represents a sender package assignment to a dropper along with delivery progress tracking.
 *
 * @author Ashraful Islam
 */
class Order extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'orders';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    protected $fillable = [
        'package_id',
        'dropper_id',
        'status',
        'delivery_status',
        'delivery_date',
        'pickup_status',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function dropper()
    {
        return $this->belongsTo(User::class, 'dropper_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    public function getOrderInfoAttribute()
    {
        return $this->package->package_info . ' - ' . $this->dropper->full_name . ' - ' . $this->status;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
