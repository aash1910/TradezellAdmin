<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ListingOptions;
use App\Http\Requests\ListingRequest;
use App\Models\Listing;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ListingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Listing::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/listing');
        CRUD::setEntityNameStrings('listing', 'listings');
    }

    protected function setupListOperation()
    {
        CRUD::addColumns([
            ['name' => 'id',          'label' => 'ID'],
            ['name' => 'title',       'label' => 'Title'],
            ['name' => 'type',        'label' => 'Type'],
            ['name' => 'status',      'label' => 'Status'],
            ['name' => 'category',    'label' => 'Category'],
            ['name' => 'condition',   'label' => 'Condition'],
            ['name' => 'price',       'label' => 'Price'],
            [
                'name'  => 'user_id',
                'label' => 'Owner',
                'type'  => 'closure',
                'function' => function ($entry) {
                    $user = $entry->user;
                    return $user ? "{$user->first_name} {$user->last_name} (#{$user->id})" : 'N/A';
                },
            ],
            ['name' => 'created_at',  'label' => 'Created'],
        ]);

        CRUD::addFilter([
            'name'  => 'status',
            'type'  => 'dropdown',
            'label' => 'Status',
        ], ['active' => 'Active', 'paused' => 'Paused', 'sold' => 'Sold', 'traded' => 'Traded'],
        function ($value) {
            CRUD::addClause('where', 'status', $value);
        });

        CRUD::addFilter([
            'name'  => 'type',
            'type'  => 'dropdown',
            'label' => 'Type',
        ], ['trade' => 'Trade', 'sell' => 'Sell', 'both' => 'Both'],
        function ($value) {
            CRUD::addClause('where', 'type', $value);
        });
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
        CRUD::addColumns([
            ['name' => 'description', 'label' => 'Description'],
            ['name' => 'currency',    'label' => 'Currency'],
            ['name' => 'lat',         'label' => 'Latitude'],
            ['name' => 'lng',         'label' => 'Longitude'],
            [
                'name'     => 'images',
                'label'    => 'Images',
                'type'     => 'closure',
                'function' => function ($entry) {
                    return $this->renderListingImagesHtml($entry);
                },
            ],
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ListingRequest::class);
        $this->addListingFormFields();
    }

    protected function setupUpdateOperation()
    {
        CRUD::setValidation(ListingRequest::class);
        $this->addListingFormFields();
    }

    protected function addListingFormFields(): void
    {
        $fields = [
            [
                'name'      => 'user_id',
                'label'     => 'Owner',
                'type'      => 'select2',
                'entity'    => 'user',
                'model'     => \App\User::class,
                'attribute' => 'full_name',
                'options'   => function ($query) {
                    return $query->where('id', '!=', 1)->orderBy('first_name')->get();
                },
            ],
            [
                'name'    => 'status',
                'label'   => 'Status',
                'type'    => 'select_from_array',
                'options' => ['active' => 'Active', 'paused' => 'Paused', 'sold' => 'Sold', 'traded' => 'Traded'],
                'default' => 'active',
            ],
            [
                'name'    => 'type',
                'label'   => 'Type',
                'type'    => 'select_from_array',
                'options' => ['trade' => 'Trade', 'sell' => 'Sell', 'both' => 'Both'],
                'default' => 'trade',
            ],
            ['name' => 'title',       'label' => 'Title',       'type' => 'text'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            [
                'name'          => 'category',
                'label'         => 'Category',
                'type'          => 'select_from_array',
                'options'       => ListingOptions::categoriesForSelect(),
                'allows_null'   => true,
                'placeholder'   => '—',
            ],
            [
                'name'    => 'condition',
                'label'   => 'Condition',
                'type'    => 'select_from_array',
                'options' => ['new' => 'New', 'like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor'],
                'allows_null' => true,
                'placeholder' => '—',
            ],
            ['name' => 'price',       'label' => 'Price (for sell)',  'type' => 'number'],
            [
                'name'    => 'currency',
                'label'   => 'Currency',
                'type'    => 'select_from_array',
                'options' => ListingOptions::CURRENCIES,
                'default' => 'USD',
            ],
            [
                'name'   => 'images',
                'label'  => 'Images',
                'type'   => 'view',
                'view'   => 'vendor.backpack.crud.listing.images_field',
                'upload' => true,
            ],
        ];

        CRUD::addFields($fields);
    }

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $request = $this->crud->getRequest();
        $request->request->remove('images');
        $this->crud->setRequest($request);

        $response = $this->traitStore();

        if (request()->hasFile('images')) {
            $entry = $this->crud->entry;
            $entry->syncImagesFromAdminRequest([]);
            $entry->save();
        }

        return $response;
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $existingImages = $this->crud->getCurrentEntry()->images ?? [];

        $request = $this->crud->getRequest();
        $request->request->remove('images');
        $this->crud->setRequest($request);

        $response = $this->traitUpdate();

        if (request()->hasFile('images') || request()->has('clear_images')) {
            $entry = $this->crud->entry->fresh();
            $entry->syncImagesFromAdminRequest($existingImages);
            $entry->save();
        }

        return $response;
    }

    private function renderListingImagesHtml(Listing $entry): string
    {
        $urls = $entry->getDisplayImageUrls();

        if (count($urls) === 0) {
            return '<span class="text-muted">No images</span>';
        }

        $html = '<div class="d-flex flex-wrap" style="gap:12px;">';
        foreach ($urls as $url) {
            $escaped = e($url);
            $html .= '<a href="' . $escaped . '" target="_blank" rel="noopener noreferrer">'
                . '<img src="' . $escaped . '" alt="Listing image" '
                . 'style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">'
                . '</a>';
        }
        $html .= '</div>';

        return $html;
    }
}
