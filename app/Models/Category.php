<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Satu kategori dipakai oleh banyak detail barang setoran.
     */
    public function depositItems(): HasMany
    {
        return $this->hasMany(DepositItem::class);
    }
}
