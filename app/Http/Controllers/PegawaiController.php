<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\PegawaiRequest;
use App\Models\Prodi;
use App\Models\Satker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\PegawaiImport;
use App\Exports\PegawaiExport;
use App\Exports\PegawaiTemplateExport;
use App\Notifications\NewPegawaiRequestNotification;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pegawai::query()->with('satker');

        // ── Admin Satker: scope to their satker's sub-units only ──
        if ($user->isAdminSatker()) {
            $allowedSatkerIds = Satker::where('parent_id', $user->satker_id)
                ->orWhere('id', $user->satker_id)
                ->pluck('id');
            $query->whereIn('satker_id', $allowedSatkerIds);
        }

        // Both roles can further filter by satker
        if ($request->filled('satker_id')) {
            $satkerId = (int) $request->input('satker_id');
            // Include sub-satkers
            $satkerIds = Satker::where('id', $satkerId)->orWhere('parent_id', $satkerId)->pluck('id');
            $query->whereIn('satker_id', $satkerIds);
        }

        // Search nama / NIK
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('nik', 'like', "%{$q}%");
            });
        }

        $pegawais = $query
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        // Task 17: Operator sees only sub-satkers of their own satker in the dropdown
        if ($user->isAdminSatker()) {
            $satkers = Satker::where('parent_id', $user->satker_id)
                ->orWhere('id', $user->satker_id)
                ->orderBy('nama_satker')
                ->get();
        } else {
            $satkers = Satker::where('level', 'induk')->orderBy('nama_satker')->get();
        }

        return view('pegawai.index', [
            'pegawais' => $pegawais,
            'satkers' => $satkers,
            'selectedSatkerId' => $request->input('satker_id'),
            'q' => $request->input('q'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        return view('pegawai.create', [
            'indukSatkers' => $this->getIndukSatkers($user),
            'prodis' => Prodi::orderBy('nama')->get(),
            'user' => $user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * - Admin Polda  → direct insert to pegawais
     * - admin_satker → create PegawaiRequest (pending)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/', 'unique:pegawais,nik'],
            'tgl_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', 'string', 'in:Pria,Wanita'],
            'pendidikan' => ['required', 'string', 'in:SD,SMP,SMA/SMK,D3,S1,S1 Profesi,S2,S2 Profesi,S3'],
            'prodi_id' => ['nullable', 'integer', 'exists:prodis,id'],
            'prodi_lainnya' => ['nullable', 'string', 'max:255'],
            'tgl_kerja' => ['nullable', 'date'],
            'satker_id' => ['nullable', 'integer', 'exists:satkers,id'],
            'status' => ['required', 'in:aktif,non_aktif'],
            'status_k2' => ['required', 'in:K-II,Non K-II'],
            'nomor_k2' => ['nullable', 'required_if:status_k2,K-II', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'file_ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'file_kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'file_ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // Handle "Lainnya" prodi — create new prodi if provided
        if (!empty($validated['prodi_lainnya'])) {
            $kategoriProdi = in_array($validated['pendidikan'], ['SD', 'SMP'])
                ? 'Umum'
                : (($validated['pendidikan'] === 'SMA/SMK') ? 'SMA/SMK' : 'Perguruan Tinggi');

            $newProdi = Prodi::firstOrCreate(
                ['nama' => $validated['prodi_lainnya'], 'kategori' => $kategoriProdi]
            );
            $validated['prodi_id'] = $newProdi->id;
        }
        unset($validated['prodi_lainnya']);

        // Determine satker_id
        if ($user->isAdminSatker()) {
            // Validate that chosen satker_id is a sub-unit of the operator's parent
            if (empty($validated['satker_id'])) {
                return back()
                    ->withErrors(['satker_id' => 'Sub-unit wajib dipilih.'])
                    ->withInput();
            }

            $isValidSub = Satker::where('id', $validated['satker_id'])
                ->where('parent_id', $user->satker_id)
                ->exists();

            // Allow if the chosen unit IS the operator's own unit (induk without subs)
            if (!$isValidSub && $validated['satker_id'] != $user->satker_id) {
                abort(403, 'Sub-unit tidak valid untuk satker Anda.');
            }
        } elseif (empty($validated['satker_id'])) {
            return back()
                ->withErrors(['satker_id' => 'Satker wajib dipilih.'])
                ->withInput();
        }

        // Upload files (done ahead of time so path is stored in payload)
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')
                ->store('pegawai/foto', 'public');
        } else {
            unset($validated['foto']);
        }
        if ($request->hasFile('file_ktp')) {
            $validated['file_ktp'] = $request->file('file_ktp')
                ->store('pegawai/ktp', 'public');
        } else {
            unset($validated['file_ktp']);
        }
        if ($request->hasFile('file_kk')) {
            $validated['file_kk'] = $request->file('file_kk')
                ->store('pegawai/kk', 'public');
        } else {
            unset($validated['file_kk']);
        }
        if ($request->hasFile('file_ijazah')) {
            $validated['file_ijazah'] = $request->file('file_ijazah')
                ->store('pegawai/ijazah', 'public');
        } else {
            unset($validated['file_ijazah']);
        }

        // ── Admin Polda: direct insert ──────────────────────────
        if ($user->isSuperAdmin()) {
            Pegawai::create($validated);

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Pegawai berhasil ditambahkan.');
        }

        // ── Admin satker: submit for approval ───────────────────
        $pegawaiRequest = PegawaiRequest::create([
            'pegawai_id' => null,
            'satker_id' => $validated['satker_id'],
            'requested_by' => $user->id,
            'action_type' => 'create',
            'data_payload' => $validated,
            'status' => 'pending',
        ]);

        // Notify Admin
        $this->notifyAdmin($pegawaiRequest);

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Permintaan tambah pegawai telah dikirim dan menunggu persetujuan.');
    }

    /**
     * Display the specified resource (detail profile).
     */
    public function show(Pegawai $pegawai)
    {
        $user = auth()->user();

        $this->authorizePegawaiAccess($pegawai);

        return view('pegawai.show', [
            'pegawai' => $pegawai->load('satker'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pegawai $pegawai)
    {
        $user = auth()->user();

        $this->authorizePegawaiAccess($pegawai);

        // For edit: pre-load sub-units of the pegawai's current parent
        $currentParentId = optional($pegawai->satker)->parent_id;

        if (optional($pegawai->satker)->level === 'induk') {
            // Pegawai menempel langsung pada induk (induk tidak punya sub)
            $currentParentId = $pegawai->satker_id;
            $subSatkers = collect([
                (object) ['id' => $pegawai->satker_id, 'nama_satker' => '- ' . $pegawai->satker->nama_satker . ' (Tanpa Sub-Unit) -']
            ]);
        } else {
            $subSatkers = $currentParentId
                ? Satker::where('parent_id', $currentParentId)->where('level', 'sub')->orderBy('nama_satker')->get()
                : collect();
        }

        return view('pegawai.edit', [
            'pegawai' => $pegawai->load('satker', 'prodi'),
            'indukSatkers' => $this->getIndukSatkers($user),
            'subSatkers' => $subSatkers,
            'prodis' => Prodi::orderBy('nama')->get(),
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * - Admin Polda  → direct update
     * - admin_satker → create PegawaiRequest (pending)
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        $user = $request->user();

        $this->authorizePegawaiAccess($pegawai);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/', 'unique:pegawais,nik,' . $pegawai->id],
            'tgl_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', 'string', 'in:Pria,Wanita'],
            'pendidikan' => ['required', 'string', 'in:SD,SMP,SMA/SMK,D3,S1,S1 Profesi,S2,S2 Profesi,S3'],
            'prodi_id' => ['nullable', 'integer', 'exists:prodis,id'],
            'prodi_lainnya' => ['nullable', 'string', 'max:255'],
            'tgl_kerja' => ['nullable', 'date'],
            'satker_id' => ['nullable', 'integer', 'exists:satkers,id'],
            'status' => ['required', 'in:aktif,non_aktif'],
            'status_k2' => ['required', 'in:K-II,Non K-II'],
            'nomor_k2' => ['nullable', 'required_if:status_k2,K-II', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'file_ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'file_kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'file_ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // Handle "Lainnya" prodi — create new prodi if provided
        if (!empty($validated['prodi_lainnya'])) {
            $kategoriProdi = in_array($validated['pendidikan'], ['SD', 'SMP'])
                ? 'Umum'
                : (($validated['pendidikan'] === 'SMA/SMK') ? 'SMA/SMK' : 'Perguruan Tinggi');

            $newProdi = Prodi::firstOrCreate(
                ['nama' => $validated['prodi_lainnya'], 'kategori' => $kategoriProdi]
            );
            $validated['prodi_id'] = $newProdi->id;
        }
        unset($validated['prodi_lainnya']);

        if ($user->isAdminSatker()) {
            // Validate that chosen satker_id is a sub-unit of the operator's parent
            if (empty($validated['satker_id'])) {
                return back()
                    ->withErrors(['satker_id' => 'Sub-unit wajib dipilih.'])
                    ->withInput();
            }

            $isValidSub = Satker::where('id', $validated['satker_id'])
                ->where('parent_id', $user->satker_id)
                ->exists();

            if (!$isValidSub && $validated['satker_id'] != $user->satker_id) {
                abort(403, 'Sub-unit tidak valid untuk satker Anda.');
            }
        } elseif (empty($validated['satker_id'])) {
            return back()
                ->withErrors(['satker_id' => 'Satker wajib dipilih.'])
                ->withInput();
        }

        // Upload files
        if ($request->hasFile('foto')) {
            // Delete old foto if exists
            if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
                Storage::disk('public')->delete($pegawai->foto);
            }
            $validated['foto'] = $request->file('foto')
                ->store('pegawai/foto', 'public');
        } else {
            unset($validated['foto']);
        }
        if ($request->hasFile('file_ktp')) {
            if ($pegawai->file_ktp && Storage::disk('public')->exists($pegawai->file_ktp)) {
                Storage::disk('public')->delete($pegawai->file_ktp);
            }
            $validated['file_ktp'] = $request->file('file_ktp')
                ->store('pegawai/ktp', 'public');
        } else {
            unset($validated['file_ktp']);
        }
        if ($request->hasFile('file_kk')) {
            if ($pegawai->file_kk && Storage::disk('public')->exists($pegawai->file_kk)) {
                Storage::disk('public')->delete($pegawai->file_kk);
            }
            $validated['file_kk'] = $request->file('file_kk')
                ->store('pegawai/kk', 'public');
        } else {
            unset($validated['file_kk']);
        }
        if ($request->hasFile('file_ijazah')) {
            if ($pegawai->file_ijazah && Storage::disk('public')->exists($pegawai->file_ijazah)) {
                Storage::disk('public')->delete($pegawai->file_ijazah);
            }
            $validated['file_ijazah'] = $request->file('file_ijazah')
                ->store('pegawai/ijazah', 'public');
        } else {
            unset($validated['file_ijazah']);
        }

        // Determine redirect target (back to show page if coming from there)
        $redirectBack = $request->has('_redirect_show');

        // ── Admin Polda: direct update ──────────────────────────
        if ($user->isSuperAdmin()) {
            $pegawai->update($validated);

            $route = $redirectBack
                ? route('pegawai.show', $pegawai)
                : route('pegawai.index');

            return redirect($route)
                ->with('success', 'Pegawai berhasil diperbarui.');
        }

        // ── Admin satker: submit for approval ───────────────────
        $pegawaiRequest = PegawaiRequest::create([
            'pegawai_id' => $pegawai->id,
            'satker_id' => $validated['satker_id'],
            'requested_by' => $user->id,
            'action_type' => 'update',
            'data_payload' => $validated,
            'status' => 'pending',
        ]);

        // Notify Admin
        $this->notifyAdmin($pegawaiRequest);

        $route = $redirectBack
            ? route('pegawai.show', $pegawai)
            : route('pegawai.index');

        return redirect($route)
            ->with('success', 'Permintaan ubah data pegawai telah dikirim dan menunggu persetujuan.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * - Admin Polda  → soft delete
     * - admin_satker → create PegawaiRequest (pending)
     */
    public function destroy(Pegawai $pegawai)
    {
        $user = auth()->user();

        $this->authorizePegawaiAccess($pegawai);

        // ── Admin Polda: direct soft delete ─────────────────────
        if ($user->isSuperAdmin()) {
            $pegawai->delete();

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Pegawai berhasil dihapus.');
        }

        // ── Admin satker: submit for approval ───────────────────
        $pegawaiRequest = PegawaiRequest::create([
            'pegawai_id' => $pegawai->id,
            'satker_id' => $pegawai->satker_id,
            'requested_by' => $user->id,
            'action_type' => 'delete',
            'data_payload' => ['nama' => $pegawai->nama, 'nik' => $pegawai->nik],
            'status' => 'pending',
        ]);

        // Notify Admin
        $this->notifyAdmin($pegawaiRequest);

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Permintaan hapus pegawai telah dikirim dan menunggu persetujuan.');
    }

    /**
     * Preview file KTP / KK
     */
    public function showFile(Pegawai $pegawai, string $type)
    {
        $user = auth()->user();

        $this->authorizePegawaiAccess($pegawai);

        if (!in_array($type, ['ktp', 'kk', 'ijazah'])) {
            abort(404);
        }

        $path = match ($type) {
            'ktp' => $pegawai->file_ktp,
            'kk' => $pegawai->file_kk,
            'ijazah' => $pegawai->file_ijazah,
            default => null,
        };

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(
            storage_path('app/public/' . $path)
        );
    }

    /**
     * Download file KTP / KK
     */
    public function downloadFile(Pegawai $pegawai, string $type)
    {
        $user = auth()->user();

        $this->authorizePegawaiAccess($pegawai);

        if (!in_array($type, ['ktp', 'kk', 'ijazah'])) {
            abort(404);
        }

        $path = match ($type) {
            'ktp' => $pegawai->file_ktp,
            'kk' => $pegawai->file_kk,
            'ijazah' => $pegawai->file_ijazah,
            default => null,
        };

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->download(storage_path('app/public/' . $path));
    }

    /**
     * Export Excel
     */
    public function export(Request $request)
    {
        return Excel::download(
            new PegawaiExport(auth()->user()),
            'data_pegawai_' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Import Pegawai from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new PegawaiImport();
        Excel::import($import, $request->file('file'));

        // Notify Admin of new requests from import
        if ($request->user()->isAdminSatker()) {
            $this->notifyAdminSummary($request->user());
        }

        // Collect row-level validation failures
        $failures  = $import->failures();
        $emptyRows = $import->getEmptyRows(); // Baris kosong yang harus diabaikan
        $errors    = [];
        foreach ($failures as $failure) {
            $row    = $failure->row();
            $column = $failure->attribute();
            // Skip internal helper field from showing to user
            if ($column === '__row_number') continue;
            // Skip error dari baris kosong (sudah ditandai di PegawaiImport)
            if (in_array($row, $emptyRows)) continue;
            foreach ($failure->errors() as $msg) {
                $errors[] = "Baris ke-{$row} (kolom '{$column}'): {$msg}";
            }
        }

        // Collect scope-violation warnings (rows blocked by satker restriction)
        $scopeWarnings = $import->getScopeWarnings();

        $allWarnings = array_merge($errors, $scopeWarnings);

        if (!empty($allWarnings)) {
            return redirect()
                ->route('pegawai.index')
                ->with('import_errors', $allWarnings)
                ->with('warning', 'Import selesai dengan ' . count($allWarnings) . ' peringatan. Baris yang gagal/tidak sesuai satker dilewati.');
        }

        $isOperator = $request->user()->isAdminSatker();
        $successMsg = $isOperator
            ? 'Import selesai! Semua data dikirim sebagai permintaan dan menunggu persetujuan Admin Polda.'
            : 'Import data pegawai berhasil!';

        return redirect()
            ->route('pegawai.index')
            ->with('success', $successMsg);
    }

    /**
     * Download blank Excel template for import.
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new PegawaiTemplateExport(),
                'template_import_pegawai.xlsx'
            );
        } catch (\Throwable $e) {
            \Log::error('[downloadTemplate] Gagal generate template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunduh template. Silakan hubungi administrator. (' . $e->getMessage() . ')');
        }
    }

    // ── AJAX endpoint for dependent dropdown ────────────────────

    /**
     * Return sub-units (level=sub) for a given parent (induk) ID.
     * Used by the dependent dropdown via fetch API.
     */
    public function getSubSatker(int $id)
    {
        $subSatkers = Satker::where('parent_id', $id)
            ->where('level', 'sub')
            ->orderBy('nama_satker')
            ->get(['id', 'nama_satker']);

        if ($subSatkers->isEmpty()) {
            $parent = Satker::find($id);
            if ($parent) {
                return response()->json([
                    ['id' => $parent->id, 'nama_satker' => '- ' . $parent->nama_satker . ' (Tanpa Sub-Unit) -']
                ]);
            }
        }

        return response()->json($subSatkers);
    }

    /**
     * Return prodi list filtered by kategori.
     * Uses query param ?kategori= to avoid slash issue with SMA/SMK.
     */
    public function getProdiByKategori(Request $request)
    {
        $kategori = $request->query('kategori', '');

        $prodis = Prodi::where('kategori', $kategori)
            ->orWhere('kategori', 'Umum')
            ->orderBy('nama')
            ->get(['id', 'nama', 'kategori']);

        return response()->json($prodis);
    }

    // ── Private helpers for satker dropdown ──────────────────────

    /**
     * Get induk satkers for the first dropdown.
     * - Admin Polda: all induk
     * - Operator: only their assigned induk (locked)
     */
    private function getIndukSatkers($user)
    {
        if ($user->isSuperAdmin()) {
            return Satker::where('level', 'induk')
                ->orderBy('nama_satker')
                ->get();
        }

        // Operator: only their own induk
        return Satker::where('id', $user->satker_id)
            ->get();
    }

    /**
     * Helper to consistently authorize operator access to a Pegawai
     */
    private function authorizePegawaiAccess(Pegawai $pegawai)
    {
        $user = auth()->user();

        if ($user->isAdminSatker()) {
            $allowedIds = Satker::where('parent_id', $user->satker_id)
                ->orWhere('id', $user->satker_id)
                ->pluck('id')
                ->toArray();

            if (!in_array($pegawai->satker_id, $allowedIds)) {
                abort(403, 'Akses ditolak: Pegawai tidak berada di bawah wewenang Satker Anda.');
            }
        }
    }

    /**
     * Tampilkan data pegawai yang di-soft-delete (Arsip).
     * Hanya Super Admin.
     */
    public function arsip(Request $request)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $query = Pegawai::onlyTrashed()->with('satker');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('nik', 'like', "%{$q}%");
            });
        }

        $pegawais = $query->orderBy('deleted_at', 'desc')->paginate(10)->withQueryString();

        return view('pegawai.arsip', compact('pegawais'));
    }

    /**
     * Restore pegawai from soft deletes.
     * Hanya Super Admin.
     */
    public function restore($id)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $pegawai = Pegawai::onlyTrashed()->findOrFail($id);
        $pegawai->restore();

        return redirect()->route('pegawai.arsip')->with('success', 'Pegawai berhasil dipulihkan dari arsip.');
    }

    /**
     * Force delete (permanen) pegawai dari database.
     * Hanya Super Admin.
     */
    public function forceDelete($id)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $pegawai = Pegawai::onlyTrashed()->findOrFail($id);

        // Hapus file terkait jika ada
        if ($pegawai->foto && Storage::disk('public')->exists($pegawai->foto)) {
            Storage::disk('public')->delete($pegawai->foto);
        }
        if ($pegawai->file_ktp && Storage::disk('public')->exists($pegawai->file_ktp)) {
            Storage::disk('public')->delete($pegawai->file_ktp);
        }
        if ($pegawai->file_kk && Storage::disk('public')->exists($pegawai->file_kk)) {
            Storage::disk('public')->delete($pegawai->file_kk);
        }
        if ($pegawai->file_ijazah && Storage::disk('public')->exists($pegawai->file_ijazah)) {
            Storage::disk('public')->delete($pegawai->file_ijazah);
        }

        $pegawai->forceDelete();

        return redirect()->route('pegawai.arsip')->with('success', 'Pegawai berhasil dihapus secara permanen.');
    }

    /**
     * Helper to notify Super Admin via Email
     */
    private function notifyAdmin(PegawaiRequest $request)
    {
        try {
            $adminEmail = config('app.admin_notification_email');
            
            if ($adminEmail) {
                Notification::route('mail', $adminEmail)
                    ->notify(new NewPegawaiRequestNotification($request));
            }
        } catch (\Exception $e) {
            // Silently fail if mail is not configured
            \Log::error('Gagal mengirim notifikasi email: ' . $e->getMessage());
        }
    }

    /**
     * Helper to notify Super Admin about a bulk import
     */
    private function notifyAdminSummary($operator)
    {
        try {
            $adminEmail = config('app.admin_notification_email');
            
            if ($adminEmail) {
                // We'll just send a general "check your dashboard" notification for imports
                Notification::route('mail', $adminEmail)
                    ->notify(new \App\Notifications\BulkImportNotification($operator));
            }
        } catch (\Exception $e) {
            \Log::error('Gagal mengirim notifikasi email summary: ' . $e->getMessage());
        }
    }
}

