<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\PegawaiRequest;
use App\Models\Prodi;
use App\Models\Satker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class PegawaiImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts
{
    use SkipsFailures;

    private ?int $operatorParentId;
    private array $allowedSatkerIds;
    private bool $isAdminSatker;
    private ?int $requestedBy;

    /** @var array Pesan peringatan untuk baris yang dilewati (scope/satker tidak cocok) */
    private array $scopeWarnings = [];

    /** @var array Nomor baris yang terdeteksi kosong (untuk difilter dari error) */
    private array $emptyRows = [];

    public function __construct()
    {
        $user = Auth::user();

        if ($user && $user->isAdminSatker()) {
            $this->isAdminSatker    = true;
            $this->requestedBy      = $user->id;
            $this->operatorParentId = $user->satker_id;
            $this->allowedSatkerIds = Satker::where('parent_id', $user->satker_id)
                ->orWhere('id', $user->satker_id)
                ->pluck('id')
                ->toArray();
        } else {
            $this->isAdminSatker    = false;
            $this->requestedBy      = null;
            $this->operatorParentId = null;
            $this->allowedSatkerIds = [];
        }
    }

    /**
     * Kembalikan daftar peringatan baris yang dilewati karena tidak masuk scope satker.
     */
    public function getScopeWarnings(): array
    {
        return $this->scopeWarnings;
    }

    /**
     * Kembalikan daftar nomor baris kosong yang harus diabaikan dari error.
     */
    public function getEmptyRows(): array
    {
        return $this->emptyRows;
    }

    /**
     * 11 Columns: NIK, NAMA, TGL LAHIR, JK, PENDIDIKAN, PRODI, TGL KERJA, SATKER, UNIT KERJA, STATUS, KET
     * Upsert: NIK as unique key.
     */
    public function model(array $row)
    {
        // Lewati baris kosong (tidak ada NIK maupun NAMA)
        if (empty($row['nik']) && empty($row['nama'])) {
            return null;
        }

        $nik = $this->sanitizeNik($row['nik'] ?? '');

        // Normalize unit_kerja: trim + uppercase agar toleran terhadap perbedaan penulisan di Excel
        $unitKerjaName = strtoupper(trim($row['unit_kerja'] ?? ''));

        // Coba cari unit kerja di scope operator dulu
        $unitKerja = null;
        
        $unitKerjaQuery = Satker::whereRaw('UPPER(nama_satker) = ?', [$unitKerjaName])
            ->where('level', 'sub');

        if ($this->operatorParentId) {
            // Cari unit kerja yang memang berada di bawah induknya
            $unitKerja = (clone $unitKerjaQuery)->whereIn('id', $this->allowedSatkerIds)->first();
            
            // Jika tidak ketemu di scope operator, cek apakah unit kerja itu ada di satker lain
            if (!$unitKerja && (clone $unitKerjaQuery)->exists()) {
                $warning = "Baris ke-" . ($row['__row_number'] ?? '?') . ": Unit Kerja '{$unitKerjaName}' tidak terdaftar di bawah Satker Anda. Baris dilewati.";
                $this->scopeWarnings[] = $warning;
                Log::warning('[PegawaiImport] Scope violation: ' . $warning);
                return null;
            }
        } else {
            // Jika Admin Polda, tambahkan filter berdasar kolom 'satker' jika ada di template
            $satkerName = strtoupper(trim($row['satker'] ?? ''));
            if ($satkerName) {
                $indukSatker = Satker::whereRaw('UPPER(nama_satker) = ?', [$satkerName])->where('level', 'induk')->first();
                if ($indukSatker) {
                    $unitKerjaQuery->where('parent_id', $indukSatker->id);
                }
            }
            $unitKerja = $unitKerjaQuery->first();
        }

        if (! $unitKerja) {
            return null; // Ditangani oleh validasi (rule 'unit_kerja.in')
        }

        // Operator restriction legacy check (can be removed as it is handled above, but left for safety)
        if ($this->operatorParentId && ! in_array($unitKerja->id, $this->allowedSatkerIds)) {
            $warning = "Baris ke-" . ($row['__row_number'] ?? '?') . ": Unit Kerja '{$unitKerjaName}' tidak terdaftar di bawah Satker Anda. Baris dilewati.";
            $this->scopeWarnings[] = $warning;
            Log::warning('[PegawaiImport] Scope violation: ' . $warning);
            return null;
        }

        // Lookup prodi by name (optional)
        $prodiId = null;
        $prodiName = trim($row['prodi'] ?? '');
        if ($prodiName && $prodiName !== '-' && $prodiName !== 'Tanpa Jurusan') {
            $prodi = Prodi::where('nama', $prodiName)->first();
            $prodiId = $prodi ? $prodi->id : null;
        } else {
            // Assign "Tanpa Jurusan" for SD/SMP
            $tanpaJurusan = Prodi::where('nama', 'Tanpa Jurusan')->first();
            $prodiId = $tanpaJurusan ? $tanpaJurusan->id : null;
        }

        // Parse dates (handle both d/m/Y and Y-m-d formats)
        $tglLahir = $this->parseDate($row['tgl_lahir'] ?? null);
        $tglKerja = $this->parseDate($row['tgl_kerja'] ?? null);

        // Prepare data
        $data = [
            'nama'          => trim($row['nama']),
            'tgl_lahir'     => $tglLahir,
            'jenis_kelamin' => $this->normalizeGender($row['jk'] ?? ''),
            'pendidikan'    => trim($row['pendidikan'] ?? ''),
            'prodi_id'      => $prodiId,
            'tgl_kerja'     => $tglKerja,
            'satker_id'     => $unitKerja->id,
            'status'        => strtolower(trim($row['status'] ?? 'aktif')),
            'status_k2'     => trim($row['status_k2'] ?? 'Non K-II'),
            'nomor_k2'      => trim($row['nomor_k2'] ?? ''),
            'keterangan'    => trim($row['ket'] ?? ''),
        ];

        // ── admin_satker: buat PegawaiRequest (tidak langsung insert) ──
        if ($this->isAdminSatker) {
            PegawaiRequest::create([
                'pegawai_id'   => null,
                'satker_id'    => $unitKerja->id,
                'requested_by' => $this->requestedBy,
                'action_type'  => 'create',
                'data_payload' => array_merge($data, ['nik' => $nik]),
                'status'       => 'pending',
            ]);

            return null; // Jangan insert langsung ke pegawais
        }

        // ── super_admin: upsert langsung ──
        $pegawai = Pegawai::where('nik', $nik)->first();

        if ($pegawai) {
            $pegawai->update($data);
            return null;
        }

        $data['nik'] = $nik;
        return new Pegawai($data);
    }

    /**
     * Validation rules per row.
     */
    public function rules(): array
    {
        // Ambil semua nama sub-satker dalam bentuk UPPERCASE agar perbandingan tidak case-sensitive
        $validSatkerNames = Satker::where('level', 'sub')
            ->pluck('nama_satker')
            ->map(fn($n) => strtoupper(trim($n)))
            ->toArray();

        return [
            'nik'        => ['required', 'string', 'regex:/^[0-9]+$/'],
            'nama'       => ['required', 'string', 'max:255'],
            'jk'         => ['required', Rule::in(['Pria', 'Wanita', 'Laki-laki', 'Perempuan', 'L', 'P'])],
            'pendidikan' => ['required', Rule::in(['SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S1 Profesi', 'S2', 'S2 Profesi', 'S3'])],
            'unit_kerja' => ['required', Rule::in($validSatkerNames)],
            'status'     => ['required'],
            'status_k2'  => ['nullable', Rule::in(['K-II', 'Non K-II'])],
        ];
    }

    /**
     * Normalize data sebelum validasi dijalankan.
     * - Mengabaikan baris yang seluruh kolomnya kosong (pengganti SkipsOnEmptyRows)
     * - Mendeteksi NIK dalam format scientific notation (e.g. 1.23E+15)
     * - Mengubah unit_kerja menjadi UPPERCASE agar cocok dengan daftar validasi
     */
    public function prepareForValidation($data, $index)
    {
        // ── Skip baris kosong (pengganti SkipsOnEmptyRows) ──
        // Cek apakah semua kolom penting kosong; jika ya, kosongkan seluruh data
        // agar baris ini gagal validasi 'required' dan dilewati oleh SkipsOnFailure
        $allEmpty = empty(trim($data['nik'] ?? ''))
                 && empty(trim($data['nama'] ?? ''))
                 && empty(trim($data['unit_kerja'] ?? ''));

        if ($allEmpty) {
            // Catat nomor baris ini agar error-nya bisa difilter di controller
            $this->emptyRows[] = $index + 2; // +2 karena row 1 = heading
            // Kembalikan array kosong agar seluruh baris di-skip oleh SkipsOnFailure
            return array_map(fn() => null, $data);
        }

        // ── Deteksi NIK scientific notation (e.g. 1.23475869178262E+15) ──
        if (isset($data['nik'])) {
            $nikStr = trim((string) $data['nik']);

            // Jika mengandung 'e' atau 'E' (tanda scientific notation), tandai sebagai rusak
            if (preg_match('/[eE][+\-]/', $nikStr)) {
                Log::warning("[PegawaiImport] Baris " . ($index + 2) . ": NIK terbaca scientific notation '{$nikStr}'");
                // Set ke string khusus agar gagal validasi regex dengan pesan kustom
                $data['nik'] = 'SCIENTIFIC_NOTATION_ERROR';
            } else {
                // Pastikan NIK selalu string (bukan float/int dari Excel)
                $data['nik'] = (string) $nikStr;
            }
        }

        if (isset($data['unit_kerja'])) {
            $data['unit_kerja'] = strtoupper(trim($data['unit_kerja']));
        }

        // Simpan nomor baris untuk digunakan di pesan error scope
        $data['__row_number'] = $index + 2; // +2 karena row 1 = heading
        return $data;
    }

    public function customValidationMessages(): array
    {
        return [
            'nik.required'        => 'Kolom NIK wajib diisi.',
            'nik.string'          => 'Kolom NIK harus berupa teks, bukan angka.',
            'nik.regex'           => 'Format NIK terbaca sebagai angka logaritma. Pastikan kolom NIK diubah menjadi Text di Excel sebelum di-save ke CSV.',
            'nama.required'       => 'Kolom NAMA wajib diisi.',
            'jk.required'         => 'Kolom JK wajib diisi.',
            'jk.in'               => 'JK harus "Pria", "Wanita", "L", atau "P".',
            'pendidikan.required' => 'Kolom PENDIDIKAN wajib diisi.',
            'pendidikan.in'       => 'PENDIDIKAN tidak valid. Pilihan: SD, SMP, SMA/SMK, D3, S1, S1 Profesi, S2, S2 Profesi.',
            'unit_kerja.required' => 'Kolom UNIT KERJA wajib diisi.',
            'unit_kerja.in'       => 'Nama UNIT KERJA tidak ditemukan di sistem.',
            'status.required'     => 'Kolom STATUS wajib diisi.',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Sanitize NIK: ensure it's always a 16-digit string.
     */
    private function sanitizeNik($nik): string
    {
        $nik = preg_replace('/\D/', '', (string) $nik);
        return str_pad($nik, 16, '0', STR_PAD_LEFT);
    }

    /**
     * Parse Indonesian date format (DD/MM/YYYY) or standard format.
     */
    private function parseDate($value): ?string
    {
        if (empty($value)) return null;

        // If it's a numeric (Excel serial date)
        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))
                ->format('Y-m-d');
        }

        // Try dd/mm/yyyy
        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {}

        // Try yyyy-mm-dd
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {}

        return null;
    }
    /**
     * Map various gender inputs to "Pria" or "Wanita".
     */
    private function normalizeGender($value): string
    {
        $v = trim((string) $value);
        if (in_array($v, ['Laki-laki', 'L', 'Pria'])) return 'Pria';
        if (in_array($v, ['Perempuan', 'P', 'Wanita'])) return 'Wanita';
        return $v;
    }
}
