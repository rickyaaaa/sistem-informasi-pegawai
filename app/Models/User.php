<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN  = 'super_admin';
    public const ROLE_ADMIN_SATKER = 'admin_satker';

    public const ROLE_DISPLAY_NAMES = [
        'super_admin'  => 'ADMIN POLDA',
        'admin_satker' => 'OPERATOR',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'satker_id',
        'status',
    ];

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
            'password'  => 'hashed',
            'satker_id' => 'integer',
        ];
    }

    // ── Role helpers ────────────────────────────────────────────

    public function getRoleDisplayAttribute(): string
    {
        return self::ROLE_DISPLAY_NAMES[$this->role] ?? $this->role;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdminSatker(): bool
    {
        return $this->role === self::ROLE_ADMIN_SATKER;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ── Relations ────────────────────────────────────────────────

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class);
    }

    /**
     * Relasi: User (Operator) mengajukan banyak Request Pegawai
     */
    public function requestedPegawaiRequests(): HasMany
    {
        return $this->hasMany(PegawaiRequest::class, 'requested_by');
    }

    /**
     * Relasi: User (Superadmin) menyetujui/menolak banyak Request Pegawai
     */
    public function approvedPegawaiRequests(): HasMany
    {
        return $this->hasMany(PegawaiRequest::class, 'approved_by');
    }
}
