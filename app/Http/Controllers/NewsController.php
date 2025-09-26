<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends BaseController
{
    /**
     * The model class.
     */
    protected string $modelClass = News::class;

    /**
     * The resource class for transforming the model.
     */
    protected string $resource = NewsResource::class;

    /**
     * The form request class for validation.
     */
    protected string $storeRequest = NewsRequest::class;

    /**
     * The form request class for update validation.
     */
    protected string $updateRequest = NewsRequest::class;

    /**
     * Relations to eager load.
     */
    protected array $with = ['creator', 'files'];

    /**
     * Searchable fields for filtering - Demo of all condition types.
     */
    protected array $searchable = [
        'title' => [
            'column' => 'title',
            'condition' => '%like%',
        ],
        'description' => [
            'column' => 'description',
            'condition' => '%like%',
        ],
        'content' => [
            'column' => 'content',
            'condition' => '%like%',
        ],
        'creator_name' => [
            'column' => 'name',
            'condition' => '%like%',
            'relation' => 'creator',
        ],
        'creator_phone' => [
            'column' => 'phone',
            'condition' => 'like%',
            'relation' => 'creator',
        ],
        'status' => [
            'column' => 'status',
            'condition' => 'equal',
        ],
        'created_by' => [
            'column' => 'created_by',
            'condition' => 'in', // Support multiple creator IDs
        ],
    ];

    /**
     * Sortable fields.
     */
    protected array $sortable = [
        'title' => [
            'column' => 'title',
            'condition' => '%like%',
        ],
        'created_at' => [
            'column' => 'created_at',
            'condition' => 'equal',
        ],
        'creator_name' => [
            'column' => 'name',
            'condition' => '%like%',
            'relation' => 'creator',
        ],
    ];

    /**
     * Apply additional filters to query.
     */
    protected function applyFilters($query, Request $request): void
    {
        // Filter by creator
        if ($request->has('created_by')) {
            $createdBy = $request->get('created_by');
            if (is_array($createdBy)) {
                $query->whereIn('created_by', $createdBy);
            } else {
                $query->where('created_by', $createdBy);
            }
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('created_at', [
                $request->get('date_from'),
                $request->get('date_to')
            ]);
        }

        // Filter by published status (if you have a status field)
        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }
    }
}
