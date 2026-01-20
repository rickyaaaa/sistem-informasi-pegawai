<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Satker') }}
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

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-muted">
                    Total: {{ $satkers->total() }}
                </div>
                <a href="{{ route('satker.create') }}" class="btn btn-primary">
                    Tambah Satker
                </a>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 90px;">#</th>
                                    <th>Nama Satker</th>
                                    <th style="width: 180px;" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($satkers as $satker)
                                    <tr>
                                        <td>{{ ($satkers->currentPage() - 1) * $satkers->perPage() + $loop->iteration }}</td>
                                        <td>{{ $satker->nama_satker }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('satker.edit', $satker) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>
                                            <form action="{{ route('satker.destroy', $satker) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus satker ini?');">
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
                                        <td colspan="3" class="text-center text-muted py-4">
                                            Belum ada data satker.
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
        </div>
    </div>
</x-app-layout>

