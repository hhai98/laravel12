<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    /**
     * The model class.
     */
    protected string $modelClass = User::class;

    /**
     * The resource class for transforming the model.
     */
    protected string $resource = UserResource::class;

    /**
     * The form request class for validation.
     */
    protected string $storeRequest = UserRequest::class;

    /**
     * The form request class for update validation.
     */
    protected string $updateRequest = UserRequest::class;

    /**
     * Relations to eager load.
     */
    protected array $with = ['role'];

    /**
     * Searchable fields for filtering.
     */
    protected array $searchable = [
        'name' => [
            'column' => 'name',
            'condition' => '%like%',
        ],
        'phone' => [
            'column' => 'phone',
            'condition' => 'like%',
        ],
        'role_name' => [
            'column' => 'name',
            'condition' => '%like%',
            'relation' => 'role',
        ],
        'sns_driver' => [
            'column' => 'sns_driver',
            'condition' => 'equal',
        ],
    ];

    /**
     * Sortable fields.
     */
    protected array $sortable = [
        'name' => [
            'column' => 'name',
            'condition' => 'equal',
        ],
        'phone' => [
            'column' => 'phone',
            'condition' => '%like%',
        ],
        'role_name' => [
            'column' => 'name',
            'condition' => '%like%',
            'relation' => 'role',
        ],
    ];



    /**
     * Apply additional filters to query.
     */
    protected function applyFilters($query, Request $request): void
    {
        // Filter by role
        if ($request->has('role_id')) {
            $query->where('role_id', $request->get('role_id'));
        }

        // Filter by SNS driver
        if ($request->has('sns_driver')) {
            $query->where('sns_driver', $request->get('sns_driver'));
        }

        // Filter by users with SNS account
        if ($request->has('has_sns')) {
            if ($request->boolean('has_sns')) {
                $query->whereNotNull('sns_id');
            } else {
                $query->whereNull('sns_id');
            }
        }
    }
}
