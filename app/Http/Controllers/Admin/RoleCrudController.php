<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\RoleCrudController as BackpackRoleCrudController;

/**
 * Class RoleCrudController
 * Custom Role CRUD Controller with disabled delete operation
 * 
 * @author Ashraful Islam
 * @package App\Http\Controllers\Admin
 */
class RoleCrudController extends BackpackRoleCrudController
{
    public function setup()
    {
        parent::setup();
        
        // Disable delete and bulk delete operations
        $this->crud->denyAccess(['delete']);
    }

    public function setupListOperation()
    {
        // Call parent to setup default columns
        parent::setupListOperation();
        
        // Remove the permissions column from the list view
        $this->crud->removeColumn('permissions');
    }

    public function setupCreateOperation()
    {
        // Call parent to setup default fields
        parent::setupCreateOperation();
        
        // Remove the permissions field from the create form
        $this->crud->removeField('permissions');
    }

    public function setupUpdateOperation()
    {
        // Call parent to setup default fields
        parent::setupUpdateOperation();
        
        // Remove the permissions field from the edit form
        $this->crud->removeField('permissions');
    }
}

