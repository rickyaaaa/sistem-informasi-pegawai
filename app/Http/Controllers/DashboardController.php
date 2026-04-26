<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Satker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ── Statistik (dengan Caching & Single Query) ─────────
        $stats = Cache::remember("dashboard_stats_{$user->id}", now()->addMinutes(2), function () use ($user) {
            $baseQuery = Pegawai::query()
                ->join('satkers as s', 'pegawais.satker_id', '=', 's.id')
                ->leftJoin('satkers as induk', function ($join) {
                    // If pegawai is in a sub-unit, join to its parent (the induk).
                    // If pegawai is already in an induk satker, self-join.
                    $join->on('induk.id', '=', 's.parent_id')
                         ->orWhere(function ($q) {
                             $q->whereNull('s.parent_id')
                               ->whereRaw('induk.id = s.id');
                         });
                });

            if ($user->isAdminSatker()) {
                $allowedIds = Satker::where('parent_id', $user->satker_id)
                    ->orWhere('id', $user->satker_id)
                    ->pluck('id');
                $baseQuery->whereIn('pegawais.satker_id', $allowedIds);
            }

            return $baseQuery->selectRaw("
                COUNT(pegawais.id) as total,
                SUM(CASE WHEN pegawais.status = 'aktif' THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN pegawais.status = 'non_aktif' THEN 1 ELSE 0 END) as non_aktif,
                SUM(CASE WHEN pegawais.jenis_kelamin = 'Pria' THEN 1 ELSE 0 END) as pria,
                SUM(CASE WHEN pegawais.jenis_kelamin = 'Wanita' THEN 1 ELSE 0 END) as wanita,
                SUM(CASE WHEN induk.tipe_satuan = 'satker' THEN 1 ELSE 0 END) as satker_total,
                SUM(CASE WHEN induk.tipe_satuan = 'satker' AND pegawais.jenis_kelamin = 'Pria' THEN 1 ELSE 0 END) as satker_pria,
                SUM(CASE WHEN induk.tipe_satuan = 'satker' AND pegawais.jenis_kelamin = 'Wanita' THEN 1 ELSE 0 END) as satker_wanita,
                SUM(CASE WHEN induk.tipe_satuan = 'satwil' THEN 1 ELSE 0 END) as satwil_total,
                SUM(CASE WHEN induk.tipe_satuan = 'satwil' AND pegawais.jenis_kelamin = 'Pria' THEN 1 ELSE 0 END) as satwil_pria,
                SUM(CASE WHEN induk.tipe_satuan = 'satwil' AND pegawais.jenis_kelamin = 'Wanita' THEN 1 ELSE 0 END) as satwil_wanita
            ")->first();
        });

        $totalPegawai        = (int) $stats->total;
        $pegawaiAktif        = (int) $stats->aktif;
        $pegawaiNonAktif     = (int) $stats->non_aktif;
        $pegawaiPria         = (int) $stats->pria;
        $pegawaiWanita       = (int) $stats->wanita;
        $pegawaiSatkerTotal  = (int) $stats->satker_total;
        $pegawaiSatkerPria   = (int) $stats->satker_pria;
        $pegawaiSatkerWanita = (int) $stats->satker_wanita;
        $pegawaiSatwilTotal  = (int) $stats->satwil_total;
        $pegawaiSatwilPria   = (int) $stats->satwil_pria;
        $pegawaiSatwilWanita = (int) $stats->satwil_wanita;

        $pendidikanStats = Cache::remember("dashboard_pendidikan_{$user->id}", now()->addMinutes(2), function () use ($user) {
            $query = Pegawai::query();
            if ($user->isAdminSatker()) {
                $allowedIds = Satker::where('parent_id', $user->satker_id)
                    ->orWhere('id', $user->satker_id)
                    ->pluck('id');
                $query->whereIn('satker_id', $allowedIds);
            }
            return $query->selectRaw('pendidikan, COUNT(*) as total')
                ->groupBy('pendidikan')
                ->pluck('total', 'pendidikan');
        });

        // ── Satker list untuk dropdown (hanya induk) ──────────────
        $satkers = Satker::where('level', 'induk')->orderBy('nama_satker')->get();

        // ── Daftar pendidikan yang tersedia ───────────────────────
        $pendidikanList = ['SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S1 Profesi', 'S2', 'S2 Profesi', 'S3'];

        // ── Pencarian ─────────────────────────────────────────────
        $searchResults  = null;
        $searchPerformed = false;

        $hasInput = $request->filled('q')
            || $request->filled('satker_id_search')
            || $request->filled('pendidikan_search')
            || $request->filled('usia_max');

        if ($hasInput) {
            $searchPerformed = true;

            $q = Pegawai::query()->with('satker.parent');

            // Admin Satker hanya bisa lihat satker sendiri dan sub-unitnya
            if ($user->isAdminSatker()) {
                $allowedIds = Satker::where('parent_id', $user->satker_id)
                    ->orWhere('id', $user->satker_id)
                    ->pluck('id');
                $q->whereIn('satker_id', $allowedIds);
            }

            // Filter nama / NIK
            if ($request->filled('q')) {
                $keyword = trim($request->input('q'));
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('nik', 'like', "%{$keyword}%");
                });
            }

            // Filter satker (Induk + Sub-unitnya)
            if ($request->filled('satker_id_search')) {
                $satkerId = (int) $request->input('satker_id_search');
                $allowedIds = Satker::where('id', $satkerId)
                                    ->orWhere('parent_id', $satkerId)
                                    ->pluck('id');
                $q->whereIn('satker_id', $allowedIds);
            }

            // Filter pendidikan (bisa multi-pilih)
            if ($request->filled('pendidikan_search')) {
                $pendList = (array) $request->input('pendidikan_search');
                $q->whereIn('pendidikan', $pendList);
            }

            // Filter usia maksimal (berdasarkan tgl_lahir)
            if ($request->filled('usia_max')) {
                $usiaMax = (int) $request->input('usia_max');
                $batas   = Carbon::now()->subYears($usiaMax)->toDateString();
                $q->where('tgl_lahir', '>=', $batas);
            }

            // Determine per_page limit
            $perPage = $request->input('per_page', 10);
            if ($perPage === 'all') {
                $perPage = $q->count() > 0 ? $q->count() : 1;
            } else {
                $perPage = (int) $perPage;
            }

            $searchResults = $q->orderBy('nama')->paginate($perPage)->withQueryString();
        }

        return view('admin.dashboard', compact(
            'totalPegawai',
            'pegawaiAktif',
            'pegawaiNonAktif',
            'pegawaiPria',
            'pegawaiWanita',
            'pegawaiSatkerTotal',
            'pegawaiSatkerPria',
            'pegawaiSatkerWanita',
            'pegawaiSatwilTotal',
            'pegawaiSatwilPria',
            'pegawaiSatwilWanita',
            'pendidikanStats',
            'satkers',
            'pendidikanList',
            'searchResults',
            'searchPerformed'
        ));
    }

    /**
     * Clear dashboard cache for the current user and redirect back.
     * Used by the "Refresh Data" button on the dashboard.
     */
    public function refreshCache(Request $request)
    {
        $userId = $request->user()->id;
        Cache::forget("dashboard_stats_{$userId}");
        Cache::forget("dashboard_pendidikan_{$userId}");

        return redirect()->route('dashboard')->with('success', '✅ Data berhasil diperbarui.');
    }
}
