<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class MatchCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\TradezellMatch::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/match');
        CRUD::setEntityNameStrings('match', 'matches');
        CRUD::denyAccess(['create', 'update', 'delete']);
    }

    protected function setupListOperation()
    {
        CRUD::addColumns([
            ['name' => 'id',           'label' => 'ID'],
            [
                'name'  => 'user_one_id',
                'label' => 'User One',
                'type'  => 'closure',
                'function' => function ($entry) {
                    $u = $entry->userOne;
                    return $u ? "{$u->first_name} {$u->last_name} (#{$u->id})" : 'N/A';
                },
            ],
            [
                'name'  => 'user_two_id',
                'label' => 'User Two',
                'type'  => 'closure',
                'function' => function ($entry) {
                    $u = $entry->userTwo;
                    return $u ? "{$u->first_name} {$u->last_name} (#{$u->id})" : 'N/A';
                },
            ],
            ['name' => 'status',       'label' => 'Status'],
            ['name' => 'unmatched_at', 'label' => 'Unmatched At'],
            ['name' => 'created_at',   'label' => 'Matched At'],
        ]);

        CRUD::addFilter([
            'name'  => 'status',
            'type'  => 'dropdown',
            'label' => 'Status',
        ], ['active' => 'Active', 'unmatched' => 'Unmatched'],
        function ($value) {
            CRUD::addClause('where', 'status', $value);
        });
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }
}
