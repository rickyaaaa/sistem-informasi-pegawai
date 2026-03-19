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

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function query()
    {
        $query = Pegawai::query()->with(['satker.parent', 'prodi']);

        if ($this->user->isAdminSatker()) {
            $subIds = Satker::where('parent_id', $this->user->satker_id)
                ->pluck('id');
            $query->whereIn('satker_id', $subIds);
        }

        return $query->orderBy('nama');
    }

    public function headings(): array
    {
        return [
            'NIK',
            'NAMA',
            'TGL LAHIR',
            'JK',
            'PENDIDIKAN',
            'PRODI',
            'TGL KERJA',
            'SATKER',
            'UNIT KERJA',
            'STATUS',
            'KET',
        ];
    }

    public function map($pegawai): array
    {
        return [
            $pegawai->nik,
            $pegawai->nama,
            $pegawai->tgl_lahir ? $pegawai->tgl_lahir->format('d/m/Y') : '-',
            $pegawai->jenis_kelamin ?? '-',
            $pegawai->pendidikan ?? '-',
            $pegawai->prodi->nama ?? '-',
            $pegawai->tgl_kerja ? $pegawai->tgl_kerja->format('d/m/Y') : '-',
            optional($pegawai->satker)->parent->nama_satker ?? '-',
            $pegawai->satker->nama_satker ?? '-',
            $pegawai->status === 'aktif' ? 'Aktif' : 'Non Aktif',
            $pegawai->keterangan ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIK as text
        ];
    }
}
