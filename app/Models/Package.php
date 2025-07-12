<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
class Package extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'packages';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    protected $fillable = [
        'sender_id',
        'pickup_name', 'pickup_mobile', 'pickup_address', 'pickup_details',
        'weight', 'price', 'pickup_date', 'pickup_time',
        'drop_name', 'drop_mobile', 'drop_address', 'drop_details',
        'pickup_lat', 'pickup_lng', 'drop_lat', 'drop_lng', 
        'status'
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

    public function sender()
    {
        return $this->belongsTo(\App\User::class, 'sender_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getPackageInfoAttribute()
    {
        return $this->pickup_name . ' (' . $this->pickup_address . ') - ' . $this->drop_name . ' (' . $this->drop_address . ') - ' . date('d M Y', strtotime($this->pickup_date)) . ' ' . date('H:i', strtotime($this->pickup_time));
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

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
