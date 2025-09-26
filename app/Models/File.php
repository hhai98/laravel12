<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'url',
        'thumb',
        'type',
        'fileable_id',
        'fileable_type',
    ];

    /**
     * Get the parent fileable model.
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include images.
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope a query to only include documents.
     */
    public function scopeDocuments($query)
    {
        return $query->where('type', 'document');
    }

    /**
     * Scope a query to only include videos.
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }
}
