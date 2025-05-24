<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ReviewRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ReviewCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ReviewCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Review::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/review');
        CRUD::setEntityNameStrings('review', 'reviews');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addColumn([
            'name' => 'order_id',
            'label' => 'Order',
            'type' => 'relationship',
            'entity' => 'order',
            'model' => 'App\Models\Order',
            'attribute' => 'order_info'
        ]);

        CRUD::addColumn([
            'name' => 'reviewer_id',
            'label' => 'Reviewer',
            'type' => 'relationship',
            'entity' => 'reviewer',
            'model' => 'App\User',
            'attribute' => 'full_name'
        ]);

        CRUD::addColumn([
            'name' => 'reviewee_id',
            'label' => 'Reviewee',
            'type' => 'relationship',
            'entity' => 'reviewee',
            'model' => 'App\User',
            'attribute' => 'full_name'
        ]);

        CRUD::addColumn(['name' => 'rating']);
        CRUD::addColumn(['name' => 'review_text']);

        $this->crud->denyAccess('show');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ReviewRequest::class);

        CRUD::field('order_id') ->type('select') ->entity('order') ->model('App\Models\Order') ->attribute('order_info') ->allows_null(true) ->placeholder('-');
        CRUD::addField([
            'name' => 'reviewer_id',
            'label' => 'Reviewer',
            'type' => 'select2',
            'entity' => 'reviewer',
            'model' => 'App\User',
            'attribute' => 'full_name',
            'options'   => (function ($query) {
                return $query->role(['sender', 'dropper'])->get();
            }), 
            'allows_null' => true,
            'placeholder' => '-'
        ]);
        CRUD::addField([
            'name' => 'reviewee_id',
            'label' => 'Reviewee',
            'type' => 'select2',
            'entity' => 'reviewee',
            'model' => 'App\User',
            'attribute' => 'full_name',
            'options'   => (function ($query) {
                return $query->role(['sender', 'dropper'])->get();
            }),
            'allows_null' => true,
            'placeholder' => '-'
        ]);
        CRUD::field('rating')
            ->type('select2_from_array')
            ->options([
                1 => '1 Star',
                2 => '2 Stars',
                3 => '3 Stars',
                4 => '4 Stars',
                5 => '5 Stars'
            ])
            ->allows_null(true) ->placeholder('-');
        CRUD::field('review_text') ->type('textarea');
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
