<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FileResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'url' => $this->getFileUrl($this->url),
            'thumb' => $this->getFileUrl($this->thumb),
            'type' => $this->type,
            'fileable_id' => $this->fileable_id,
            'fileable_type' => $this->fileable_type,
        ]);
    }
}
