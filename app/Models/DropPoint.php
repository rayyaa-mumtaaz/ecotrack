<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DropPoint extends Model
{
    protected $fillable = [
        'name',
        'address',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Satu drop point menerima banyak setoran.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }
}
