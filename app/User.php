<?php

namespace App;

use Laravel\Sanctum\HasApiTokens;
use Prologue\Alerts\Facades\Alert;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasRoles;
    use CrudTrait;
    use Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'status', 'image', 'document', 'address', 'latitude', 'longitude', 'date_of_birth', 'gender', 'nationality', 'mobile', 'otp', 'is_verified', 'otp_expires_at', 'settings', 'role', 'facebook_id', 'stripe_account_id'
    ];

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setImageAttribute($value)
    {
        $this->setSingleImageAttribute($value, 'image');
    }

    public function setDocumentAttribute($value)
    {
        $this->setSingleImageAttribute($value, 'document', 'uploads/documents');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'otp', 'otp_expires_at', 'status', 'email_verified_at', 'created_at', 'updated_at'
    ];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function save(array $options = [])
    {
        if (app('env') == 'production' &&
            !app()->runningInConsole() &&
            !app()->runningUnitTests()) {
            Alert::warning('User editing is disabled in the demo.');

            return true;
        }

        return parent::save($options);
    }

    public function setSingleImageAttribute($value, $attribute_name, $destination_path = 'uploads/images')
    {
        $disk = 'public';

        // if the image was erased
        if ($value==null) {
            Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        if (Str::startsWith($value, 'data:image'))
        {
            $extension = explode('/', mime_content_type($value))[1];
            $image = Image::make($value)->encode($extension, 90);
            $filename = md5($value.time()).'.'.$extension;

            Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

            // 3. Delete the previous image, if there was one.
            Storage::disk($disk)->delete($this->{$attribute_name});

            $public_destination_path = Str::replaceFirst('public/', '', $destination_path);
            $this->attributes[$attribute_name] = $public_destination_path.'/'.$filename;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
