<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'size',
        'fileable_id',
        'fileable_type',
    ];

    /**
     * Get the owning fileable model.
     */
    public function fileable()
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL for the file.
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
}
