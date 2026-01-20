<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('pegawai.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Cari (Nama / NIK)</label>
                            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Contoh: Budi / 3276...">
                        </div>

                        @if(auth()->user()?->isSuperAdmin())
                            <div class="col-md-5">
                                <label class="form-label">Filter Satker</label>
                                <select name="satker_id" class="form-select">
                                    <option value="">-- Semua Satker --</option>
                                    @foreach($satkers as $satker)
                                        <option value="{{ $satker->id }}" @selected((string)$selectedSatkerId === (string)$satker->id)>
                                            {{ $satker->nama_satker }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Terapkan</button>
                            <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    Total: {{ $pegawais->total() }}
                </div>
                <a href="{{ route('pegawai.create') }}" class="btn btn-primary">
                    Tambah Pegawai
                </a>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 90px;">#</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Pendidikan</th>
                                    <th>Satker</th>
                                    <th>Status</th>
                                    <th style="width: 180px;" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pegawais as $pegawai)
                                    <tr>
                                        <td>{{ ($pegawais->currentPage() - 1) * $pegawais->perPage() + $loop->iteration }}</td>
                                        <td>{{ $pegawai->nama }}</td>
                                        <td>{{ $pegawai->nik }}</td>
                                        <td>{{ $pegawai->pendidikan }}</td>
                                        <td>{{ $pegawai->satker?->nama_satker ?? '-' }}</td>
                                        <td>
                                            @if($pegawai->status === 'aktif')
                                                <span class="badge text-bg-success">Aktif</span>
                                            @else
                                                <span class="badge text-bg-secondary">Non Aktif</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('pegawai.edit', $pegawai) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            <form action="{{ route('pegawai.destroy', $pegawai) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus data pegawai ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Belum ada data pegawai.
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
        </div>
    </div>
</x-app-layout>

