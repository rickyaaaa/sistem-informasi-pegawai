<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PegawaiRequest extends Model
{
    protected $table = 'pegawai_requests';

    protected $fillable = [
        'pegawai_id',
        'satker_id',
        'requested_by',
        'action_type',
        'data_payload',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'data_payload' => 'array',
            'approved_at'  => 'datetime',
            'pegawai_id'   => 'integer',
            'satker_id'    => 'integer',
            'requested_by' => 'integer',
            'approved_by'  => 'integer',
        ];
    }

    // ── Relations ──────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class)->withTrashed();
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function actionLabel(): string
    {
        return match ($this->action_type) {
            'create' => 'Tambah',
            'update' => 'Ubah',
            'delete' => 'Hapus',
            default  => ucfirst($this->action_type),
        };
    }
}
