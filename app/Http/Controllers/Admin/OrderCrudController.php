<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\OrderRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class OrderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class OrderCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Order::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/order');
        CRUD::setEntityNameStrings('order', 'orders');
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
        
        CRUD::column('id')->label('ID');
        CRUD::column('package_id')
            ->label('Package')
            ->entity('package')
            ->model('App\Models\Package')
            ->type('closure')
            ->function(function($entry) {
                return $entry->package ? $entry->package->id . ' - ' . $entry->package->package_info : '-';
            });
        //CRUD::column('package_id') -> label('Package') -> entity('package') -> model('App\Models\Package') -> attribute('package_info');
        CRUD::column('dropper_id') -> label('Dropper') -> entity('dropper') -> model('App\User') -> attribute('full_name');
        CRUD::column('status')->wrapper([
            'element' => 'span',
            'class' => function ($crud, $column, $entry, $related_key) {
                switch ($entry->status) {
                    case 'ongoing':
                        return 'badge badge-default';
                    case 'active':
                        return 'badge badge-info';
                    case 'canceled':
                        return 'badge badge-danger';
                    case 'completed':
                        return 'badge badge-success';
                    default:
                        return 'badge badge-default';
                }
            }
        ]);
        CRUD::column('created_at')->label('Order Date');
        CRUD::column('updated_at')
            ->label('Delivery Date')
            ->type('closure')
            ->function(function($entry) {
                return $entry->status === 'completed' ? $entry->updated_at : '-';
            });

        CRUD::addFilter([
        'type' => 'date',
        'name' => 'order_date',
        'label'=> 'Order Date'
        ],
        false,
        function($value) {
            CRUD::addClause('whereDate', 'created_at', $value);
        });
        
        CRUD::addFilter([
        'type' => 'date',
        'name' => 'delivery_date',
        'label'=> 'Delivery Date'
        ],
        false,
        function($value) {
            CRUD::addClause('whereDate', 'updated_at', $value);
            // CRUD::addClause('whereHas', 'package', function ($query) use ($value) {
            //     $query->whereDate('pickup_date', $value); // or 'delivery_date' if you add that column
            // });
        });
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(OrderRequest::class);

        CRUD::addField([
            'name' => 'package_id',
            'label' => 'Package',
            'type' => 'select2',
            'entity' => 'package',
            'model' => 'App\Models\Package',
            'attribute' => 'package_info',
            'allows_null' => true,
            'placeholder' => '-'
        ]);
        
        CRUD::addField([
            'name' => 'dropper_id',
            'label' => 'Dropper',
            'type' => 'select2',
            'entity' => 'dropper',
            'model' => 'App\User',
            'attribute' => 'full_name',
            'options'   => (function ($query) {
                return $query->whereHas('roles', function($q) {
                    $q->whereIn('name', ['dropper']);
                })->get();
            }),
            'allows_null' => true,
            'placeholder' => '-'
        ]);
        
        CRUD::addField([
            'name' => 'status',
            'type' => 'select_from_array',
            'options' => [
                'ongoing' => 'Ongoing',
                'active' => 'Active',
                'canceled' => 'Canceled',
                'completed' => 'Completed',
            ],
            'allows_null' => false,
            'default' => 'ongoing',
        ]);

        CRUD::addField([
            'name' => 'delivery_status',
            'type' => 'select_from_array',
            'options' => [
                '0' => 'Ongoing',
                '1' => 'Delivered',
            ],
            'allows_null' => false,
            'default' => '0',
        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
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
