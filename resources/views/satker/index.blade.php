@extends('layouts.admin')

@section('title', 'Data Satker')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Daftar Satker</h5>
        <small class="text-muted">Total {{ $satkers->total() }} satker terdaftar</small>
    </div>
    <a href="{{ route('satker.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Tambah Satker
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Nama Satker</th>
                        <th class="text-end" style="width:160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($satkers as $satker)
                        <tr>
                            <td class="text-muted">
                                {{ ($satkers->currentPage() - 1) * $satkers->perPage() + $loop->iteration }}
                            </td>
                            <td class="fw-medium">{{ $satker->nama_satker }}</td>
                            <td class="text-end">
                                <a href="{{ route('satker.edit', $satker) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('satker.destroy', $satker) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus satker ini?')">
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
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="bi bi-building fs-2 d-block mb-2 opacity-50"></i>
                                Belum ada data satker
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $satkers->links() }}
</div>

@endsection
