<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\SatkerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\UserController;

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
| Super Admin Only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->group(function () {

    Route::resource('satker', SatkerController::class)
        ->except(['show']);

    // ── User Management ────────────────────────────────────────
    Route::resource('users', UserController::class)
        ->except(['show']);

    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');

    // ── Approval workflow ──────────────────────────────────────
    Route::get('/approvals', [ApprovalController::class, 'index'])
        ->name('approval.index');

    Route::post('/approvals/{approvalRequest}/approve', [ApprovalController::class, 'approve'])
        ->name('approval.approve');

    Route::post('/approvals/{approvalRequest}/reject', [ApprovalController::class, 'reject'])
        ->name('approval.reject');
});

/*
|--------------------------------------------------------------------------
| Pegawai (Super Admin & Admin Satker)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin,admin_satker'])->group(function () {

    Route::resource('pegawai', PegawaiController::class);

    // AJAX: get sub-units for dependent dropdown
    Route::get('/api/get-sub-satker/{id}', [PegawaiController::class, 'getSubSatker'])
        ->name('api.sub-satker');

    // Export Excel
    Route::get('/pegawai-export', [PegawaiController::class, 'export'])
        ->name('pegawai.export');

    // Preview KTP / KK
    Route::get('/pegawai/{pegawai}/file/{type}',
        [PegawaiController::class, 'showFile']
    )->name('pegawai.file.show');

    // Download KTP / KK
    Route::get('/pegawai/{pegawai}/file/{type}/download',
        [PegawaiController::class, 'downloadFile']
    )->name('pegawai.file.download');
});

require __DIR__.'/auth.php';
