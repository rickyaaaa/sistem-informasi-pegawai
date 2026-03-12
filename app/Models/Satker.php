<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satker extends Model
{
    protected $fillable = [
        'nama_satker',
    ];

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
}
