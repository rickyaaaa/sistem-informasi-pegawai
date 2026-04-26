@extends('layouts.admin')

@section('title', 'ARSIP PEGAWAI')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Arsip Pegawai (Soft Delete)</h5>
        <small class="text-muted">Total {{ $pegawais->total() }} pegawai diarsipkan.</small>
    </div>
    <div>
        <a href="{{ route('pegawai.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pegawai
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

{{-- Search --}}
<form method="GET" action="{{ route('pegawai.arsip') }}" class="mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ request('q') }}"
                   class="form-control form-control-sm"
                   placeholder="Cari nama atau NIK...">
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-danger flex-grow-1">
                <i class="bi bi-search me-1"></i> Cari
            </button>
            @if(request()->filled('q'))
                <a href="{{ route('pegawai.arsip') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th style="width:50px;">NO</th>
                        <th>NAMA</th>
                        <th>NIK</th>
                        <th>PENDIDIKAN</th>
                        <th>SATKER/SATWIL</th>
                        <th>TGL HAPUS</th>
                        <th class="text-end" style="width:150px;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawais as $pegawai)
                        <tr>
                            <td class="text-muted">
                                {{ ($pegawais->currentPage() - 1) * $pegawais->perPage() + $loop->iteration }}
                            </td>
                            <td class="fw-bold">{{ strtoupper($pegawai->nama) }}</td>
                            <td>{{ $pegawai->nik }}</td>
                            <td>{{ strtoupper($pegawai->pendidikan) }}</td>
                            @php
                                $isInduk = empty($pegawai->satker->parent_id);
                                $indukName = $isInduk ? ($pegawai->satker->nama_satker ?? '-') : ($pegawai->satker->parent->nama_satker ?? '-');
                            @endphp
                            <td>{{ strtoupper($indukName) }}</td>
                            <td>{{ $pegawai->deleted_at ? $pegawai->deleted_at->format('d/m/Y H:i') : '-' }}</td>

                            <td class="text-end" style="white-space:nowrap;">
                                <!-- Restore -->
                                <form action="{{ route('pegawai.restore', $pegawai->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin memulihkan data pegawai ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success mx-1" title="Pulihkan">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                </form>

                                <!-- Force Delete -->
                                <form action="{{ route('pegawai.force_delete', $pegawai->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus permanen pegawai ini? Data tidak bisa dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mx-1" title="Hapus Permanen">
                                        <i class="bi bi-trash"></i> Permanen
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-archive fs-2 d-block mb-2 opacity-50"></i>
                                Belum ada data di arsip.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $pegawais->links() }}
</div>

@endsection
