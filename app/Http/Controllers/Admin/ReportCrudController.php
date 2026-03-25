<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ReportCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Report::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/report');
        CRUD::setEntityNameStrings('report', 'reports');
        CRUD::denyAccess(['create']);
    }

    protected function setupListOperation()
    {
        CRUD::addColumns([
            ['name' => 'id',         'label' => 'ID'],
            [
                'name'     => 'reporter_id',
                'label'    => 'Reporter',
                'type'     => 'closure',
                'function' => function ($entry) {
                    $u = $entry->reporter;
                    return $u ? "{$u->first_name} {$u->last_name} (#{$u->id})" : 'N/A';
                },
            ],
            ['name' => 'reportable_type', 'label' => 'Type'],
            ['name' => 'reason',          'label' => 'Reason'],
            ['name' => 'status',          'label' => 'Status'],
            ['name' => 'created_at',      'label' => 'Reported At'],
        ]);

        CRUD::addFilter([
            'name'  => 'status',
            'type'  => 'dropdown',
            'label' => 'Status',
        ], ['pending' => 'Pending', 'reviewed' => 'Reviewed', 'dismissed' => 'Dismissed'],
        function ($value) {
            CRUD::addClause('where', 'status', $value);
        });
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
        CRUD::addColumns([
            ['name' => 'description', 'label' => 'Description'],
        ]);
    }

    protected function setupUpdateOperation()
    {
        CRUD::addFields([
            [
                'name'    => 'status',
                'label'   => 'Status',
                'type'    => 'select_from_array',
                'options' => ['pending' => 'Pending', 'reviewed' => 'Reviewed', 'dismissed' => 'Dismissed'],
            ],
        ]);
    }
}
