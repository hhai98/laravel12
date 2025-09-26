<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Customize the response for a request.
     */
    public function withResponse(Request $request, $response): void
    {
        // Add custom headers or modify response
    }

    /**
     * Include relationship data conditionally.
     */
    protected function includeRelationship(string $relationship, string $resourceClass = null): array
    {
        if (!$this->relationLoaded($relationship)) {
            return [];
        }

        $relation = $this->$relationship;
        
        if ($relation === null) {
            return [$relationship => null];
        }

        if ($resourceClass) {
            if ($relation instanceof \Illuminate\Database\Eloquent\Collection) {
                return [$relationship => $resourceClass::collection($relation)];
            }
            return [$relationship => new $resourceClass($relation)];
        }

        return [$relationship => $relation];
    }

    /**
     * Include files/images relationship.
     */
    protected function includeFiles(): array
    {
        return $this->includeRelationship('files', FileResource::class);
    }

    /**
     * Include user relationship.
     */
    protected function includeUser(): array
    {
        return $this->includeRelationship('user', UserResource::class);
    }

    /**
     * Include creator relationship.
     */
    protected function includeCreator(): array
    {
        return $this->includeRelationship('creator', UserResource::class);
    }

    /**
     * Include role relationship.
     */
    protected function includeRole(): array
    {
        return $this->includeRelationship('role', RoleResource::class);
    }

    /**
     * Format date for output.
     */
    protected function formatDate($date): ?string
    {
        return $date?->format('Y-m-d H:i:s');
    }

    /**
     * Format date for ISO output.
     */
    protected function formatDateISO($date): ?string
    {
        return $date?->toISOString();
    }

    /**
     * Get boolean value as integer.
     */
    protected function boolToInt($value): int
    {
        return $value ? 1 : 0;
    }

    /**
     * Get truncated text.
     */
    protected function truncateText(?string $text, int $length = 100): ?string
    {
        if (!$text) {
            return null;
        }

        return strlen($text) > $length 
            ? substr($text, 0, $length) . '...' 
            : $text;
    }

    /**
     * Get file URL with full path.
     */
    protected function getFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return url($path);
    }
}
