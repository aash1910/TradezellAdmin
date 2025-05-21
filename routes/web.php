<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
use Illuminate\Http\Request;
use App\Models\LandingPage;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Haor;
use App\Models\Page;
use App\Models\River;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\Video;
use App\Models\Wetland;

function page_fields(){

    $landingPage = LandingPage::find(1);
    $data = $landingPage ? $landingPage->toArray() : array();

    $json_fields = array(
        'topbar_menu_items', 
        'social_media_menu_items',
        'mega_menu_items',
        'home_exploring_items',
        'home_featured_haors_items',
        'home_haor_map_items',
        'home_conservation_effects_items',
        'home_summary_report_items',
        'home_recreation_tourism_items',
        'home_gallery_items',
        'resort_page_hotel_list',
        'footer_link_items',
        'footer_link_items_section2'
    );
    foreach ($data as $field => $content) {

        if(in_array($field, $json_fields) && !empty($content)){
            $data[$field] = json_decode($content, true);
        }

    }
    //echo "<pre>";print_r($data);exit;

    return $data;
}

function district_list(){
    $district_list = District::pluck('name', 'id');
    $data = $district_list ? $district_list->toArray() : array();
    return $data;
}

function upazila_list(){
    $upazila_list = Upazila::all('name', 'id', 'district_id');
    $data = $upazila_list ? $upazila_list->toArray() : array();
    return $data;
}

function haor_list(){
    $haor_list = Haor::all('name', 'id', 'district_id', 'upazila_id');
    $data = $haor_list ? $haor_list->toArray() : array();
    return $data;
}

function haor_detail($id){

    $data = page_fields();

    $row = Haor::findOrFail($id);
    $row = $row ? $row->toArray() : array();

    $json_fields = array(
        'gallery_items'
    );
    foreach ($row as $field => $content) {
        if(in_array($field, $json_fields) && !empty($content)){
            $row[$field] = json_decode($content, true);
        }
    }

    // get previous haor
    $prev = Haor::where('id', '<', $id)->orderBy('id','desc')->first();
    if(empty($prev)){
        $prev = Haor::orderBy('id','desc')->first();
    }
    // get next haor
    $next = Haor::where('id', '>', $id)->orderBy('id','asc')->first();
    if(empty($next)){
        $next = Haor::orderBy('id','asc')->first();
    }

    //echo "<pre>";print_r($prev);exit;

    $row['prev_haor_id'] = $prev ? $prev->id : '';
    $row['prev_haor_name'] = $prev ? $prev->name : '';
    $row['next_haor_id'] = $next ? $next->id : '';
    $row['next_haor_name'] = $next ? $next->name : '';

    return array_merge($data, $row);
}

function haors(){
    $data = page_fields();

    $districts = District::orderBy('id','asc')->select('name', 'id')->get();
    $data['district_items'] = $districts ? $districts->toArray() : array();

    foreach ($data['district_items'] as $key=>$item) {

        $upazilas = Upazila::where('district_id', $item['id'])->orderBy('name','asc')->select('name', 'id')->get();
        $data['district_items'][$key]['upazilas'] = $upazilas ? $upazilas->toArray() : array();

        foreach ($data['district_items'][$key]['upazilas'] as $key2=>$item2) {

            $haors = Haor::where('upazila_id', $item2['id'])->orderBy('name','asc')->select('thumb_img','area','name', 'id')->get();
            $data['district_items'][$key]['upazilas'][$key2]['haors'] = $haors ? $haors->toArray() : array();
        }
    }

    return $data;
}

function district($id){

    $data = page_fields();

    $row = District::findOrFail($id);
    $row = $row ? $row->toArray() : array();

    //

    $upazilas = Upazila::where('district_id', $id)->orderBy('name','asc')->select('name', 'id')->get();
    $row['upazila_items'] = $upazilas ? $upazilas->toArray() : array();

    foreach ($row['upazila_items'] as $key=>$item) {

        $haors = Haor::where('upazila_id', $item['id'])->orderBy('name','asc')->select('thumb_img','area','name', 'id')->get();
        $row['upazila_items'][$key]['haors'] = $haors ? $haors->toArray() : array();
    }

    // get previous haor
    $prev = District::where('id', '<', $id)->orderBy('id','desc')->first();
    if(empty($prev)){
        $prev = District::orderBy('id','desc')->first();
    }
    // get next haor
    $next = District::where('id', '>', $id)->orderBy('id','asc')->first();
    if(empty($next)){
        $next = District::orderBy('id','asc')->first();
    }

    //echo "<pre>";print_r($prev);exit;

    $row['prev_district_id'] = $prev ? $prev->id : '';
    $row['prev_district_name'] = $prev ? $prev->name : '';
    $row['next_district_id'] = $next ? $next->id : '';
    $row['next_district_name'] = $next ? $next->name : '';

    return array_merge($data, $row);
}

function upazila($id){

    $data = page_fields();

    $row = Upazila::findOrFail($id);
    $row = $row ? $row->toArray() : array();

    $row['haor_items'] = Haor::where('upazila_id', $id)->orderBy('name','asc')->pluck('name', 'id');

    // get previous haor
    $prev = Upazila::where('id', '<', $id)->orderBy('id','desc')->first();
    if(empty($prev)){
        $prev = Upazila::orderBy('id','desc')->first();
    }
    // get next haor
    $next = Upazila::where('id', '>', $id)->orderBy('id','asc')->first();
    if(empty($next)){
        $next = Upazila::orderBy('id','asc')->first();
    }

    //echo "<pre>";print_r($prev);exit;

    $row['prev_upazila_id'] = $prev ? $prev->id : '';
    $row['prev_upazila_name'] = $prev ? $prev->name : '';
    $row['next_upazila_id'] = $next ? $next->id : '';
    $row['next_upazila_name'] = $next ? $next->name : '';

    return array_merge($data, $row);
}

function pages($url_title) {
    $data = page_fields();

    $row = Page::where('url_title', $url_title)->first();
    $row = $row ? $row->toArray() : array();

    return array_merge($data, $row);
};

function rivers() {
    $data = page_fields();

    $rivers = River::orderBy('id','asc')->get();
    $data['rivers'] = $rivers ? $rivers->toArray() : array();
    //echo "<pre>";print_r($data);exit;

    return $data;
};

function galleries() {
    $data = page_fields();

    $g_cats = GalleryCategory::orderBy('id','desc')->select('name', 'id')->get();
    $data['g_cats'] = $g_cats ? $g_cats->toArray() : array();

    foreach ($data['g_cats'] as $key=>$item) {
        $galleries = Gallery::where('gallery_category_id', $item['id'])->orderBy('id','asc')->select('image','name', 'id')->get();
        $data['g_cats'][$key]['galleries'] = $galleries ? $galleries->toArray() : array();
    }
    //echo "<pre>";print_r($data);exit;

    return $data;
};

function videos() {
    $data = page_fields();

    $videos = Video::orderBy('id','asc')->select('name', 'thumb_img', 'url', 'id')->get();
    $data['videos'] = $videos ? $videos->toArray() : array();

    //echo "<pre>";print_r($data);exit;
    return $data;
};

function wetlands() {
    $data = page_fields();

    $wetlands = Wetland::orderBy('id','asc')->select('thumb_img','area','district','name', 'id')->get();
    $data['wetlands'] = $wetlands ? $wetlands->toArray() : array();

    //echo "<pre>";print_r($data);exit;
    return $data;
};

function wetland_detail($id){

    $data = page_fields();

    $row = Wetland::findOrFail($id);
    $row = $row ? $row->toArray() : array();

    $json_fields = array(
        'gallery_items'
    );
    foreach ($row as $field => $content) {
        if(in_array($field, $json_fields) && !empty($content)){
            $row[$field] = json_decode($content, true);
        }
    }

    // get previous haor
    $prev = Wetland::where('id', '<', $id)->orderBy('id','desc')->first();
    if(empty($prev)){
        $prev = Wetland::orderBy('id','desc')->first();
    }
    // get next haor
    $next = Wetland::where('id', '>', $id)->orderBy('id','asc')->first();
    if(empty($next)){
        $next = Wetland::orderBy('id','asc')->first();
    }

    //echo "<pre>";print_r($prev);exit;

    $row['prev_haor_id'] = $prev ? $prev->id : '';
    $row['prev_haor_name'] = $prev ? $prev->name : '';
    $row['next_haor_id'] = $next ? $next->id : '';
    $row['next_haor_name'] = $next ? $next->name : '';

    return array_merge($data, $row);
}

Route::get('/', function () {
    $data = page_fields();

    $data["district_list"] = district_list();
    $data["upazila_list"] = upazila_list();
    $data["haor_list"] = haor_list();
    
    //echo "<pre>";print_r($data);exit;
    return view('pages.home', $data); // view('welcome');
}); 

Route::get('/district/{id}', function ($id) {
    $data = district($id);

    return view('pages.district', $data);
});

Route::get('/upazila/{id}', function ($id) {
    $data = upazila($id);

    return view('pages.upazila', $data);
});

Route::get('/haor-detail/{id}', function ($id) {
    $data = haor_detail($id);

    return view('pages.haor-detail', $data);
});

Route::get('/haors', function () {
    $data = haors();

    return view('pages.haors', $data);
});

Route::get('/statistics', function () {
    $data = page_fields();

    return view('pages.statistics', $data);
});
Route::get('/travel', function () {
    $data = page_fields();

    return view('pages.travel', $data);
});
Route::get('/resort', function () {
    $data = page_fields();

    return view('pages.resort', $data);
});
Route::get('/bird', function () {
    $data = page_fields();

    return view('pages.bird', $data);
});
Route::get('/fish', function () {
    $data = page_fields();

    return view('pages.fish', $data);
});

Route::get('/cookies', function () {
    $data = page_fields();

    return view('pages.cookies', $data);
});

Route::get('/privacy-policy', function () {
    $data = page_fields();

    return view('pages.privacy-policy', $data);
});

Route::get('/terms-of-use', function () {
    $data = page_fields();

    return view('pages.terms-of-use', $data);
});

Route::get('/pages/{url_title}', function ($url_title) {
    $data = pages($url_title);

    return view('pages.page', $data);
});

Route::get('/rivers', function () {
    $data = rivers();
    
    return view('pages.river', $data);
});

Route::get('/galleries', function () {
    $data = galleries();
    
    return view('pages.gallery', $data);
});

Route::get('/videos', function () {
    $data = videos();
    
    return view('pages.videos', $data);
});

Route::get('/video/{id}', function ($id) {
    $data = page_fields();

    $video = Video::findOrFail($id);
    $data['video'] = $video ? $video->toArray() : array();
    //echo "<pre>";print_r($data);exit;
    
    return view('pages.video-detail', $data);
});

Route::get('/wetlands', function () {
    $data = wetlands();
    
    return view('pages.wetlands', $data);
});

Route::get('/wetland-detail/{id}', function ($id) {
    $data = wetland_detail($id);

    return view('pages.wetland-detail', $data);
});

Route::get('/map', function () {
    $data = page_fields();

    return view('pages.map', $data);
});