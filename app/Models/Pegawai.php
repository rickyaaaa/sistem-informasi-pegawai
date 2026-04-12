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
        'tgl_lahir',
        'jenis_kelamin',
        'foto',
        'pendidikan',
        'prodi_id',
        'tgl_kerja',
        'satker_id',
        'status',
        'keterangan',
        'file_ktp',
        'file_kk',
        'file_ijazah',
    ];

    protected function casts(): array
    {
        return [
            'satker_id' => 'integer',
            'prodi_id'  => 'integer',
            'tgl_lahir' => 'date',
            'tgl_kerja' => 'date',
        ];
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class);
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function pegawaiRequests(): HasMany
    {
        return $this->hasMany(PegawaiRequest::class);
    }
}
