<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

abstract class BaseController extends Controller
{
    /**
     * The model instance.
     */
    protected Model $model;

    /**
     * The resource class for transforming the model.
     */
    protected string $resource;

    /**
     * The form request class for validation.
     */
    protected string $storeRequest;

    /**
     * The form request class for update validation.
     */
    protected string $updateRequest;

    /**
     * Number of items per page for pagination.
     */
    protected int $perPage = 15;

    /**
     * Relations to eager load.
     */
    protected array $with = [];

    /**
     * Searchable fields for filtering.
     */
    protected array $searchable = [];

    /**
     * Sortable fields.
     */
    protected array $sortable = ['id', 'created_at', 'updated_at'];

    public function __construct()
    {
        $this->initializeModel();
    }

    /**
     * Initialize the model instance.
     */
    protected function initializeModel(): void
    {
        if (!isset($this->modelClass)) {
            throw new \Exception('Model class not defined in ' . static::class);
        }

        $this->model = app($this->modelClass);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|ResourceCollection
    {
        $query = $this->model->newQuery();

        // Eager load relationships
        if (!empty($this->with)) {
            $query->with($this->with);
        }

        // Apply search filters
        $this->applySearch($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        // Apply additional filters
        $this->applyFilters($query, $request);

        $items = $query->paginate($this->getPerPage($request));

        return $this->resourceCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(?FormRequest $request = null): JsonResponse|JsonResource
    {
        $request = $request ?: $this->resolveFormRequest($this->storeRequest ?? null);
        
        $validated = $request->validated();
        
        // Apply any transformations before storing
        $data = $this->beforeStore($validated, $request);
        
        $item = $this->model->create($data);
        
        // Apply any post-creation logic
        $this->afterStore($item, $data, $request);
        
        // Load relationships if needed
        if (!empty($this->with)) {
            $item->load($this->with);
        }
        
        return $this->resourceResponse($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse|JsonResource
    {
        $item = $this->findModel($id);
        
        return $this->resourceResponse($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, ?FormRequest $request = null): JsonResponse|JsonResource
    {
        $request = $request ?: $this->resolveFormRequest($this->updateRequest ?? $this->storeRequest ?? null);
        
        $item = $this->findModel($id);
        $validated = $request->validated();
        
        // Apply any transformations before updating
        $data = $this->beforeUpdate($validated, $request, $item);
        
        $item->update($data);
        
        // Apply any post-update logic
        $this->afterUpdate($item, $data, $request);
        
        // Refresh and load relationships if needed
        $item->refresh();
        if (!empty($this->with)) {
            $item->load($this->with);
        }
        
        return $this->resourceResponse($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $item = $this->findModel($id);
        
        // Apply any pre-deletion logic
        $this->beforeDestroy($item);
        
        $item->delete();
        
        // Apply any post-deletion logic
        $this->afterDestroy($item);
        
        return response()->json([
            'message' => class_basename($this->model) . ' deleted successfully'
        ]);
    }

    /**
     * Restore a soft-deleted resource.
     */
    public function restore($id): JsonResponse|JsonResource
    {
        if (!method_exists($this->model, 'restore')) {
            return response()->json([
                'message' => 'Model does not support soft deletes'
            ], 400);
        }

        $item = $this->model->withTrashed()->findOrFail($id);
        $item->restore();
        
        return $this->resourceResponse($item);
    }

    /**
     * Permanently delete a soft-deleted resource.
     */
    public function forceDelete($id): JsonResponse
    {
        if (!method_exists($this->model, 'forceDelete')) {
            return response()->json([
                'message' => 'Model does not support soft deletes'
            ], 400);
        }

        $item = $this->model->withTrashed()->findOrFail($id);
        $item->forceDelete();
        
        return response()->json([
            'message' => class_basename($this->model) . ' permanently deleted'
        ]);
    }

    /**
     * Find model by ID.
     */
    protected function findModel($id): Model
    {
        $query = $this->model->newQuery();
        
        if (!empty($this->with)) {
            $query->with($this->with);
        }
        
        return $query->findOrFail($id);
    }

    /**
     * Apply search filters to query.
     */
    protected function applySearch($query, Request $request): void
    {
        $search = $request->get('search');
        
        if ($search && !empty($this->searchable)) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $key => $config) {
                    $this->applyAdvancedSearch($q, $key, $config, $search);
                }
            });
        }
    }

    /**
     * Apply advanced search with conditions.
     */
    protected function applyAdvancedSearch($query, string $key, array $config, $search): void
    {
        $column = $config['column'] ?? $key;
        $condition = $config['condition'] ?? 'equal';
        $relation = $config['relation'] ?? null;

        if ($relation) {
            // Handle relationship searches
            $query->orWhereHas($relation, function ($relationQuery) use ($column, $condition, $search) {
                $this->applySearchCondition($relationQuery, $column, $condition, $search);
            });
        } else {
            // Handle direct column searches
            $this->applySearchCondition($query, $column, $condition, $search, 'or');
        }
    }

    /**
     * Apply search condition based on type.
     */
    protected function applySearchCondition($query, string $column, string $condition, $search, string $boolean = 'and'): void
    {
        $method = $boolean === 'or' ? 'orWhere' : 'where';

        switch ($condition) {
            case 'equal':
                $query->$method($column, $search);
                break;

            case 'like%':
                $query->$method($column, 'like', $search . '%');
                break;

            case '%like':
                $query->$method($column, 'like', '%' . $search);
                break;

            case '%like%':
            case 'like':
                $query->$method($column, 'like', '%' . $search . '%');
                break;

            case 'in':
                $values = is_array($search) ? $search : explode(',', $search);
                $query->whereIn($column, $values);
                break;

            case 'not_in':
                $values = is_array($search) ? $search : explode(',', $search);
                $query->whereNotIn($column, $values);
                break;

            case 'greater':
            case '>':
                $query->$method($column, '>', $search);
                break;

            case 'greater_equal':
            case '>=':
                $query->$method($column, '>=', $search);
                break;

            case 'less':
            case '<':
                $query->$method($column, '<', $search);
                break;

            case 'less_equal':
            case '<=':
                $query->$method($column, '<=', $search);
                break;

            case 'not_equal':
            case '!=':
                $query->$method($column, '!=', $search);
                break;

            case 'null':
                $query->whereNull($column);
                break;

            case 'not_null':
                $query->whereNotNull($column);
                break;

            case 'between':
                if (is_array($search) && count($search) === 2) {
                    $query->whereBetween($column, $search);
                }
                break;

            case 'not_between':
                if (is_array($search) && count($search) === 2) {
                    $query->whereNotBetween($column, $search);
                }
                break;

            case 'date':
                $query->$method($column, '=', date('Y-m-d', strtotime($search)));
                break;

            case 'date_range':
                if (is_array($search) && count($search) === 2) {
                    $query->whereBetween($column, [
                        date('Y-m-d', strtotime($search[0])),
                        date('Y-m-d', strtotime($search[1]))
                    ]);
                }
                break;

            default:
                // Default to like search
                $query->$method($column, 'like', '%' . $search . '%');
                break;
        }
    }

    /**
     * Apply sorting to query.
     */
    protected function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortBy, $this->sortable)) {
            $query->orderBy($sortBy, $sortDirection);
        }
    }

    /**
     * Apply additional filters to query.
     * Override this method in child controllers for custom filtering.
     */
    protected function applyFilters($query, Request $request): void
    {
        // Override in child controllers
    }

    /**
     * Get per page limit from request.
     */
    protected function getPerPage(Request $request): int
    {
        $perPage = $request->get('per_page', $this->perPage);
        return min($perPage, 100); // Max 100 items per page
    }

    /**
     * Create resource response.
     */
    protected function resourceResponse($item, int $status = 200): JsonResponse|JsonResource
    {
        if (isset($this->resource)) {
            return new $this->resource($item);
        }
        
        return response()->json($item, $status);
    }

    /**
     * Create resource collection response.
     */
    protected function resourceCollection($items): JsonResponse|ResourceCollection
    {
        if (isset($this->resource)) {
            return $this->resource::collection($items);
        }
        
        return response()->json($items);
    }

    /**
     * Resolve form request instance.
     */
    protected function resolveFormRequest(?string $requestClass): FormRequest
    {
        if (!$requestClass) {
            // Return a basic form request that accepts all input
            return new class extends FormRequest {
                public function authorize(): bool
                {
                    return true;
                }
                
                public function rules(): array
                {
                    return [];
                }
            };
        }
        
        return app($requestClass);
    }

    /**
     * Hook: Before storing data.
     */
    protected function beforeStore(array $data, FormRequest $request): array
    {
        return $data;
    }

    /**
     * Hook: After storing data.
     */
    protected function afterStore(Model $item, array $data, FormRequest $request): void
    {
        // Override in child controllers
    }

    /**
     * Hook: Before updating data.
     */
    protected function beforeUpdate(array $data, FormRequest $request, Model $item): array
    {
        return $data;
    }

    /**
     * Hook: After updating data.
     */
    protected function afterUpdate(Model $item, array $data, FormRequest $request): void
    {
        // Override in child controllers
    }

    /**
     * Hook: Before destroying item.
     */
    protected function beforeDestroy(Model $item): void
    {
        // Override in child controllers
    }

    /**
     * Hook: After destroying item.
     */
    protected function afterDestroy(Model $item): void
    {
        // Override in child controllers
    }
}
