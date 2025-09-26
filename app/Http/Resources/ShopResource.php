<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ShopResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'geo' => $this->geo,
            'description' => $this->description,
            
            // Include relationships conditionally
            ...$this->includeUser(),
            ...$this->includeFiles(),
        ]);
    }
}
