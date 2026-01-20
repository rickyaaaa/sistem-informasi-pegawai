<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $fillable = [
        'nama',
        'nik',
        'pendidikan',
        'satker_id',
        'status',
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
}
