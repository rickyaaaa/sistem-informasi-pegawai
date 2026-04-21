<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\PegawaiRequest;
use App\Models\Prodi;
use App\Models\Satker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
     * 11 Columns: NIK, NAMA, TGL LAHIR, JK, PENDIDIKAN, PRODI, TGL KERJA, SATKER, UNIT KERJA, STATUS, KET
     * Upsert: NIK as unique key.
     */
    public function model(array $row)
    {
        $nik = $this->sanitizeNik($row['nik'] ?? '');

        // Lookup unit kerja (sub-unit) by name
        $unitKerja = Satker::where('nama_satker', trim($row['unit_kerja'] ?? ''))
            ->where('level', 'sub')
            ->first();

        if (! $unitKerja) {
            return null; // Handled by validation
        }

        // Operator restriction
        if ($this->operatorParentId && ! in_array($unitKerja->id, $this->allowedSatkerIds)) {
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
        $validSatkerNames = Satker::where('level', 'sub')
            ->pluck('nama_satker')
            ->toArray();

        return [
            'nik'        => ['required', 'string'],
            'nama'       => ['required', 'string', 'max:255'],
            'jk'         => ['required', Rule::in(['Pria', 'Wanita', 'Laki-laki', 'Perempuan', 'L', 'P'])],
            'pendidikan' => ['required', Rule::in(['SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S1 Profesi', 'S2', 'S2 Profesi', 'S3'])],
            'unit_kerja' => ['required', Rule::in($validSatkerNames)],
            'status'     => ['required'],
            'status_k2'  => ['nullable', Rule::in(['K-II', 'Non K-II'])],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nik.required'        => 'Kolom NIK wajib diisi.',
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
