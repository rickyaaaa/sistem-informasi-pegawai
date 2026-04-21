<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use Illuminate\Http\Request;

class ProgramStudiController extends Controller
{
    /**
     * Display a listing of program studi.
     */
    public function index(Request $request)
    {
        $query = Prodi::query()->orderBy('kategori')->orderBy('nama');

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where('nama', 'like', "%{$q}%")
                  ->orWhere('kategori', 'like', "%{$q}%");
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        $prodis = $query->paginate(15)->withQueryString();

        $kategoriList = Prodi::distinct()->orderBy('kategori')->pluck('kategori');

        return view('prodi.index', compact('prodis', 'kategoriList'));
    }

    /**
     * Show the form for creating a new program studi.
     */
    public function create()
    {
        return view('prodi.create');
    }

    /**
     * Store a newly created program studi.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'in:Umum,SMA/SMK,Perguruan Tinggi'],
        ]);

        $validated['nama'] = trim($validated['nama']);

        // Prevent duplicates
        $exists = Prodi::where('nama', $validated['nama'])
            ->where('kategori', $validated['kategori'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['nama' => 'Program studi dengan nama dan kategori ini sudah ada.']);
        }

        Prodi::create($validated);

        return redirect()->route('prodi.index')
            ->with('success', 'Program studi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified program studi.
     */
    public function edit(Prodi $prodi)
    {
        return view('prodi.edit', compact('prodi'));
    }

    /**
     * Update the specified program studi.
     */
    public function update(Request $request, Prodi $prodi)
    {
        $validated = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'in:Umum,SMA/SMK,Perguruan Tinggi'],
        ]);

        $validated['nama'] = trim($validated['nama']);

        $prodi->update($validated);

        return redirect()->route('prodi.index')
            ->with('success', 'Program studi berhasil diperbarui.');
    }

    /**
     * Remove the specified program studi.
     */
    public function destroy(Prodi $prodi)
    {
        // Check if prodi is in use
        if ($prodi->pegawais()->count() > 0) {
            return back()->with('error', 'Program studi ini masih digunakan oleh ' . $prodi->pegawais()->count() . ' pegawai. Tidak dapat dihapus.');
        }

        $prodi->delete();

        return redirect()->route('prodi.index')
            ->with('success', 'Program studi berhasil dihapus.');
    }
}
