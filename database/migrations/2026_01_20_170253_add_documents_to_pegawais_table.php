<?php

namespace App\Exports;

use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PegawaiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        $user = Auth::user();

        $query = Pegawai::query()
            ->with('satker')
            ->orderBy('nama');

        // Admin satker hanya export satkernya
        if ($user->isAdminSatker()) {
            $query->where('satker_id', $user->satker_id);
        }

        return $query->get()->map(function ($pegawai) {
            return [
                'Nama'       => $pegawai->nama,
                'NIK'        => $pegawai->nik,
                'Pendidikan' => $pegawai->pendidikan,
                'Satker'     => $pegawai->satker?->nama_satker ?? '-',
                'Status'     => $pegawai->status === 'aktif' ? 'Aktif' : 'Non Aktif',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIK',
            'Pendidikan',
            'Satker',
            'Status',
        ];
    }
}
