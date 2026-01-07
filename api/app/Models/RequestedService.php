<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestedService extends Model
{
    protected $fillable = [
        'client_id',
        'service_id',
        'date',
        'time',
        'observations',
        'location',
        'reference_image',
        'status',
        'validity_status',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'service_request_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the reference image for the requested service.
     */
    public function image()
    {
        return $this->morphOne(File::class, 'fileable');
    }
}
