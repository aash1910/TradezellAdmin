<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ListingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
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
        ]);
    }

    protected function setupUpdateOperation()
    {
        CRUD::addFields([
            [
                'name'    => 'status',
                'label'   => 'Status',
                'type'    => 'select_from_array',
                'options' => ['active' => 'Active', 'paused' => 'Paused', 'sold' => 'Sold', 'traded' => 'Traded'],
            ],
            [
                'name'    => 'type',
                'label'   => 'Type',
                'type'    => 'select_from_array',
                'options' => ['trade' => 'Trade', 'sell' => 'Sell', 'both' => 'Both'],
            ],
            ['name' => 'title',       'label' => 'Title',       'type' => 'text'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'category',    'label' => 'Category',    'type' => 'text'],
            [
                'name'    => 'condition',
                'label'   => 'Condition',
                'type'    => 'select_from_array',
                'options' => ['new' => 'New', 'like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor'],
            ],
            ['name' => 'price',       'label' => 'Price (for sell)',  'type' => 'number'],
            ['name' => 'currency',    'label' => 'Currency',    'type' => 'text'],
        ]);
    }
}
