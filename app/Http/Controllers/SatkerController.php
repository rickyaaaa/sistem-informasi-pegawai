<?php

namespace App\Http\Controllers;

use App\Models\Satker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatkerController extends Controller
{
    public function index(Request $request)
    {
        $query = Satker::whereNull('parent_id')
            ->with([
                'children' => function ($query) {
                    $query->orderBy('nama_satker');
                }
            ])
            ->orderBy('nama_satker');

        // Filter by name search
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where('nama_satker', 'like', "%{$q}%");
        }

        // Filter by type (satker / satwil)
        if ($request->filled('tipe')) {
            $query->where('tipe_satuan', $request->input('tipe'));
        }

        $satkers = $query->paginate(10)->withQueryString();

        return view('satker.index', compact('satkers'));
    }

    public function create(Request $request)
    {
        $parents = Satker::whereNull('parent_id')->orderBy('nama_satker')->get();
        // Support pre-selection of parent_id from query string (e.g. "Tambah Sub-Unit" button)
        $preselectedParentId = $request->query('parent_id');

        return view('satker.create', compact('parents', 'preselectedParentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satker' => ['required', 'string', 'max:255'],
            'tipe_satuan' => ['nullable', 'in:satker,satwil'],
            'parent_id'   => ['nullable', 'exists:satkers,id'],
            'sub_units'   => ['nullable', 'array'],
            'sub_units.*' => ['string', 'max:255'],
        ]);

        $namaNormalized = strtoupper(trim($validated['nama_satker']));
        $parentId       = !empty($validated['parent_id']) ? (int)$validated['parent_id'] : null;

        // ── Mode Sub-Unit: parent_id diberikan → tambah sub-unit ke induk yang ada ──
        if ($parentId) {
            $parent = Satker::findOrFail($parentId);

            // Cek duplikat nama pada parent yang sama
            $existingSub = Satker::where('parent_id', $parentId)
                ->where('nama_satker', $namaNormalized)
                ->first();

            if ($existingSub) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => 'already_exists'], 409);
                }
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['nama_satker' => 'Sub-unit dengan nama ini sudah ada di satker induk tersebut.']);
            }

            Satker::create([
                'nama_satker' => $namaNormalized,
                'tipe_satuan' => $parent->tipe_satuan,
                'level'       => 'sub',
                'parent_id'   => $parentId,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'created'], 201);
            }

            return redirect()
                ->route('satker.edit', $parentId)
                ->with('success', "Sub-unit \"{$namaNormalized}\" berhasil ditambahkan.");
        }

        // ── Mode Induk: buat Satker Induk baru (+ sub_units opsional) ──
        // Cek duplikat induk
        $existing = Satker::whereNull('parent_id')
            ->where('nama_satker', $namaNormalized)
            ->first();

        if ($existing) {
            // Kalau request dari AI import (AJAX), return 409 Conflict
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'already_exists'], 409);
            }
            return redirect()->back()
                ->withInput()
                ->withErrors(['nama_satker' => 'Satker/Satwil dengan nama ini sudah ada.']);
        }

        DB::transaction(function () use ($validated, $namaNormalized) {
            $parent = Satker::create([
                'nama_satker' => $namaNormalized,
                'tipe_satuan' => $validated['tipe_satuan'],
                'level'       => 'induk',
                'parent_id'   => null,
            ]);

            if (!empty($validated['sub_units'])) {
                $subs = array_map(fn($s) => [
                    'nama_satker' => strtoupper(trim($s)),
                    'tipe_satuan' => $validated['tipe_satuan'],
                    'level'       => 'sub',
                    'parent_id'   => $parent->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ], $validated['sub_units']);

                Satker::insert($subs);
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'created'], 201);
        }

        return redirect()
            ->route('satker.index')
            ->with('success', 'Satker/Satwil beserta sub-unit berhasil ditambahkan.');

    }

    public function edit(Satker $satker)
    {
        $parents = Satker::whereNull('parent_id')
            ->where('id', '!=', $satker->id)
            ->orderBy('nama_satker')
            ->get();

        return view('satker.edit', compact('satker', 'parents'));
    }

    public function update(Request $request, Satker $satker)
    {
        $validated = $request->validate([
            'nama_satker' => ['required', 'string', 'max:255'],
            'tipe_satuan' => ['required', 'in:satker,satwil'],
            'parent_id' => ['nullable', 'exists:satkers,id'],
        ]);

        $validated['nama_satker'] = strtoupper($validated['nama_satker']);
        $validated['level'] = !empty($validated['parent_id']) ? 'sub' : 'induk';

        $satker->update($validated);

        return redirect()
            ->route('satker.index')
            ->with('success', 'Satker/Satwil berhasil diperbarui.');
    }

    public function destroy(Satker $satker)
    {
        // ── Proteksi: hitung semua data yang akan ikut terhapus ──────────
        $totalPegawai = $this->countPegawaiRecursive($satker);
        $totalUsers   = $this->countUsersRecursive($satker);

        if ($totalPegawai > 0 || $totalUsers > 0) {
            $detail = [];
            if ($totalPegawai > 0) $detail[] = "{$totalPegawai} data pegawai";
            if ($totalUsers > 0)   $detail[] = "{$totalUsers} akun operator/user";
            $detailStr = implode(' dan ', $detail);

            return redirect()
                ->route('satker.index')
                ->with('error', "⛔ Satker \"{$satker->nama_satker}\" tidak dapat dihapus karena masih memiliki {$detailStr}. Hapus atau pindahkan data tersebut terlebih dahulu.");
        }

        $satker->delete(); // cascade ke children via model event

        return redirect()
            ->route('satker.index')
            ->with('success', 'Satker/Satwil berhasil dihapus.');
    }

    /**
     * Bulk delete: hapus banyak satker/sub sekaligus.
     * Dikirim dari form checkbox di index.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:satkers,id'],
        ]);

        $satkers = Satker::query()->whereIn('id', $request->ids)->get();
        $blocked = [];

        /** @var \App\Models\Satker $sat */
        foreach ($satkers as $sat) {
            $totalPegawai = $this->countPegawaiRecursive($sat);
            $totalUsers   = $this->countUsersRecursive($sat);

            if ($totalPegawai > 0 || $totalUsers > 0) {
                $detail = [];
                if ($totalPegawai > 0) $detail[] = "{$totalPegawai} pegawai";
                if ($totalUsers > 0)   $detail[] = "{$totalUsers} user";
                $blocked[] = "\"{$sat->nama_satker}\" (" . implode(', ', $detail) . ")";
            } else {
                $sat->delete();
            }
        }

        $deletedCount = count($satkers) - count($blocked);
        $msg = "{$deletedCount} item berhasil dihapus.";

        if (!empty($blocked)) {
            $msg .= " Item berikut tidak dapat dihapus karena masih memiliki data: " . implode('; ', $blocked) . ".";
            return redirect()->route('satker.index')->with('warning', $msg);
        }

        return redirect()
            ->route('satker.index')
            ->with('success', $msg);
    }

    // ── Private Helpers ───────────────────────────────────────────────

    /**
     * Hitung total pegawai pada satker beserta seluruh sub-unitnya (rekursif).
     */
    private function countPegawaiRecursive(Satker $satker): int
    {
        $count = $satker->pegawais()->withTrashed()->count();
        foreach ($satker->children as $child) {
            $count += $this->countPegawaiRecursive($child);
        }
        return $count;
    }

    /**
     * Hitung total user/operator pada satker beserta seluruh sub-unitnya (rekursif).
     */
    private function countUsersRecursive(Satker $satker): int
    {
        $count = $satker->users()->count();
        foreach ($satker->children as $child) {
            $count += $this->countUsersRecursive($child);
        }
        return $count;
    }
}