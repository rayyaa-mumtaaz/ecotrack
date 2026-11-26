<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pickup extends Model
{
    protected $fillable = [
        'pickup_number',
        'pickup_date',
        'partner_id',
        'total_weight',
        'notes',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'total_weight' => 'decimal:2',
    ];

    /**
     * Pengangkutan dikirim ke satu mitra.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Satu pengangkutan memuat banyak setoran.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Pengangkutan dianggap selesai jika semua setorannya berstatus Selesai
     * (sudah diserahkan ke mitra). Status disimpan di setoran, bukan di pickup.
     */
    public function isCompleted(): bool
    {
        return $this->deposits()->exists()
            && $this->deposits()->where('status', '!=', Deposit::STATUS_COMPLETED)->doesntExist();
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
