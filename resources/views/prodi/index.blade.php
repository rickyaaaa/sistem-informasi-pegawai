@extends('layouts.admin')

@section('title', 'PROGRAM STUDI')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Daftar Program Studi</h5>
        <small class="text-muted">Total {{ $prodis->total() }} program studi terdaftar</small>
    </div>
    <a href="{{ route('prodi.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Tambah Program Studi
    </a>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('prodi.index') }}" class="mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ request('q') }}"
                   class="form-control form-control-sm"
                   placeholder="Cari nama program studi...">
        </div>
        <div class="col-md-3">
            <select name="kategori" class="form-select form-select-sm">
                <option value="">-- Semua Kategori --</option>
                <option value="Umum" {{ request('kategori') === 'Umum' ? 'selected' : '' }}>Umum (SD/SMP)</option>
                <option value="SMA/SMK" {{ request('kategori') === 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                <option value="Perguruan Tinggi" {{ request('kategori') === 'Perguruan Tinggi' ? 'selected' : '' }}>Perguruan Tinggi</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-danger flex-grow-1">
                <i class="bi bi-search"></i>
            </button>
            @if(request()->hasAny(['q','kategori']))
                <a href="{{ route('prodi.index') }}" class="btn btn-sm btn-outline-secondary">
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
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Nama Program Studi</th>
                        <th style="width:180px;">Kategori</th>
                        <th style="width:100px;">Pegawai</th>
                        <th class="text-end" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prodis as $prodi)
                        <tr>
                            <td class="text-muted">
                                {{ ($prodis->currentPage() - 1) * $prodis->perPage() + $loop->iteration }}
                            </td>
                            <td class="fw-medium">{{ $prodi->nama }}</td>
                            <td>
                                @if($prodi->kategori === 'Perguruan Tinggi')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Perguruan Tinggi</span>
                                @elseif($prodi->kategori === 'SMA/SMK')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">SMA/SMK</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $prodi->kategori }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $prodi->pegawais()->count() }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('prodi.edit', $prodi) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('prodi.destroy', $prodi) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus program studi {{ $prodi->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-journal-x fs-2 d-block mb-2 opacity-50"></i>
                                Belum ada data program studi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $prodis->links() }}
</div>

@endsection
