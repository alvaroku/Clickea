<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'service_request_id',
        'provider_id',
        'price',
        'provider_observations',
        'status',
        'rating',
        'rating_comment'
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(RequestedService::class, 'service_request_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
