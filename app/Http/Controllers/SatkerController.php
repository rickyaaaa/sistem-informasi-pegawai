<?php

namespace App\Http\Controllers;

use App\Models\Satker;
use Illuminate\Http\Request;

class SatkerController extends Controller
{
    public function index()
    {
        $satkers = Satker::query()
            ->orderBy('nama_satker')
            ->paginate(10)
            ->withQueryString();

        return view('satker.index', [
            'satkers' => $satkers,
        ]);
    }

    public function create()
    {
        return view('satker.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satker' => ['required', 'string', 'max:255', 'unique:satkers,nama_satker'],
        ]);

        Satker::create($validated);

        return redirect()
            ->route('satker.index')
            ->with('success', 'Satker berhasil ditambahkan.');
    }

    public function edit(Satker $satker)
    {
        return view('satker.edit', [
            'satker' => $satker,
        ]);
    }

    public function update(Request $request, Satker $satker)
    {
        $validated = $request->validate([
            'nama_satker' => ['required', 'string', 'max:255', 'unique:satkers,nama_satker,'.$satker->id],
        ]);

        $satker->update($validated);

        return redirect()
            ->route('satker.index')
            ->with('success', 'Satker berhasil diperbarui.');
    }

    public function destroy(Satker $satker)
    {
        // NOTE (Thesis-friendly):
        // When we later connect pegawai/users to satker with foreign keys,
        // deletion may be restricted. We'll handle that case with a clear message.
        $satker->delete();

        return redirect()
            ->route('satker.index')
            ->with('success', 'Satker berhasil dihapus.');
    }
}
