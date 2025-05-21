<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LandingPageRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Class LandingPageCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LandingPageCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\LandingPage::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/landing-page');
        CRUD::setEntityNameStrings('landing page', 'landing pages');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

        $this->crud->setHeading('Landing Page');
        $this->crud->denyAccess('create');
        $this->crud->denyAccess('show');
        $this->crud->denyAccess('delete');

        $this->crud->addColumns([
            [
                'name' => 'page',
                'type' => 'closure',
                'function' => function($entry) {
                    return 'Landing Page';
                },
            ],
            [
                'name' => 'Site URL',
                'type' => 'closure',
                'function' => function($entry) {
                    return '<a class="btn btn-sm btn-link" target="_blank" href="/"><i class="la la-eye"></i> Open</a>';
                },
            ],
        ]);
        

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setHeading('Landing Page');
        $this->crud->removeSaveActions(['save_and_new','save_and_preview']);
        CRUD::setValidation(LandingPageRequest::class);
        CRUD::setOperationSetting('contentClass', 'col-md-12 bold-labels');


        // -----------------
        // header tab
        // -----------------

        CRUD::field('topbar_logo')
                ->type('image')
                ->label('Header Logo')
                ->upload(true)
                ->disk('public')
                ->tab('Header')
                ->size(4);

        CRUD::field('topbar_telephone')
                ->type('text')
                ->label('Header Telephone')
                ->tab('Header')
                ->size(4);

        CRUD::field('topbar_email')
                ->type('text')
                ->label('Header Email')
                ->tab('Header')
                ->size(4);

        CRUD::field('topbar_menu_items')
                ->type('table')
                ->label('Menu Items')
                ->columns([
                    'text'  => 'Text',
                    'url'  => 'URL',
                ])
                ->tab('Header')
                ->size(12);

        CRUD::addField([
            'name' => 'social_media_menu_items',
            'label' => 'Social Media Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name'    => 'icon',
                    'label'   => 'Icon',
                    'type'    => 'icon_picker',
                    'iconset' => 'fontawesome5', // options: fontawesome, glyphicon, ionicon, weathericon, mapicon, octicon, typicon, elusiveicon, materialdesign
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-8',
                    ],
                    
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Header',
            'size' => 12,
        ]);

        CRUD::addField([
            'name' => 'mega_menu_items',
            'label' => 'Mega Menu Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'image',
                    'label' => 'Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    
                ],
                [
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Header',
            'size' => 12,
        ]);


        // -----------------
        // Home tab
        // -----------------

        CRUD::field('home_top_hero_image')
                ->type('image')
                ->label('Top Background Image')
                ->upload(true)
                ->disk('public')
                ->tab('Home')
                ->size(6);

        CRUD::field('home_top_hero_video_url')
                ->type('text')
                ->label('Top Background Video URL')
                ->tab('Home')
                ->size(6);

        CRUD::field('home_top_hero_title')
                ->type('text')
                ->label('Top Title')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_top_hero_text')
                ->type('textarea')
                ->label('Top Text')
                ->tab('Home')
                ->size(8);

        CRUD::field('home_exploring_title')
                ->type('text')
                ->label('Exploring Section Title')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_exploring_text')
                ->type('textarea')
                ->label('Exploring Section Text')
                ->tab('Home')
                ->size(8);

        CRUD::addField([
            'name' => 'home_exploring_items',
            'label' => 'Exploring Section Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'image',
                    'label' => 'Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    
                ],
                [
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Home',
            'size' => 12,
        ]);

        CRUD::field('home_statistics_total_haors')
                ->type('text')
                ->label('Statistic Section Total Haors')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_statistics_total_area')
                ->type('text')
                ->label('Statistic Section Total Area')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_statistics_total_projects')
                ->type('text')
                ->label('Statistic Section Total Projects')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_featured_haors_title')
                ->type('text')
                ->label('Featured Section Title')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_featured_haors_sub_title')
                ->type('text')
                ->label('Featured Section Sub Title')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_featured_haors_view_all_url')
                ->type('text')
                ->label('Featured Section View All URL')
                ->tab('Home')
                ->size(4);

        CRUD::addField([
            'name' => 'home_featured_haors_items',
            'label' => 'Featured Section Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'image',
                    'label' => 'Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    
                ],
                [
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
                [
                    'name' => 'subtitle',
                    'label' => 'Sub Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Home',
            'size' => 12,
        ]);

        CRUD::field('home_haor_map_title')
                ->type('text')
                ->label('Map Section Title')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_haor_map_text')
                ->type('textarea')
                ->label('Map Section Text')
                ->tab('Home')
                ->size(8);

        CRUD::addField([
            'name' => 'home_haor_map_items',
            'label' => 'Map Section Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'district',
                    'label' => 'District',
                    'type' => 'select_from_array',
                    'options' => ["Sylhet" => "Sylhet", "Sunamgang" => "Sunamgang", "Netrokona" => "Netrokona", "Kishoreganj" => "Kishoreganj", "Brahmanbaria" => "Brahmanbaria", "Habiganj" => "Habiganj", "Maulvibazar" => "Maulvibazar"],
                    'allows_null' => false,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-8',
                    ],
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Home',
            'size' => 12,
        ]);

        CRUD::field('home_conservation_effects_title')
                ->type('text')
                ->label('Conservation Effects Section Title')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_conservation_effects_text')
                ->type('textarea')
                ->label('Conservation Effects Section Text')
                ->tab('Home')
                ->size(8);

        CRUD::addField([
            'name' => 'home_conservation_effects_items',
            'label' => 'Conservation Effects Section Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'image',
                    'label' => 'Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    
                ],
                [
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
                [
                    'name' => 'text',
                    'label' => 'Text',
                    'type' => 'textarea',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Home',
            'size' => 12,
        ]);

        CRUD::field('home_summary_report_title')
                ->type('text')
                ->label('Report Section Title')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_summary_report_sub_title')
                ->type('text')
                ->label('Report Section Sub Title')
                ->tab('Home')
                ->size(4);

        CRUD::field('home_summary_report_view_all_url')
                ->type('text')
                ->label('Report Section View All URL')
                ->tab('Home')
                ->size(4);

        CRUD::addField([
            'name' => 'home_summary_report_items',
            'label' => 'Report Section Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'image',
                    'label' => 'Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    
                ],
                [
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
                [
                    'name' => 'subtitle',
                    'label' => 'Sub Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Home',
            'size' => 12,
        ]);

        CRUD::field('home_recreation_tourism_title')
                ->type('text')
                ->label('Recreation and Tourism Section Title')
                ->tab('Home')
                ->size(12);

        CRUD::addField([
            'name' => 'home_recreation_tourism_items',
            'label' => 'Recreation and Tourism Section Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'image',
                    'label' => 'Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    
                ],
                [
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
                [
                    'name' => 'subtitle',
                    'label' => 'Sub Title',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'text',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Home',
            'size' => 12,
        ]);

        CRUD::addField([
            'name' => 'home_gallery_items',
            'label' => 'Gallery Section Items',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'image',
                    'label' => 'Thumbnail Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-6',
                    ],
                ],
                [
                    'name' => 'image2',
                    'label' => 'Modal Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-6',
                    ],
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Home',
            'size' => 12,
        ]);

        // -----------------
        // Statistics Page tab
        // -----------------

        CRUD::field('statistics_page_title')
                ->type('text')
                ->label('Title')
                ->tab('Statistics Page')
                ->size(3);

        CRUD::field('statistics_page_header_image')
                ->type('image')
                ->label('Header Image')
                ->upload(true)
                ->disk('public')
                ->tab('Statistics Page')
                ->size(3);

        CRUD::field('statistics_page_overview')
                ->type('textarea')
                ->label('Overview')
                ->tab('Statistics Page')
                ->size(6);

        CRUD::field('statistics_page_content')
                ->type('ckeditor')
                ->label('Content')
                ->tab('Statistics Page')
                ->options([
                    'filebrowserUploadUrl'=> route('upload', ['_token' => csrf_token() ]),
                    'filebrowserUploadMethod'=> 'form'
                ])
                ->size(8);

        CRUD::field('statistics_page_right_content')
                ->type('ckeditor')
                ->label('Right Content')
                ->tab('Statistics Page')
                ->size(4);

        // -----------------
        // Bird Page tab
        // -----------------

        CRUD::field('bird_page_title')
                ->type('text')
                ->label('Title')
                ->tab('Bird Page')
                ->size(3);

        CRUD::field('bird_page_header_image')
                ->type('image')
                ->label('Header Image')
                ->upload(true)
                ->disk('public')
                ->tab('Bird Page')
                ->size(3);

        CRUD::field('bird_page_overview')
                ->type('textarea')
                ->label('Overview')
                ->tab('Bird Page')
                ->size(6);

        CRUD::field('bird_page_content')
                ->type('ckeditor')
                ->label('Content')
                ->tab('Bird Page')
                ->options([
                    'filebrowserUploadUrl'=> route('upload', ['_token' => csrf_token() ]),
                    'filebrowserUploadMethod'=> 'form'
                ])
                ->size(12);

        // -----------------
        // Fish Page tab
        // -----------------

        CRUD::field('fish_page_title')
                ->type('text')
                ->label('Title')
                ->tab('Fish Page')
                ->size(3);

        CRUD::field('fish_page_header_image')
                ->type('image')
                ->label('Header Image')
                ->upload(true)
                ->disk('public')
                ->tab('Fish Page')
                ->size(3);

        CRUD::field('fish_page_overview')
                ->type('textarea')
                ->label('Overview')
                ->tab('Fish Page')
                ->size(6);

        CRUD::field('fish_page_content')
                ->type('ckeditor')
                ->label('Content')
                ->tab('Fish Page')
                ->options([
                    'filebrowserUploadUrl'=> route('upload', ['_token' => csrf_token() ]),
                    'filebrowserUploadMethod'=> 'form'
                ])
                ->size(12);

        // -----------------
        // Travel Page tab
        // -----------------

        CRUD::field('travel_page_title')
                ->type('text')
                ->label('Title')
                ->tab('Travel Page')
                ->size(6);

        CRUD::field('travel_page_header_image')
                ->type('image')
                ->label('Header Image')
                ->upload(true)
                ->disk('public')
                ->tab('Travel Page')
                ->size(6);

        CRUD::field('travel_page_how_to_go_content')
                ->type('ckeditor')
                ->label('How To Go Content')
                ->tab('Travel Page')
                ->size(8);

        CRUD::field('travel_page_how_to_go_image')
                ->type('image')
                ->label('How To Go Image')
                ->upload(true)
                ->disk('public')
                ->tab('Travel Page')
                ->size(4);

        CRUD::field('travel_page_where_to_stay_image')
                ->type('image')
                ->label('Where to Stay Image')
                ->upload(true)
                ->disk('public')
                ->tab('Travel Page')
                ->size(4);

        CRUD::field('travel_page_where_to_stay_content')
                ->type('ckeditor')
                ->label('Where to Stay Content')
                ->tab('Travel Page')
                ->size(8);

        // -----------------
        // Resort Page tab
        // -----------------

        CRUD::field('resort_page_title')
                ->type('text')
                ->label('Title')
                ->tab('Resort Page')
                ->size(3);

        CRUD::field('resort_page_header_image')
                ->type('image')
                ->label('Header Image')
                ->upload(true)
                ->disk('public')
                ->tab('Resort Page')
                ->size(3);

        CRUD::addField([
            'name' => 'resort_page_hotel_list',
            'label' => 'Hotel List',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'image',
                    'label' => 'Image',
                    'type' => 'image',
                    'disk' => 'public',
                    'upload' => true,
                    //'prefix'    => '/storage/',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    
                ],
                [
                    'name' => 'content',
                    'label' => 'Content',
                    'type' => 'ckeditor',
                    'wrapper' => [
                        'class' => 'col-md-6',
                    ],
                ],
                [
                    'name' => 'contact',
                    'label' => 'Contact',
                    'type' => 'ckeditor',
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                ],
            ],
            'new_item_label' => 'Add',
            'tab' => 'Resort Page',
            'size' => 12,
        ]);


        // -----------------
        // Cookie tab
        // -----------------

        CRUD::field('cookie_policy_title')
                ->type('text')
                ->label('Title')
                ->tab('Cookie Policy')
                ->size(6);

        CRUD::field('cookie_policy_content')
                ->type('ckeditor')
                ->label('Content')
                ->tab('Cookie Policy')
                ->size(12);


        CRUD::field('privacy_policy_title')
                ->type('text')
                ->label('Title')
                ->tab('Privacy Policy')
                ->size(6);

        CRUD::field('privacy_policy_content')
                ->type('ckeditor')
                ->label('Content')
                ->tab('Privacy Policy')
                ->size(12);

        CRUD::field('terms_conditions_title')
                ->type('text')
                ->label('Title')
                ->tab('Terms and Conditions')
                ->size(6);

        CRUD::field('terms_conditions_content')
                ->type('ckeditor')
                ->label('Content')
                ->tab('Terms and Conditions')
                ->size(12);


        // -----------------
        // Footer tab
        // -----------------

        CRUD::field('footer_logo')
                ->type('image')
                ->label('Footer Logo')
                ->upload(true)
                ->disk('public')
                ->tab('Footer')
                ->size(6);

        CRUD::field('footer_text')
                ->type('text')
                ->label('Footer Text')
                ->tab('Footer')
                ->size(6);

        CRUD::field('footer_contact_address')
                ->type('text')
                ->label('Footer Contact Address')
                ->tab('Footer')
                ->size(6);

        CRUD::field('footer_copyright_text')
                ->type('ckeditor')
                ->label('Footer Copyright Text')
                ->tab('Footer')
                ->size(6);

        CRUD::field('footer_link_items')
                ->type('table')
                ->label('Section 1 Link Items')
                ->columns([
                    'text'  => 'Text',
                    'url'  => 'URL',
                ])
                ->tab('Footer')
                ->size(6);

        CRUD::field('footer_link_items_section2')
                ->type('table')
                ->label('Section 2 Link Items')
                ->columns([
                    'text'  => 'Text',
                    'url'  => 'URL',
                ])
                ->tab('Footer')
                ->size(6);

    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }


    public function ckeditor_image_upload(Request $request)
    {

        if($request->hasFile('upload')) {

            $filenamewithextension = $request->file('upload')->getClientOriginalName();
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $filenametostore = $filename.'_'.time().'.'.$extension;
    
            //Upload File
            $request->file('upload')->move('uploads/images', $filenametostore);
    
            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url = asset('uploads/images/'.$filenametostore); 
            $msg = 'Image successfully uploaded'; 
            $re = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
            
            // Render HTML output 
            @header('Content-type: text/html; charset=utf-8'); 
            echo $re;
        }
    }

}
