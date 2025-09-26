<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class RoleResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'code' => $this->code,
            'name' => $this->name,
        ]);
    }
}
