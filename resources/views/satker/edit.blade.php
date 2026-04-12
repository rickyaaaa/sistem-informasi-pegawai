@extends('layouts.admin')

@section('title', 'Edit Satker/Satwil')

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm">
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
                        @if($satker->parent_id || !$satker->children->isEmpty() === false)
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

                        {{-- Info sub-unit yang sudah ada --}}
                        @if($satker->children->count() > 0)
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    Sub-Unit Terdaftar
                                    <span class="badge bg-secondary ms-1">{{ $satker->children->count() }}</span>
                                </label>
                                <div class="border rounded p-3 bg-light" style="max-height:200px; overflow-y:auto;">
                                    @foreach($satker->children->sortBy('nama_satker') as $child)
                                        <div class="small text-muted">
                                            <i class="bi bi-arrow-return-right me-1"></i>{{ $child->nama_satker }}
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-text">Untuk edit/hapus sub-unit, gunakan aksi di halaman daftar.</div>
                            </div>
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

        </div>
    </div>

@endsection