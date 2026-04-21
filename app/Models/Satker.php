<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satker extends Model
{
    protected $fillable = [
        'nama_satker',
        'parent_id',
        'tipe_satuan',
        'level',
    ];

    protected static function booted()
    {
        static::deleting(function ($satker) {
            // Hapus request yang terkait
            $satker->pegawaiRequests()->delete();
            
            // Hapus user / operator satker
            $satker->users()->delete();

            // Hapus pegawai secara permanen (karena satkers di-hard delete)
            $satker->pegawais()->forceDelete();

            // Cascade ke sub-unit (memicu event deleting pada anaknya juga)
            foreach ($satker->children as $child) {
                $child->delete();
            }
        });
    }

    // ── Hierarchy Relations ─────────────────────────────────────

    /**
     * Relasi: Satker induk (parent)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Satker::class, 'parent_id');
    }

    /**
     * Relasi: Sub-unit (children)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Satker::class, 'parent_id');
    }

    // ── Existing Relations ──────────────────────────────────────

    /**
     * Relasi: 1 Satker memiliki banyak Pegawai
     */
    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'satker_id');
    }

    /**
     * Relasi: 1 Satker memiliki banyak User (Admin/Operator)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'satker_id');
    }

    /**
     * Relasi: 1 Satker memanage banyak Request/Approval Pegawai
     */
    public function pegawaiRequests(): HasMany
    {
        return $this->hasMany(PegawaiRequest::class, 'satker_id');
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeInduk($query)
    {
        return $query->where('level', 'induk');
    }

    public function scopeSub($query)
    {
        return $query->where('level', 'sub');
    }
}
