<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Satker;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        // Enforce policy for all resource actions.
        $this->authorizeResource(Pegawai::class, 'pegawai');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pegawai::query()->with('satker');

        // Server-side satker scoping (CRITICAL):
        // admin_satker can only see data from their own satker.
        if ($user->isAdminSatker()) {
            $query->where('satker_id', $user->satker_id);
        } elseif ($request->filled('satker_id')) {
            // Super admin can filter by satker from UI.
            $query->where('satker_id', (int) $request->input('satker_id'));
        }

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

        $satkers = $user->isSuperAdmin()
            ? Satker::query()->orderBy('nama_satker')->get()
            : collect();

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
        $user = request()->user();

        return view('pegawai.create', [
            'satkers' => Satker::query()->orderBy('nama_satker')->get(),
            'user' => $user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:50', 'unique:pegawais,nik'],
            'pendidikan' => ['required', 'string', 'max:100'],
            'satker_id' => ['nullable', 'integer', 'exists:satkers,id'],
            'status' => ['required', 'in:aktif,non_aktif'],
        ]);

        if ($user->isAdminSatker()) {
            $validated['satker_id'] = $user->satker_id;
        } else {
            // super_admin must pick satker.
            if (empty($validated['satker_id'])) {
                return back()
                    ->withErrors(['satker_id' => 'Satker wajib dipilih.'])
                    ->withInput();
            }
        }

        Pegawai::create($validated);

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Pegawai berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pegawai $pegawai)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pegawai $pegawai)
    {
        $user = request()->user();

        return view('pegawai.edit', [
            'pegawai' => $pegawai->load('satker'),
            'satkers' => Satker::query()->orderBy('nama_satker')->get(),
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:50', 'unique:pegawais,nik,'.$pegawai->id],
            'pendidikan' => ['required', 'string', 'max:100'],
            'satker_id' => ['nullable', 'integer', 'exists:satkers,id'],
            'status' => ['required', 'in:aktif,non_aktif'],
        ]);

        if ($user->isAdminSatker()) {
            // Admin satker cannot move pegawai across satkers.
            $validated['satker_id'] = $user->satker_id;
        } else {
            if (empty($validated['satker_id'])) {
                return back()
                    ->withErrors(['satker_id' => 'Satker wajib dipilih.'])
                    ->withInput();
            }
        }

        $pegawai->update($validated);

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();

        return redirect()
            ->route('pegawai.index')
            ->with('success', 'Pegawai berhasil dihapus.');
    }
}
