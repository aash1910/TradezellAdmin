<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Payment;
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
        'pickup_name', 
        'pickup_mobile', 
        'pickup_address', 
        'pickup_address2', 
        'pickup_address3', 
        'pickup_details',
        'weight', 'price', 'pickup_date', 'pickup_time', 'pickup_image', 
        'drop_name', 'drop_mobile', 
        'drop_address', 
        'drop_address2', 
        'drop_address3', 
        'drop_details',
        'pickup_lat', 
        'pickup_lng',
        'pickup_lat2', 
        'pickup_lng2', 
        'pickup_lat3', 
        'pickup_lng3',  
        'drop_lat', 
        'drop_lng', 
        'drop_lat2', 
        'drop_lng2', 
        'drop_lat3', 
        'drop_lng3', 
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function setPickupImageAttribute($value)
    {
        $this->setSingleImageAttribute($value, 'pickup_image', 'uploads/packages');
    }

    /**
     * Helper to handle single image upload for pickup_image, similar to User model
     * @author Ashraful Islam
     */
    public function setSingleImageAttribute($value, $attribute_name, $destination_path = 'uploads/images')
    {
        $disk = 'public';

        // if the image was erased
        if ($value==null) {
            \Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        if (\Illuminate\Support\Str::startsWith($value, 'data:image'))
        {
            $extension = explode('/', mime_content_type($value))[1];
            $image = \Intervention\Image\Facades\Image::make($value)->encode($extension, 90);
            $filename = md5($value.time()).'.'.$extension;

            \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

            // Delete the previous image, if there was one.
            \Storage::disk($disk)->delete($this->{$attribute_name});

            $public_destination_path = \Illuminate\Support\Str::replaceFirst('public/', '', $destination_path);
            $this->attributes[$attribute_name] = $public_destination_path.'/'.$filename;
        }
    }

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

    public function escrowPayment()
    {
        return $this->hasOne(Payment::class)
            ->where('payment_type', 'escrow')
            ->where('status', 'succeeded');
    }

    public function refundPayment()
    {
        return $this->hasOne(Payment::class)
            ->where('payment_type', 'refund');
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
