<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PackageRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PackageCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PackageCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Package::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/package');
        CRUD::setEntityNameStrings('package', 'packages');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // remove preview button 
        $this->crud->denyAccess('show');
        

        CRUD::column('sender_id')->entity('sender')->model('App\User')->attribute('full_name');
        CRUD::column('pickup_name');
        CRUD::column('pickup_mobile');
        CRUD::column('pickup_address');
        CRUD::column('weight');
        CRUD::column('price');
        CRUD::addColumn([
            'name'     => 'pickup_datetime',
            'label'    => 'Pickup Date & Time',
            'type'     => 'closure',
            'function' => function($entry) {
                return date('d M Y h:i A', strtotime($entry->pickup_date . ' ' . $entry->pickup_time));
            }
        ]);
        CRUD::column('drop_name')->label('Drop-off Name');
        CRUD::column('drop_mobile')->label('Drop-off Number');
        CRUD::column('drop_address')->label('Drop-off Location');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PackageRequest::class);
        CRUD::removeSaveActions(['save_and_preview']);
        CRUD::setOperationSetting('contentClass', 'col-md-12 bold-labels');

        // -----------------
        // Pick-up details tab
        // -----------------

        CRUD::addField([
            'name' => 'sender_id',
            'label' => 'Sender',
            'type' => 'select2',
            'entity' => 'sender',
            'model' => "App\User",
            'attribute' => 'full_name',
            'options'   => (function ($query) {
                return $query->role('sender')->get();
            }),
            'wrapper' => [
                'class' => 'form-group col-md-4',
            ],
            'tab' => 'Pick-up details',
        ]);
        CRUD::addField([
            'name' => 'pickup_name',
            'label' => 'Name',
            'tab' => 'Pick-up details',
            'wrapper' => [
                'class' => 'form-group col-md-4',
            ],
        ]);
        CRUD::addField([
            'name' => 'pickup_mobile',
            'label' => 'Number',
            'tab' => 'Pick-up details',
            'wrapper' => [
                'class' => 'form-group col-md-4',
            ],
        ]);
        CRUD::addField([
            'name' => 'weight',
            'label' => 'Weight',
            'type' => 'number',
            'attributes' => ["step" => "0.01"],
            'tab' => 'Pick-up details',
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
        ]);
        CRUD::addField([
            'name' => 'price',
            'label' => 'Price',
            'type' => 'number',
            'attributes' => ["step" => "0.01"],
            'tab' => 'Pick-up details',
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
        ]);
        CRUD::addField([
            'name' => 'pickup_date',
            'label' => 'Date',
            'type' => 'date',
            'tab' => 'Pick-up details',
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
        ]);
        CRUD::addField([
            'name' => 'pickup_time',
            'label' => 'Time',
            'type' => 'time',
            'tab' => 'Pick-up details',
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
        ]);
        
        CRUD::addField([
            'name' => 'pickup_address',
            'label' => 'Location',
            'type' => 'textarea',
            'tab' => 'Pick-up details',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        
        CRUD::addField([
            'name' => 'pickup_details',
            'label' => 'More Details',
            'type' => 'textarea',
            'tab' => 'Pick-up details',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        // -----------------
        // Drop-off details tab
        // -----------------
        
        CRUD::addField([
            'name' => 'drop_name',
            'label' => 'Name',
            'tab' => 'Drop-off details',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        CRUD::addField([
            'name' => 'drop_mobile',
            'label' => 'Number',
            'tab' => 'Drop-off details', 
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        CRUD::addField([
            'name' => 'drop_address', 
            'label' => 'Location',
            'type' => 'textarea',
            'tab' => 'Drop-off details',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ], 
        ]);
        CRUD::addField([
            'name' => 'drop_details',
            'label' => 'More Details',
            'type' => 'textarea',
            'tab' => 'Drop-off details', 
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
        
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
}
