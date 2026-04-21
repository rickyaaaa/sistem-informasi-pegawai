<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data and re-seed
        DB::table('prodis')->truncate();

        $data = [
            // ── UMUM (untuk SD/SMP) ─────────────────────────────────
            ['nama' => 'Tanpa Jurusan',    'kategori' => 'Umum'],

            // ── SMA/SMK ──────────────────────────────────────────────
            // SMA
            ['nama' => 'IPA',                          'kategori' => 'SMA/SMK'],
            ['nama' => 'IPS',                          'kategori' => 'SMA/SMK'],
            ['nama' => 'Bahasa',                       'kategori' => 'SMA/SMK'],
            // SMK — Bisnis & Manajemen
            ['nama' => 'Administrasi Perkantoran',     'kategori' => 'SMA/SMK'],
            ['nama' => 'Akuntansi SMK',                'kategori' => 'SMA/SMK'],
            ['nama' => 'Perbankan Syariah',            'kategori' => 'SMA/SMK'],
            ['nama' => 'Pemasaran',                    'kategori' => 'SMA/SMK'],
            // SMK — Teknologi & Rekayasa
            ['nama' => 'Teknik Otomotif',              'kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Listrik',               'kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Elektronika',           'kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Mesin SMK',             'kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Bangunan',              'kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Pendingin & Tata Udara','kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Kendaraan Ringan',      'kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Pemesinan',             'kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Las',                   'kategori' => 'SMA/SMK'],
            ['nama' => 'Teknik Gambar Bangunan',       'kategori' => 'SMA/SMK'],
            ['nama' => 'Otomasi Industri',             'kategori' => 'SMA/SMK'],
            // SMK — TIK
            ['nama' => 'Teknik Komputer dan Jaringan', 'kategori' => 'SMA/SMK'],
            ['nama' => 'Rekayasa Perangkat Lunak',     'kategori' => 'SMA/SMK'],
            ['nama' => 'Multimedia',                   'kategori' => 'SMA/SMK'],
            ['nama' => 'Animasi',                      'kategori' => 'SMA/SMK'],
            // SMK — Pariwisata & Seni
            ['nama' => 'Tata Boga',                    'kategori' => 'SMA/SMK'],
            ['nama' => 'Tata Busana',                  'kategori' => 'SMA/SMK'],
            ['nama' => 'Perhotelan',                   'kategori' => 'SMA/SMK'],
            ['nama' => 'Usaha Perjalanan Wisata',      'kategori' => 'SMA/SMK'],
            // SMK — Kesehatan
            ['nama' => 'Farmasi SMK',                  'kategori' => 'SMA/SMK'],
            ['nama' => 'Keperawatan SMK',              'kategori' => 'SMA/SMK'],
            ['nama' => 'Analis Kesehatan SMK',         'kategori' => 'SMA/SMK'],
            // SMK — Agribisnis
            ['nama' => 'Agribisnis Tanaman',           'kategori' => 'SMA/SMK'],
            ['nama' => 'Agribisnis Peternakan',        'kategori' => 'SMA/SMK'],
            // SMK — Lainnya
            ['nama' => 'Kimia Industri',               'kategori' => 'SMA/SMK'],
            ['nama' => 'Pelayaran',                    'kategori' => 'SMA/SMK'],
            ['nama' => 'Lainnya',                      'kategori' => 'SMA/SMK'],

            // ── PERGURUAN TINGGI (D3, S1, S1 Profesi, S2, S2 Profesi) ──
            // Hukum & Pemerintahan
            ['nama' => 'Hukum',                        'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Ilmu Hukum',                   'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Hukum Pidana',                 'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Hukum Perdata',                'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Hukum Tata Negara',            'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Hukum Administrasi Negara',    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Ilmu Pemerintahan',            'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Ilmu Politik',                 'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Hubungan Internasional',       'kategori' => 'Perguruan Tinggi'],
            // Administrasi & Manajemen
            ['nama' => 'Administrasi Publik',          'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Administrasi Negara',          'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Administrasi Bisnis',          'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Manajemen',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Manajemen SDM',                'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Manajemen Keuangan',           'kategori' => 'Perguruan Tinggi'],
            // Ekonomi & Akuntansi
            ['nama' => 'Ekonomi',                      'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Ekonomi Pembangunan',          'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Akuntansi',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Perpajakan',                   'kategori' => 'Perguruan Tinggi'],
            // Teknik & TI
            ['nama' => 'Teknik Informatika',           'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Sistem Informasi',             'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Ilmu Komputer',                'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Teknik Komputer',              'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Teknik Elektro',               'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Teknik Mesin',                 'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Teknik Sipil',                 'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Teknik Industri',              'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Teknik Kimia',                 'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Teknik Lingkungan',            'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Arsitektur',                   'kategori' => 'Perguruan Tinggi'],
            // Sosial & Humaniora
            ['nama' => 'Psikologi',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Sosiologi',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Ilmu Komunikasi',              'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Jurnalistik',                  'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Kesejahteraan Sosial',         'kategori' => 'Perguruan Tinggi'],
            // Sastra & Bahasa
            ['nama' => 'Sastra Indonesia',             'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Sastra Inggris',               'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Pendidikan Bahasa Inggris',    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Pendidikan Bahasa Indonesia',  'kategori' => 'Perguruan Tinggi'],
            // Pendidikan
            ['nama' => 'Pendidikan Guru SD (PGSD)',    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Pendidikan Matematika',        'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Pendidikan Pancasila & Kewarganegaraan', 'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Bimbingan Konseling',          'kategori' => 'Perguruan Tinggi'],
            // Kesehatan & Kedokteran
            ['nama' => 'Kedokteran',                   'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Kedokteran Gigi',              'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Keperawatan',                  'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Kebidanan',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Farmasi',                      'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Kesehatan Masyarakat',         'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Gizi',                         'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Analis Kesehatan',             'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Fisioterapi',                  'kategori' => 'Perguruan Tinggi'],
            // Pertanian & Kehutanan
            ['nama' => 'Pertanian',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Agroteknologi',                'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Agribisnis',                   'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Kehutanan',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Perikanan',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Teknologi Pangan',             'kategori' => 'Perguruan Tinggi'],
            // MIPA
            ['nama' => 'Matematika',                   'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Fisika',                       'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Kimia',                        'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Biologi',                      'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Statistika',                   'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Geografi',                     'kategori' => 'Perguruan Tinggi'],
            // Agama
            ['nama' => 'Ilmu Agama Islam',             'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Ekonomi Syariah',              'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Hukum Keluarga Islam',         'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Pendidikan Agama Islam',       'kategori' => 'Perguruan Tinggi'],
            // Kepolisian & Pertahanan
            ['nama' => 'Ilmu Kepolisian',              'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Kriminologi',                  'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Ilmu Pertahanan',              'kategori' => 'Perguruan Tinggi'],
            // Desain & Seni
            ['nama' => 'Desain Komunikasi Visual',     'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Desain Grafis',                'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Desain Interior',              'kategori' => 'Perguruan Tinggi'],
            // Lainnya
            ['nama' => 'Ilmu Perpustakaan',            'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Kearsipan',                    'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Pariwisata',                   'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Transportasi',                 'kategori' => 'Perguruan Tinggi'],
            ['nama' => 'Lainnya',                      'kategori' => 'Perguruan Tinggi'],
        ];

        foreach ($data as $item) {
            Prodi::create($item);
        }

        $this->command->info('✔ ' . count($data) . ' prodi berhasil di-seed.');
    }
}
