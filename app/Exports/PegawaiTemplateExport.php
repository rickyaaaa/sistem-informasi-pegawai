<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PegawaiTemplateExport implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'nik',
            'nama',
            'tgl_lahir',
            'jk',
            'pendidikan',
            'prodi',
            'tgl_kerja',
            'satker',
            'unit_kerja',
            'status',
            'ket',
        ];
    }

    public function array(): array
    {
        return [
            [
                '3201234567890001',
                'Contoh Nama Pegawai',
                '15/08/1990',
                'Laki-laki',
                'S1',
                'Teknik Informatika',
                '01/03/2020',
                'ITWASDA',
                'SUBBAGRENMIN',
                'aktif',
                'Kontrak tahun ke-3',
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // NIK as text
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->setCellValue('A4', 'PETUNJUK PENGISIAN:');
        $sheet->setCellValue('A5', '- nik: NIK 16 digit (format teks, angka 0 di depan tidak hilang)');
        $sheet->setCellValue('A6', '- nama: Nama lengkap pegawai');
        $sheet->setCellValue('A7', '- tgl_lahir: Format DD/MM/YYYY (contoh: 15/08/1990)');
        $sheet->setCellValue('A8', '- jk: "Laki-laki" atau "Perempuan"');
        $sheet->setCellValue('A9', '- pendidikan: SD, SMP, SMA/SMK, D3, S1, S1 Profesi, S2, atau S2 Profesi');
        $sheet->setCellValue('A10', '- prodi: Nama jurusan sesuai daftar di sistem (contoh: Teknik Informatika). Isi "-" jika SD/SMP');
        $sheet->setCellValue('A11', '- tgl_kerja: Tanggal mulai kerja, format DD/MM/YYYY');
        $sheet->setCellValue('A12', '- satker: Nama INDUK Satker/Satwil (contoh: ITWASDA, POLRES PESISIR BARAT)');
        $sheet->setCellValue('A13', '- unit_kerja: Nama Sub-Bagian / unit kerja yang terdaftar di sistem');
        $sheet->setCellValue('A14', '- status: "aktif" atau "non_aktif"');
        $sheet->setCellValue('A15', '- ket: Keterangan opsional');
        $sheet->setCellValue('A16', '');
        $sheet->setCellValue('A17', '⚠ Hapus baris contoh (baris 2) dan petunjuk ini sebelum import!');

        return [
            1  => ['font' => ['bold' => true, 'size' => 11]],
            4  => ['font' => ['bold' => true, 'color' => ['rgb' => 'CC0000']]],
            17 => ['font' => ['bold' => true, 'color' => ['rgb' => 'CC0000']]],
        ];
    }
}
