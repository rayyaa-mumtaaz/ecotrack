<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'drop_point_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Nilai role disimpan di sini agar tidak ditulis berulang di banyak file
    public const ROLE_SUPERADMIN = 'superadmin'; // koordinator: kelola akun petugas, master data, semua drop point
    public const ROLE_ADMIN = 'admin';           // petugas: hanya drop point tempat bertugas
    public const ROLE_USER = 'user';             // penyetor

    public const ROLES = [
        self::ROLE_SUPERADMIN => 'Superadmin',
        self::ROLE_ADMIN => 'Petugas',
        self::ROLE_USER => 'Penyetor',
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
        ];
    }

    /**
     * Satu user (penyetor) memiliki banyak setoran.
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Drop point tempat admin/petugas bertugas (kosong untuk penyetor).
     */
    public function dropPoint(): BelongsTo
    {
        return $this->belongsTo(DropPoint::class);
    }

    /**
     * Setoran yang diterima oleh admin/petugas ini (deposits.received_by).
     */
    public function receivedDeposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'received_by');
    }

    public function isSuperadmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /**
     * Petugas drop point (role admin).
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Superadmin atau petugas: boleh masuk panel /admin.
     */
    public function isStaff(): bool
    {
        return $this->isSuperadmin() || $this->isAdmin();
    }

    /**
     * Petugas hanya boleh mengelola data drop point tempat ia bertugas.
     * Superadmin tidak dibatasi.
     */
    public function isLimitedToDropPoint(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Apakah user boleh mengakses data milik drop point tertentu.
     */
    public function canAccessDropPoint(?int $dropPointId): bool
    {
        if (! $this->isLimitedToDropPoint()) {
            return true;
        }

        return $this->drop_point_id !== null && $this->drop_point_id === $dropPointId;
    }
}
