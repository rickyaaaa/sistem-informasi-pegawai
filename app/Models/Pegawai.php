<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pegawai extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'nik',
        'jenis_kelamin',
        'foto',
        'pendidikan',
        'satker_id',
        'status',
        'file_ktp',
        'file_kk',
    ];

    protected function casts(): array
    {
        return [
            'satker_id' => 'integer',
        ];
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class);
    }

    public function pegawaiRequests(): HasMany
    {
        return $this->hasMany(PegawaiRequest::class);
    }
}

