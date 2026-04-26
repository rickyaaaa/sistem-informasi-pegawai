@extends('layouts.admin')

@section('title', 'DATA PEGAWAI')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Daftar Pegawai</h5>
        <small class="text-muted">Total {{ $pegawais->total() }} pegawai terdaftar</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pegawai.export') }}" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-arrow-down me-1"></i> Export Excel
        </a>
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel
        </button>
        <a href="{{ route('pegawai.create') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pegawai
        </a>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('import_errors'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-x-circle-fill me-1"></i> Detail Error Import:</strong>
        <ul class="mb-0 mt-2" style="max-height:200px;overflow-y:auto;font-size:13px;">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Info banner for admin_satker --}}
@if(auth()->user()->isAdminSatker())
    <div class="alert border-0 mb-4 d-flex align-items-start gap-3"
         style="background:#fef2f2;border-left:4px solid #dc2626 !important;border-radius:10px;padding:14px 18px;">
        <i class="bi bi-info-circle-fill text-danger mt-1" style="font-size:18px;flex-shrink:0;"></i>
        <div>
            <div class="fw-semibold text-danger mb-1" style="font-size:14px;">Mode Pengajuan Aktif</div>
            <div class="text-secondary" style="font-size:13px;">
                Setiap tindakan <strong>tambah, ubah, atau hapus</strong> pegawai akan dikirim sebagai permintaan
                dan memerlukan persetujuan <strong>ADMIN POLDA</strong> sebelum diterapkan ke sistem.
            </div>
        </div>
    </div>
@endif

{{-- Search + Filter --}}
<form method="GET" action="{{ route('pegawai.index') }}" class="mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ $q ?? '' }}"
                   class="form-control form-control-sm"
                   placeholder="Cari nama atau NIK...">
        </div>
        <div class="col-md-4">
            <select name="satker_id" class="form-select form-select-sm">
                <option value="">-- SEMUA SATKER/SATWIL --</option>
                @foreach($satkers as $satker)
                    <option value="{{ $satker->id }}"
                        {{ ($selectedSatkerId ?? '') == $satker->id ? 'selected' : '' }}>
                        {{ $satker->nama_satker }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-danger flex-grow-1">
                <i class="bi bi-search me-1"></i> Filter
            </button>
            @if(request()->filled('q') || request()->filled('satker_id'))
                <a href="{{ route('pegawai.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</form>

{{-- Table --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr class="text-uppercase">
                        <th style="width:40px;" class="text-center">
                            <input type="checkbox" class="form-check-input" disabled>
                        </th>
                        <th style="width:50px;">NO</th>
                        <th>NAMA</th>
                        <th>NIK</th>
                        <th>PENDIDIKAN</th>
                        <th>PRODI</th>
                        <th>SATKER/SATWIL</th>
                        <th>SUB/BAG</th>
                        <th class="text-end" style="width:120px;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawais as $pegawai)
                        <tr>
                            {{-- Checkbox --}}
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" disabled>
                            </td>

                            {{-- NO --}}
                            <td class="text-muted">
                                {{ ($pegawais->currentPage() - 1) * $pegawais->perPage() + $loop->iteration }}
                            </td>

                            {{-- NAMA --}}
                            <td class="fw-bold">{{ strtoupper($pegawai->nama) }}</td>

                            {{-- NIK --}}
                            <td>{{ $pegawai->nik }}</td>

                            {{-- PENDIDIKAN --}}
                            <td>{{ strtoupper($pegawai->pendidikan) }}</td>

                            {{-- PRODI --}}
                            <td>{{ strtoupper($pegawai->prodi->nama ?? '-') }}</td>

                            {{-- SATKER/SATWIL dan SUB --}}
                            @php
                                $isInduk = empty($pegawai->satker->parent_id);
                                $indukName = $isInduk ? ($pegawai->satker->nama_satker ?? '-') : ($pegawai->satker->parent->nama_satker ?? '-');
                                $subName = $isInduk ? '-' : ($pegawai->satker->nama_satker ?? '-');
                            @endphp
                            <td>{{ strtoupper($indukName) }}</td>
                            <td class="text-muted" style="font-size: 13px;">{{ strtoupper($subName) }}</td>

                            <td class="text-end" style="white-space:nowrap;">
                                <a href="{{ route('pegawai.show', $pegawai) }}"
                                   class="text-danger mx-1" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('pegawai.edit', $pegawai) }}"
                                   class="text-success mx-1"
                                   title="{{ auth()->user()->isAdminSatker() ? 'Ajukan perubahan data' : 'Edit' }}">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                @if(auth()->user()->isSuperAdmin())
                                <form action="{{ route('pegawai.destroy', $pegawai) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus pegawai ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger mx-1 border-0 bg-transparent" title="Hapus">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
                                Belum ada data pegawai
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-3">
    {{ $pegawais->links() }}
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Data Pegawai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pegawai.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Upload File Excel</label>
                        <input type="file" name="file" class="form-control"
                               accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Format: .xlsx, .xls, atau .csv. Maks 5MB.</small>
                    </div>

                    <div class="alert alert-info border-0 py-2 px-3" style="font-size:13px;">
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Tips:</strong> Download template terlebih dahulu agar format kolom sesuai.
                        <br>
                        <a href="{{ route('pegawai.template') }}" class="fw-semibold">
                            <i class="bi bi-download me-1"></i> Download Template Excel
                        </a>
                    </div>

                    <div style="font-size:12px;" class="text-muted">
                        <strong>Kolom wajib:</strong> nama, nik, jenis_kelamin, pendidikan, satker, status<br>
                        <strong>NIK:</strong> Harus 16 digit angka<br>
                        <strong>Satker:</strong> Isi nama Sub-Bagian yang terdaftar di sistem<br>
                        <strong>Upsert:</strong> Jika NIK sudah ada, data akan di-<em>update</em>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

