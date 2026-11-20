<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_COMPLETED = 'completed';

    // Label status yang ditampilkan kepada pengguna
    public const STATUSES = [
        self::STATUS_COLLECTED => 'Terkumpul',
        self::STATUS_SHIPPED => 'Diangkut',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    protected $fillable = [
        'deposit_number',
        'user_id',
        'drop_point_id',
        'pickup_id',
        'deposit_date',
        'total_weight',
        'notes',
        'status',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'total_weight' => 'decimal:2',
    ];

    /**
     * Setoran milik satu user (penyetor).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Setoran diterima di satu drop point.
     */
    public function dropPoint(): BelongsTo
    {
        return $this->belongsTo(DropPoint::class);
    }

    /**
     * Setoran dapat masuk ke satu pengangkutan (null jika belum diangkut).
     */
    public function pickup(): BelongsTo
    {
        return $this->belongsTo(Pickup::class);
    }

    /**
     * Satu setoran berisi banyak detail barang.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DepositItem::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Menghitung ulang total berat dari seluruh detail barang.
     */
    public function recalculateTotalWeight(): void
    {
        $this->update(['total_weight' => $this->items()->sum('weight')]);
    }

    /**
     * Membuat nomor transaksi, contoh: DEP-20261119-0001.
     */
    public static function generateNumber(string $date): string
    {
        $prefix = 'DEP-' . date('Ymd', strtotime($date)) . '-';
        $count = self::where('deposit_number', 'like', $prefix . '%')->count();

        return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
