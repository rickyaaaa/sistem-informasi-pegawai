<?php

namespace App\Exports;

use App\Models\Pegawai;
use App\Models\Satker;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PegawaiExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    protected User $user;
    protected array $filters;

    public function __construct(User $user, array $filters = [])
    {
        $this->user = $user;
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Pegawai::query()->with(['satker.parent', 'prodi']);

        if ($this->user->isAdminSatker()) {
            $subIds = Satker::where('parent_id', $this->user->satker_id)
                ->orWhere('id', $this->user->satker_id)
                ->pluck('id');
            $query->whereIn('satker_id', $subIds);
        }

        // Apply filters
        if (!empty($this->filters['q'])) {
            $q = $this->filters['q'];
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'LIKE', "%{$q}%")
                  ->orWhere('nik', 'LIKE', "%{$q}%");
            });
        }

        if (!empty($this->filters['satker_id_search'])) {
            $satkerId = (int) $this->filters['satker_id_search'];
            $allowedIds = Satker::where('id', $satkerId)
                                ->orWhere('parent_id', $satkerId)
                                ->pluck('id');
            $query->whereIn('satker_id', $allowedIds);
        }

        if (!empty($this->filters['pendidikan_search'])) {
            $pendList = (array) $this->filters['pendidikan_search'];
            $query->whereIn('pendidikan', $pendList);
        }

        return $query->orderBy('nama');
    }

    public function headings(): array
    {
        return [
            'NAMA',
            'TGL LAHIR',
            'JK',
            'PENDIDIKAN',
            'PRODI',
            'TGL KERJA',
            'SATKER',
            'UNIT KERJA',
            'STATUS',
            'STATUS K-II',
            'NOMOR K-II',
            'KET',
        ];
    }

    public function map($pegawai): array
    {
        return [
            $pegawai->nama,
            $pegawai->tgl_lahir ? $pegawai->tgl_lahir->format('d/m/Y') : '-',
            $pegawai->jenis_kelamin ?? '-',
            $pegawai->pendidikan ?? '-',
            $pegawai->prodi->nama ?? '-',
            $pegawai->tgl_kerja ? $pegawai->tgl_kerja->format('d/m/Y') : '-',
            optional($pegawai->satker)->parent->nama_satker ?? '-',
            $pegawai->satker->nama_satker ?? '-',
            $pegawai->status === 'aktif' ? 'Aktif' : 'Non Aktif',
            $pegawai->status_k2,
            $pegawai->nomor_k2 ?? '-',
            $pegawai->keterangan ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }
}
