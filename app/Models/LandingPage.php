<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class LandingPage extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'landing_pages';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

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

    public function setTopbarLogoAttribute($value)
    {
        $this->setImageAttribute($value, 'topbar_logo');
    }

    public function setHomeTopHeroImageAttribute($value)
    {
        $this->setImageAttribute($value, 'home_top_hero_image');
    }

    public function setStatisticsPageHeaderImageAttribute($value)
    {
        $this->setImageAttribute($value, 'statistics_page_header_image');
    }

    public function setBirdPageHeaderImageAttribute($value)
    {
        $this->setImageAttribute($value, 'bird_page_header_image');
    }

    public function setFishPageHeaderImageAttribute($value)
    {
        $this->setImageAttribute($value, 'fish_page_header_image');
    }

    public function setTravelPageHeaderImageAttribute($value)
    {
        $this->setImageAttribute($value, 'travel_page_header_image');
    }

    public function setTravelPageHowToGoImageAttribute($value)
    {
        $this->setImageAttribute($value, 'travel_page_how_to_go_image');
    }

    public function setTravelPageWhereToStayImageAttribute($value)
    {
        $this->setImageAttribute($value, 'travel_page_where_to_stay_image');
    }

    public function setResortPageHeaderImageAttribute($value)
    {
        $this->setImageAttribute($value, 'resort_page_header_image');
    }

    public function setFooterLogoAttribute($value)
    {
        $this->setImageAttribute($value, 'footer_logo');
    }

    public function setMegaMenuItemsAttribute($value) // Repeatable field
    {
        $this->attributes['mega_menu_items'] = $this->setRepeatableFieldWithImage($value);   
    }

    public function setHomeExploringItemsAttribute($value) // Repeatable field
    {
        $this->attributes['home_exploring_items'] = $this->setRepeatableFieldWithImage($value);   
    }

    public function setHomeFeaturedHaorsItemsAttribute($value) // Repeatable field
    {
        $this->attributes['home_featured_haors_items'] = $this->setRepeatableFieldWithImage($value);   
    }

    public function setHomeConservationEffectsItemsAttribute($value) // Repeatable field
    {
        $this->attributes['home_conservation_effects_items'] = $this->setRepeatableFieldWithImage($value);   
    }

    public function setHomeSummaryReportItemsAttribute($value) // Repeatable field
    {
        $this->attributes['home_summary_report_items'] = $this->setRepeatableFieldWithImage($value);   
    }

    public function setHomeRecreationTourismItemsAttribute($value) // Repeatable field
    {
        $this->attributes['home_recreation_tourism_items'] = $this->setRepeatableFieldWithImage($value);   
    }

    public function setHomeGalleryItemsAttribute($value) // Repeatable field
    {
        $this->attributes['home_gallery_items'] = $this->setRepeatableFieldWithImage($value);   
    }

    public function setResortPageHotelListAttribute($value) // Repeatable field
    {
        $this->attributes['resort_page_hotel_list'] = $this->setRepeatableFieldWithImage($value);   
    }


    public function setImageAttribute($value, $attribute_name)
    {
        $disk = 'public';
        $destination_path = 'uploads/images';

        // if the image was erased
        if ($value==null) {
            \Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        if (Str::startsWith($value, 'data:image'))
        {
            $extension = explode('/', mime_content_type($value))[1];
            $image = \Image::make($value)->encode($extension, 90);
            $filename = md5($value.time()).'.'.$extension;

            \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

            // 3. Delete the previous image, if there was one.
            \Storage::disk($disk)->delete($this->{$attribute_name});

            $public_destination_path = Str::replaceFirst('public/', '', $destination_path);
            $this->attributes[$attribute_name] = $public_destination_path.'/'.$filename;
        }
    }

    public function setImageInRepeaterField($value) // Upload Image in Repeatable field 
    {
        //$attribute_name = "image";
        $disk = 'public';
        $destination_path = 'uploads/images'; 

        // if the image was erased
        // if ($value==null) {
        //     // delete the image from disk
        //     \Storage::disk($disk)->delete($this->{$attribute_name});

        //     // set null in the database column
        //     $this->attributes[$attribute_name] = null;
        // }

        // if a base64 was sent, store it in the db
        if (Str::startsWith($value, 'data:image'))
        {
            // EXT
            $extension = explode('/', mime_content_type($value))[1];

            // 0. Make the image
            $image = \Image::make($value)->encode($extension, 90);

            // 1. Generate a filename.
            $filename = md5($value.time()).'.'.$extension;

            // 2. Store the image on disk.
            \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

            // 3. Delete the previous image, if there was one.
        //    \Storage::disk($disk)->delete($this->{$attribute_name});

            // 4. Save the public path to the database
            // but first, remove "public/" from the path, since we're pointing to it 
            // from the root folder; that way, what gets saved in the db
            // is the public URL (everything that comes after the domain name)
            $public_destination_path = Str::replaceFirst('public/', '', $destination_path);
            return '/'.$public_destination_path.'/'.$filename;
        }
    }


    public function setRepeatableFieldWithImage($value) // Repeatable field With Image
    {
        $items = json_decode($value, true);

        //echo "<pre>";print_r($items);exit;

        if (count($items)) {
            foreach ($items as $key=>$item) {
                $image = $item['image'];
                if (Str::startsWith($image, 'data:image')) {
                    $newImage = $this->setImageInRepeaterField($image);
                    $items[$key]['image'] = $newImage;
                }

                if (!empty($item['image2']) && Str::startsWith($item['image2'], 'data:image')) {
                    $newImage = $this->setImageInRepeaterField($item['image2']);
                    $items[$key]['image2'] = $newImage;
                }
            }
            //echo "<pre>";print_r($items);exit;
            return json_encode($items);
        }
        
        return $value;
    }


}
