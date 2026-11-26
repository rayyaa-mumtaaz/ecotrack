<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DropPoint extends Model
{
    // Titik tengah awal peta: area kampus, Kota Jember
    public const MAP_CENTER = [-8.1654, 113.7166];

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'description',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Satu drop point menerima banyak setoran.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Admin/petugas yang bertugas di drop point ini (users.drop_point_id).
     */
    public function officers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_ADMIN);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
