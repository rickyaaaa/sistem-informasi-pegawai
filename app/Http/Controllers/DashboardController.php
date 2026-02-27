<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Satker;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
public function index(Request $request)
{
    $user = $request->user();

    $pegawaiQuery = Pegawai::query();

    if ($user->isAdminSatker()) {
        $pegawaiQuery->where('satker_id', $user->satker_id);
    }

    $totalPegawai = $pegawaiQuery->count();

    $pegawaiAktif = (clone $pegawaiQuery)
        ->where('status', 'aktif')
        ->count();

    $pegawaiNonAktif = (clone $pegawaiQuery)
        ->where('status', 'non_aktif')
        ->count();

    $totalSatker = $user->isSuperAdmin()
        ? Satker::count()
        : 1;


    $pegawaiPerSatker = Satker::withCount('pegawais')->get();

    $pendidikanStats = Pegawai::selectRaw('pendidikan, COUNT(*) as total')
        ->groupBy('pendidikan')
        ->pluck('total', 'pendidikan');

    return view('admin.dashboard', compact(
        'totalPegawai',
        'pegawaiAktif',
        'pegawaiNonAktif',
        'totalSatker',
        'pegawaiPerSatker',
        'pendidikanStats'
    ));
}
}
