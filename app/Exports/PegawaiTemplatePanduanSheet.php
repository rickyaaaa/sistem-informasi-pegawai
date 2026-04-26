<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PegawaiTemplatePanduanSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'PANDUAN';
    }

    public function array(): array
    {
        return [
            ['PANDUAN PENGISIAN TEMPLATE IMPORT PEGAWAI'],
            [''],
            ['Kolom', 'Keterangan', 'Contoh'],
            ['nik', 'NIK 16 digit — WAJIB format TEXT di Excel (klik kanan kolom → Format Cells → Text)', '3201234567890001'],
            ['nama', 'Nama lengkap pegawai', 'Budi Santoso'],
            ['tgl_lahir', 'Tanggal lahir format DD/MM/YYYY', '15/08/1990'],
            ['jk', '"Pria" atau "Wanita"', 'Pria'],
            ['pendidikan', 'SD / SMP / SMA/SMK / D3 / S1 / S1 Profesi / S2 / S2 Profesi / S3', 'S1'],
            ['prodi', 'Nama jurusan sesuai daftar sistem. Isi "-" untuk SD/SMP', 'Teknik Informatika'],
            ['tgl_kerja', 'Tanggal mulai kerja format DD/MM/YYYY', '01/03/2020'],
            ['satker', 'Nama INDUK Satker/Satwil (UPPERCASE)', 'ITWASDA'],
            ['unit_kerja', 'Nama Sub-Bagian / unit kerja (UPPERCASE)', 'SUBBAGRENMIN'],
            ['status', '"aktif" atau "non_aktif"', 'aktif'],
            ['status_k2', '"K-II" atau "Non K-II"', 'K-II'],
            ['nomor_k2', 'Nomor registrasi (wajib jika status_k2 = K-II)', 'REG123456'],
            ['ket', 'Keterangan opsional', 'Kontrak tahun ke-3'],
            [''],
            ['⚠ PENTING:', '', ''],
            ['1. Gunakan sheet "DATA IMPORT" untuk mengisi data. JANGAN tambahkan baris atau kolom di luar template.', '', ''],
            ['2. Kolom NIK WAJIB diformat sebagai TEXT. Pilih seluruh kolom A → klik kanan → Format Cells → Text.', '', ''],
            ['3. Hapus baris contoh (baris ke-2 di sheet DATA IMPORT) sebelum diimport.', '', ''],
            ['4. Simpan file dalam format .xlsx (BUKAN .csv) untuk menghindari masalah format angka.', '', ''],
            ['5. Nama unit_kerja harus persis sama dengan yang terdaftar di sistem (case-insensitive).', '', ''],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Judul besar
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->mergeCells('A1:C1');

        // Header tabel kolom
        $sheet->getStyle('A3:C3')->getFont()->setBold(true);
        $sheet->getStyle('A3:C3')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E5E7EB');

        // Bagian PENTING
        $sheet->getStyle('A18')->getFont()->setBold(true)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color())->setRGB('B91C1C')
        );

        return [];
    }
}
