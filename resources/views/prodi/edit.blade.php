@extends('layouts.admin')

@section('title', 'EDIT PROGRAM STUDI')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i>
                <span>Edit Program Studi: {{ $prodi->nama }}</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('prodi.update', $prodi) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Umum" {{ old('kategori', $prodi->kategori) === 'Umum' ? 'selected' : '' }}>Umum (SD/SMP)</option>
                            <option value="SMA/SMK" {{ old('kategori', $prodi->kategori) === 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                            <option value="Perguruan Tinggi" {{ old('kategori', $prodi->kategori) === 'Perguruan Tinggi' ? 'selected' : '' }}>Perguruan Tinggi (D3, S1, S2, S3)</option>
                        </select>
                        @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Program Studi <span class="text-danger">*</span></label>
                        <input type="text" name="nama"
                               class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $prodi->nama) }}" required autofocus>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('prodi.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-save me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
