<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
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
}
