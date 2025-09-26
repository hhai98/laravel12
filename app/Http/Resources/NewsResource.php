<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class NewsResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'created_by' => $this->created_by,
            
            // Include relationships conditionally
            ...$this->includeCreator(),
            ...$this->includeFiles(),
        ]);
    }
}
