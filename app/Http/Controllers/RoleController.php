<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;

class RoleController extends BaseController
{
    /**
     * The model class.
     */
    protected string $modelClass = Role::class;

    /**
     * The resource class for transforming the model.
     */
    protected string $resource = RoleResource::class;

    /**
     * The form request class for validation.
     */
    protected string $storeRequest = RoleRequest::class;

    /**
     * The form request class for update validation.
     */
    protected string $updateRequest = RoleRequest::class;

    /**
     * Searchable fields for filtering.
     */
    protected array $searchable = [
        'name' => [
            'column' => 'name',
            'condition' => '%like%',
        ],
        'code' => [
            'column' => 'code',
            'condition' => 'equal',
        ],
    ];

    /**
     * Sortable fields.
     */
    protected array $sortable = ['id', 'name', 'code', 'created_at', 'updated_at'];
}
