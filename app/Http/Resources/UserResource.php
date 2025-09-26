<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'phone' => $this->phone,
            'sns_id' => $this->sns_id,
            'sns_driver' => $this->sns_driver,
            'name' => $this->name,
            'role_id' => $this->role_id,
            
            // Include relationships conditionally
            ...$this->includeRole(),
            ...$this->when($this->relationLoaded('shop'), [
                'shop' => new ShopResource($this->whenLoaded('shop'))
            ]),
        ]);
    }
}
