<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositItem extends Model
{
    public const CONDITION_WORKING = 'working';
    public const CONDITION_DAMAGED = 'damaged';
    public const CONDITION_BROKEN = 'broken';

    // Label kondisi barang yang ditampilkan kepada pengguna
    public const CONDITIONS = [
        self::CONDITION_WORKING => 'Masih berfungsi',
        self::CONDITION_DAMAGED => 'Rusak sebagian',
        self::CONDITION_BROKEN => 'Rusak total',
    ];

    protected $fillable = [
        'deposit_id',
        'category_id',
        'item_name',
        'quantity',
        'weight',
        'condition',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:2',
    ];

    /**
     * Detail barang milik satu transaksi setoran.
     */
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    /**
     * Detail barang termasuk dalam satu kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function conditionLabel(): string
    {
        return self::CONDITIONS[$this->condition] ?? $this->condition;
    }
}
