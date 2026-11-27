<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    public const TYPE_RECYCLER = 'recycler';
    public const TYPE_CRAFTSMAN = 'craftsman';
    public const TYPE_COLLECTOR = 'collector';
    public const TYPE_REPAIR = 'repair';
    public const TYPE_OTHER = 'other';

    // Label tipe mitra yang ditampilkan kepada pengguna
    public const TYPES = [
        self::TYPE_RECYCLER => 'Pendaur ulang',
        self::TYPE_CRAFTSMAN => 'Pengrajin',
        self::TYPE_COLLECTOR => 'Pengepul',
        self::TYPE_REPAIR => 'Servis / Reparasi',
        self::TYPE_OTHER => 'Lainnya',
    ];

    protected $fillable = [
        'name',
        'type',
        'contact_person',
        'phone',
        'email',
        'address',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Satu mitra menerima banyak pengangkutan.
     */
    public function pickups(): HasMany
    {
        return $this->hasMany(Pickup::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
