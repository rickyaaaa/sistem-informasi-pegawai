<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    protected $fillable = [
        'nama',
        'kategori',
    ];

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeSmaSMK($query)
    {
        return $query->where('kategori', 'SMA/SMK');
    }

    public function scopePerguruanTinggi($query)
    {
        return $query->where('kategori', 'Perguruan Tinggi');
    }

    // ── Relations ───────────────────────────────────────────────

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'prodi_id');
    }
}
