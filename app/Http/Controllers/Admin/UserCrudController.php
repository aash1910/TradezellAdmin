<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as BackpackUserCrudController;
use App\Http\Requests\UserRequest;
use App\Constants\Countries;

class UserCrudController extends BackpackUserCrudController
{
    // Override any methods you want to customize here
    
    public function setupListOperation()
    {
        // Fix the type error by converting to collection first
        $roleModel = app(config('permission.models.role'));
        $permissionModel = app(config('permission.models.permission'));
        $roles = collect($roleModel->all())->pluck('name', 'id');
        $permissions = collect($permissionModel->all())->pluck('name', 'id');

        $this->crud->addColumns([
            [
                'name'  => 'first_name',
                'label' => 'First Name',
                'type'  => 'text',
            ],
            [
                'name'  => 'last_name',
                'label' => 'Last Name',
                'type'  => 'text',
            ],
            [
                'name'  => 'mobile',
                'label' => 'Mobile',
                'type'  => 'text',
            ],
            [
                'name'  => 'email',
                'label' => trans('backpack::permissionmanager.email'),
                'type'  => 'email',
            ],
            [ 
                'label'     => trans('backpack::permissionmanager.roles'),
                'type'      => 'select_multiple',
                'name'      => 'roles',
                'entity'    => 'roles',
                'attribute' => 'name',
                'model'     => config('permission.models.role'),
            ],
            [
                'name'  => 'is_verified',
                'label' => 'OTP Verified',
                'type'  => 'boolean',
                'wrapper' => [
                    'element' => 'span',
                    'class' => function ($crud, $column, $entry, $related_key) {
                        return $entry->is_verified 
                            ? 'badge badge-success' 
                            : 'badge badge-danger';
                    }
                ],
            ],
            [
                'name'  => 'status',
                'label' => 'Status',
                'type'  => 'text',
                'wrapper' => [
                    'element' => 'span',
                    'class' => function ($crud, $column, $entry, $related_key) {
                        switch ($entry->status) {
                            case 'active':
                                return 'badge badge-success';
                            case 'inactive':
                                return 'badge badge-danger';
                            case 'pending':
                                return 'badge badge-warning';
                            default:
                                return 'badge badge-default';
                        }
                    }
                ]
            ],
        ]);

        // Role Filter
        $this->crud->addFilter(
            [
                'name'  => 'role',
                'type'  => 'dropdown',
                'label' => trans('backpack::permissionmanager.role'),
            ],
            $roles->toArray(),
            function ($value) {
                $this->crud->addClause('whereHas', 'roles', function ($query) use ($value) {
                    $query->where('role_id', '=', $value);
                });
            }
        );

    }

    public function setupCreateOperation()
    {
        $this->crud->removeSaveActions(['save_and_new','save_and_preview']);
        $this->crud->setValidation(UserRequest::class);
        $this->crud->setOperationSetting('contentClass', 'col-md-12 bold-labels');

        $this->crud->field('first_name')
                ->type('text')
                ->label('First Name')
                ->size(3);

        $this->crud->field('last_name')
                ->type('text')
                ->label('Last Name')
                ->size(3);

        $this->crud->field('email')
                ->type('email')
                ->label('Email')
                ->size(3);

        $this->crud->field('mobile')
                ->type('text')
                ->label('Mobile')
                ->size(3);

        $this->crud->field('address')
                ->type('textarea')
                ->label('Address')
                ->size(6);

        $this->crud->field('password')
                ->type('password')
                ->label('Password')
                ->size(3);

        $this->crud->field('password_confirmation')
                ->type('password')
                ->label('Password Confirmation')
                ->size(3);

        $this->crud->field('date_of_birth')
                ->type('date')
                ->label('Date of Birth')
                ->size(3);

        $this->crud->field('gender')
                ->type('select2_from_array')
                ->label('Gender')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                ])
                ->allows_null(true)
                ->placeholder('-')
                ->size(3);

        $this->crud->field('nationality')
                ->type('select2_from_array')
                ->label('Nationality')
                ->options(array_combine(Countries::getList(), Countries::getList()))
                ->allows_null(true)
                ->placeholder('-')
                ->size(3);

        $this->crud->field('status')
                ->type('select2_from_array')
                ->label('Status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'pending' => 'Pending',
                ])
                ->size(3);

        $this->crud->field('image')
                ->type('image')
                ->label('Image')
                ->upload(true)
                ->disk('public')
                ->size(3);

        $this->crud->field('document')
                ->type('image')
                ->label('Document')
                ->upload(true)
                ->disk('public')
                ->size(3);

        $this->crud->addField([
            'name'              => ['roles', 'permissions'],
            'label'             => trans('backpack::permissionmanager.user_role_permission'),
            'field_unique_name' => 'user_role_permission',
            'type'              => 'checklist_dependency',
            'subfields'         => [
                'primary' => [
                    'label'            => trans('backpack::permissionmanager.roles'),
                    'name'             => 'roles',
                    'entity'           => 'roles',
                    'entity_secondary' => 'permissions',
                    'attribute'        => 'name',
                    'model'            => config('permission.models.role'),
                    'pivot'            => true,
                    'number_columns'   => 3,
                ],
                'secondary' => [
                    'label'          => ucfirst(trans('backpack::permissionmanager.permission_singular')),
                    'name'           => 'permissions',
                    'entity'         => 'permissions',
                    'entity_primary' => 'roles',
                    'attribute'      => 'name',
                    'model'          => config('permission.models.permission'),
                    'pivot'          => true,
                    'number_columns' => 3,
                ],
            ],
            'wrapper' => [
                'class' => 'col-md-6',
            ],
        ]);

        $this->crud->field('otp')
                ->type('hidden');

        $this->crud->field('otp_expires_at')
                ->type('hidden');

        $this->crud->field('is_verified')
                ->type('checkbox')
                ->label('Is OTP Verified')
                ->size(3);

    }

    public function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        
        // Show OTP field for update operation
        $this->crud->field('otp')
            ->type('text')
            ->label('OTP')
            ->size(3);

        $this->crud->field('otp_expires_at')
            ->type('datetime')
            ->label('OTP Expires At')
            ->size(3);

    }
    
} 