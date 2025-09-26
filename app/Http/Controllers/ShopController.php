<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends BaseController
{
    /**
     * The model class.
     */
    protected string $modelClass = Shop::class;

    /**
     * The resource class for transforming the model.
     */
    protected string $resource = ShopResource::class;

    /**
     * The form request class for validation.
     */
    protected string $storeRequest = ShopRequest::class;

    /**
     * The form request class for update validation.
     */
    protected string $updateRequest = ShopRequest::class;

    /**
     * Relations to eager load.
     */
    protected array $with = ['user', 'files'];

    /**
     * Searchable fields for filtering.
     */
    protected array $searchable = [
        'name' => [
            'column' => 'name',
            'condition' => '%like%',
        ],
        'address' => [
            'column' => 'address',
            'condition' => '%like%',
        ],
        'phone' => [
            'column' => 'phone',
            'condition' => 'like%',
        ],
        'user_name' => [
            'column' => 'name',
            'condition' => '%like%',
            'relation' => 'user',
        ],
        'description' => [
            'column' => 'description',
            'condition' => '%like%',
        ],
    ];

    /**
     * Sortable fields.
     */
    protected array $sortable = ['id', 'name', 'created_at', 'updated_at'];

    /**
     * Apply additional filters to query.
     */
    protected function applyFilters($query, Request $request): void
    {
        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filter by location (if geo search is needed)
        if ($request->has('lat') && $request->has('lng') && $request->has('radius')) {
            $lat = $request->get('lat');
            $lng = $request->get('lng');
            $radius = $request->get('radius', 10); // Default 10km radius

            // This is a basic distance calculation - you might want to use a proper geo search
            $query->whereRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(ST_Y(geo))) * 
                cos(radians(ST_X(geo)) - radians(?)) + sin(radians(?)) * 
                sin(radians(ST_Y(geo))))) <= ?
            ", [$lat, $lng, $lat, $radius]);
        }
    }
}
