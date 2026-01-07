<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'active',
        'profile_picture_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function profilePicture()
    {
        return $this->belongsTo(File::class, 'profile_picture_id');
    }

    public function requestsMade()
    {
        return $this->hasMany(RequestedService::class, 'client_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'provider_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }
}
