<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\SatkerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\ProgramStudiController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated User
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Polda Only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->group(function () {

    Route::delete('/satker-bulk', [SatkerController::class, 'bulkDestroy'])
        ->name('satker.bulk-destroy');

    Route::resource('satker', SatkerController::class)
        ->except(['show']);

    // ── Arsip Pegawai ──────────────────────────────────────────
    Route::get('/pegawai/arsip', [PegawaiController::class, 'arsip'])->name('pegawai.arsip');
    Route::post('/pegawai/{id}/restore', [PegawaiController::class, 'restore'])->name('pegawai.restore');
    Route::delete('/pegawai/{id}/force-delete', [PegawaiController::class, 'forceDelete'])->name('pegawai.force_delete');

    // ── User Management ────────────────────────────────────────
    Route::delete('/users-bulk', [UserController::class, 'bulkDestroy'])
        ->name('users.bulk-destroy');

    Route::resource('users', UserController::class)
        ->except(['show']);

    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');

    // ── Approval workflow ──────────────────────────────────────
    Route::get('/approvals', [ApprovalController::class, 'index'])
        ->name('approval.index');

    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/approvals/{approvalRequest}/approve', [ApprovalController::class, 'approve'])
            ->name('approval.approve');

        Route::post('/approvals/{approvalRequest}/reject', [ApprovalController::class, 'reject'])
            ->name('approval.reject');
    });


    // ── Program Studi ─────────────────────────────────
    Route::resource('prodi', ProgramStudiController::class)
        ->except(['show']);
});

/*
|--------------------------------------------------------------------------
| Pegawai (Admin Polda & Admin Satker)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin,admin_satker'])->group(function () {

    // Import/Export/Template — MUST be before resource route
    Route::post('/pegawai/import', [PegawaiController::class, 'import'])
        ->name('pegawai.import');

    Route::get('/pegawai/template', [PegawaiController::class, 'downloadTemplate'])
        ->name('pegawai.template');

    Route::get('/pegawai-export', [PegawaiController::class, 'export'])
        ->name('pegawai.export');

    Route::resource('pegawai', PegawaiController::class);

    // AJAX: get sub-units for dependent dropdown
    Route::get('/api/get-sub-satker/{id}', [PegawaiController::class, 'getSubSatker'])
        ->name('api.sub-satker');

    // AJAX: get prodi by kategori for dynamic dropdown (query param to handle SMA/SMK slash)
    Route::get('/api/get-prodi', [PegawaiController::class, 'getProdiByKategori'])
        ->name('api.prodi');

    // Preview KTP / KK
    Route::get(
        '/pegawai/{pegawai}/file/{type}',
        [PegawaiController::class, 'showFile']
    )->name('pegawai.file.show');

    // Download KTP / KK
    Route::get(
        '/pegawai/{pegawai}/file/{type}/download',
        [PegawaiController::class, 'downloadFile']
    )->name('pegawai.file.download');
});

require __DIR__ . '/auth.php';
