<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'price',
        'gender',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the service.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requests()
    {
        return $this->hasMany(RequestedService::class);
    }

    /**
     * Get all of the service's images.
     */
    public function images()
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
