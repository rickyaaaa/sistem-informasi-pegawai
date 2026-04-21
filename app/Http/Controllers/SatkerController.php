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

    public function create()
    {
        return view('satker.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satker' => ['required', 'string', 'max:255'],
            'tipe_satuan' => ['required', 'in:satker,satwil'],
            'sub_units' => ['nullable', 'array'],
            'sub_units.*' => ['string', 'max:255'],
        ]);

        $namaNormalized = strtoupper(trim($validated['nama_satker']));

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
                'level' => 'induk',
                'parent_id' => null,
            ]);

            if (!empty($validated['sub_units'])) {
                $subs = array_map(fn($s) => [
                    'nama_satker' => strtoupper(trim($s)),
                    'tipe_satuan' => $validated['tipe_satuan'],
                    'level' => 'sub',
                    'parent_id' => $parent->id,
                    'created_at' => now(),
                    'updated_at' => now(),
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
        $satker->delete(); // cascade ke children via DB foreign key

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

        // Hapus semua ID yang dipilih satu per satu agar men-trigger event 'deleting'
        Satker::whereIn('id', $request->ids)->get()->each(function (Satker $sat) {
            $sat->delete();
        });

        $count = count($request->ids);

        return redirect()
            ->route('satker.index')
            ->with('success', "{$count} item berhasil dihapus.");
    }
}