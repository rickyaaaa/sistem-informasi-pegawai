@extends('layouts.admin')

@section('title', 'Tambah Satker')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                <i class="bi bi-building-fill-add"></i>
                <h5 class="mb-0">Form Tambah Satker</h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('satker.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="nama_satker" class="form-label fw-semibold">Nama Satker</label>
                        <input
                            type="text"
                            id="nama_satker"
                            name="nama_satker"
                            value="{{ old('nama_satker') }}"
                            class="form-control form-control-lg @error('nama_satker') is-invalid @enderror"
                            placeholder="Contoh: Bagian Kepegawaian"
                            autofocus
                        >
                        @error('nama_satker')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('satker.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
