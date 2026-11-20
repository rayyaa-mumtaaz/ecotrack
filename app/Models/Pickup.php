<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pickup extends Model
{
    protected $fillable = [
        'pickup_number',
        'pickup_date',
        'partner_name',
        'total_weight',
        'notes',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'total_weight' => 'decimal:2',
    ];

    /**
     * Satu pengangkutan memuat banyak setoran.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Menghitung ulang total berat dari setoran yang diangkut.
     */
    public function recalculateTotalWeight(): void
    {
        $this->update(['total_weight' => $this->deposits()->sum('total_weight')]);
    }

    /**
     * Membuat nomor pengangkutan, contoh: PCK-20261119-0001.
     */
    public static function generateNumber(string $date): string
    {
        $prefix = 'PCK-' . date('Ymd', strtotime($date)) . '-';
        $count = self::where('pickup_number', 'like', $prefix . '%')->count();

        return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
