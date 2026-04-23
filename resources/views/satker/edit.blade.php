@extends('layouts.admin')

@section('title', 'Edit Satker/Satwil')

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

            {{-- Form Edit Induk --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square"></i>
                    <h5 class="mb-0">Form Edit Satker/Satwil</h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('satker.update', $satker) }}">
                        @csrf
                        @method('PUT')

                        {{-- Tipe Satuan --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Tipe Satuan</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_satuan" id="tipe_satker"
                                        value="satker" {{ old('tipe_satuan', $satker->tipe_satuan) === 'satker' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipe_satker">Satker</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_satuan" id="tipe_satwil"
                                        value="satwil" {{ old('tipe_satuan', $satker->tipe_satuan) === 'satwil' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipe_satwil">Satwil</label>
                                </div>
                            </div>
                        </div>

                        {{-- Nama --}}
                        <div class="mb-4">
                            <label for="nama_satker" class="form-label fw-semibold">Nama Satker/Satwil</label>
                            <input type="text" id="nama_satker" name="nama_satker"
                                value="{{ old('nama_satker', $satker->nama_satker) }}"
                                class="form-control form-control-lg @error('nama_satker') is-invalid @enderror"
                                placeholder="Contoh: BIRO SDM / POLRES METRO" autofocus>
                            @error('nama_satker')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Satker Induk (hanya untuk sub) --}}
                        @if($satker->parent_id)
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Satker Induk <span
                                        class="text-muted fw-normal">(opsional)</span></label>
                                <select name="parent_id" class="form-select">
                                    <option value="">-- Tidak Ada / Satker Utama --</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_id', $satker->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->nama_satker }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="parent_id" value="">
                        @endif

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('satker.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sub-Unit Manager (hanya tampil jika satker ini adalah INDUK) --}}
            @if(is_null($satker->parent_id))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3 text-primary"></i>
                            <h6 class="mb-0 fw-bold">Sub-Unit Terdaftar</h6>
                            <span class="badge bg-secondary ms-1">{{ $satker->children->count() }}</span>
                        </div>
                        {{-- Tombol Tambah Sub-Unit: arahkan ke form create dengan parent_id otomatis --}}
                        <a href="{{ route('satker.create') }}?parent_id={{ $satker->id }}"
                           class="btn btn-sm btn-success"
                           id="btn-tambah-sub-unit">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Sub-Unit
                        </a>
                    </div>

                    <div class="card-body p-0">
                        @if($satker->children->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                Belum ada sub-unit untuk satker ini.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small text-uppercase text-muted">
                                        <tr>
                                            <th style="width:50px;" class="ps-3">#</th>
                                            <th>Nama Sub-Unit</th>
                                            <th style="width:150px;" class="text-end pe-3">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($satker->children->sortBy('nama_satker') as $i => $child)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                                <td>
                                                    <i class="bi bi-arrow-return-right text-muted me-1"></i>
                                                    <span class="fw-semibold">{{ $child->nama_satker }}</span>
                                                </td>
                                                <td class="text-end pe-3">
                                                    {{-- Edit Sub-Unit --}}
                                                    <a href="{{ route('satker.edit', $child) }}"
                                                       class="btn btn-outline-secondary btn-sm"
                                                       title="Edit {{ $child->nama_satker }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    {{-- Hapus Sub-Unit --}}
                                                    <form action="{{ route('satker.destroy', $child) }}"
                                                          method="POST"
                                                          class="d-inline form-delete-sub"
                                                          data-nama="{{ $child->nama_satker }}"
                                                          data-pegawai="{{ $child->pegawais()->count() }}"
                                                          data-users="{{ $child->users()->count() }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm btn-delete-sub"
                                                                title="Hapus {{ $child->nama_satker }}">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Modal Konfirmasi Hapus Sub-Unit --}}
    <div class="modal fade" id="modalDeleteSub" tabindex="-1" aria-labelledby="modalDeleteSubLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalDeleteSubLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Sub-Unit
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menghapus sub-unit: <strong id="modal-sub-nama"></strong></p>
                    <div id="modal-warning-data" class="alert alert-danger d-none">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i>
                        <strong>Peringatan Keras!</strong><br>
                        Sub-unit ini masih memiliki data:
                        <ul class="mb-0 mt-1" id="modal-warning-list"></ul>
                        <p class="mt-2 mb-0 small">Menghapus sub-unit ini akan <strong>menghapus permanen</strong> seluruh data pegawai dan operator yang terkait.</p>
                    </div>
                    <div id="modal-warning-safe" class="alert alert-warning d-none">
                        <i class="bi bi-info-circle me-1"></i>
                        Sub-unit tidak memiliki data pegawai atau operator. Anda dapat menghapusnya dengan aman.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btn-confirm-delete-sub">
                        <i class="bi bi-trash3 me-1"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // ── Tombol "Tambah Sub-Unit": set parent_id di URL ke form create ──
    // (sudah di-handle via href di blade, tidak perlu JS tambahan)

    // ── Delete Sub-Unit dengan Modal Konfirmasi ────────────────────────
    let activeDeleteForm = null;

    document.querySelectorAll('.btn-delete-sub').forEach(btn => {
        btn.addEventListener('click', function () {
            const form     = this.closest('.form-delete-sub');
            const nama     = form.dataset.nama;
            const pegawai  = parseInt(form.dataset.pegawai, 10);
            const users    = parseInt(form.dataset.users, 10);
            activeDeleteForm = form;

            document.getElementById('modal-sub-nama').textContent = nama;

            const warnData = document.getElementById('modal-warning-data');
            const warnSafe = document.getElementById('modal-warning-safe');
            const warnList = document.getElementById('modal-warning-list');

            if (pegawai > 0 || users > 0) {
                warnList.innerHTML = '';
                if (pegawai > 0) warnList.innerHTML += `<li>${pegawai} data pegawai</li>`;
                if (users > 0)   warnList.innerHTML += `<li>${users} akun operator/user</li>`;
                warnData.classList.remove('d-none');
                warnSafe.classList.add('d-none');
            } else {
                warnData.classList.add('d-none');
                warnSafe.classList.remove('d-none');
            }

            new bootstrap.Modal(document.getElementById('modalDeleteSub')).show();
        });
    });

    document.getElementById('btn-confirm-delete-sub')?.addEventListener('click', function () {
        if (activeDeleteForm) {
            activeDeleteForm.submit();
        }
    });
</script>
@endpush