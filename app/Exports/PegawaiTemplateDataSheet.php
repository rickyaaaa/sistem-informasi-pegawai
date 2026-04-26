<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PegawaiTemplateDataSheet implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'DATA IMPORT';
    }

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
            'status_k2',
            'nomor_k2',
            'ket',
        ];
    }

    public function array(): array
    {
        return [
            // Hanya 1 baris contoh — hapus ini sebelum import
            [
                "'3201234567890001", // Apostrof di depan memaksa Excel simpan sebagai teks
                'Contoh Nama Pegawai',
                '15/08/1990',
                'Pria',
                'S1',
                'Teknik Informatika',
                '01/03/2020',
                'ITWASDA',
                'SUBBAGRENMIN',
                'aktif',
                'K-II',
                'REG123456',
                'Kontrak tahun ke-3',
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            // Format kolom NIK sebagai teks agar tidak berubah ke scientific notation
            'A' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Kunci: set explicit text format untuk seluruh kolom A (NIK)
        $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Warna header (baris 1)
        $sheet->getStyle('1:1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('B91C1C');

        $sheet->getStyle('1:1')->getFont()
            ->setBold(true)
            ->setColor((new \PhpOffice\PhpSpreadsheet\Style\Color())->setRGB('FFFFFF'));

        // Warna baris contoh (baris 2) — kuning sebagai penanda "hapus sebelum import"
        $sheet->getStyle('2:2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FEF9C3');

        return [];
    }
}
