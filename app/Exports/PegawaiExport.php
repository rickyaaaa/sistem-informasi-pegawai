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

class PegawaiExport implements FromQuery, WithHeadings, WithMapping
{
    protected User $user;
    protected array $filters;
    private int $rowNumber = 0;

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
            'NO',
            'NAMA',
            'JENIS KELAMIN',
            'PENDIDIKAN',
            'PRODI',
            'UMUR',
            'SATKER/SATWIL',
            'SUB/BAG',
        ];
    }

    public function map($pegawai): array
    {
        $this->rowNumber++;
        $umur = $pegawai->tgl_lahir ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->age . ' Tahun' : '-';
        $satkerInduk = $pegawai->satker?->level === 'sub' ? strtoupper($pegawai->satker?->parent?->nama_satker ?? '-') : strtoupper($pegawai->satker?->nama_satker ?? '-');
        $subBag = $pegawai->satker?->level === 'sub' ? strtoupper($pegawai->satker?->nama_satker ?? '-') : '-';

        return [
            $this->rowNumber,
            $pegawai->nama,
            $pegawai->jenis_kelamin ?? '-',
            $pegawai->pendidikan ?? '-',
            $pegawai->prodi->nama ?? '-',
            $umur,
            $satkerInduk,
            $subBag,
        ];
    }
}
