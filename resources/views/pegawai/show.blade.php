@extends('layouts.admin')

@section('title', 'Detail Pegawai')

@section('content')

<div class="mb-4">
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-4">

    {{-- Left: Profile Card --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body py-5">
                {{-- Foto --}}
                @if($pegawai->foto)
                    <img src="{{ asset('storage/' . $pegawai->foto) }}"
                         alt="Foto {{ $pegawai->nama }}"
                         style="width:130px;height:130px;border-radius:50%;object-fit:cover;border:4px solid #e5e7eb;margin-bottom:16px;">
                @else
                    <div style="width:130px;height:130px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:48px;margin-bottom:16px;border:4px solid #e5e7eb;">
                        {{ strtoupper(substr($pegawai->nama, 0, 1)) }}
                    </div>
                @endif

                <h5 class="fw-bold mb-1">{{ $pegawai->nama }}</h5>
                <p class="text-muted mb-3" style="font-size:14px;">{{ $pegawai->nik }}</p>

                @if($pegawai->status === 'aktif')
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2" style="font-size:13px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Aktif
                    </span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2" style="font-size:13px;">
                        <i class="bi bi-x-circle-fill me-1"></i> Non Aktif
                    </span>
                @endif

                {{-- Change Photo Form --}}
                <div class="mt-4 pt-3 border-top">
                    <form action="{{ route('pegawai.update', $pegawai) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Hidden fields to preserve existing data --}}
                        <input type="hidden" name="_redirect_show" value="1">
                        <input type="hidden" name="nama" value="{{ $pegawai->nama }}">
                        <input type="hidden" name="nik" value="{{ $pegawai->nik }}">
                        <input type="hidden" name="jenis_kelamin" value="{{ $pegawai->jenis_kelamin }}">
                        <input type="hidden" name="pendidikan" value="{{ $pegawai->pendidikan }}">
                        <input type="hidden" name="satker_id" value="{{ $pegawai->satker_id }}">
                        <input type="hidden" name="status" value="{{ $pegawai->status }}">

                        <label class="btn btn-outline-primary btn-sm w-100 mb-2" for="fotoChangeInput">
                            <i class="bi bi-camera me-1"></i> Ganti Foto
                        </label>
                        <input type="file" name="foto" id="fotoChangeInput" class="d-none" accept=".jpg,.jpeg,.png" onchange="this.form.submit()">
                    </form>
                </div>
            </div>
        </div>

        {{-- Actions Card --}}
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <a href="{{ route('pegawai.edit', $pegawai) }}" class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="bi bi-pencil-square me-1"></i> Edit Pegawai
                </a>
                <form action="{{ route('pegawai.destroy', $pegawai) }}" method="POST"
                      onsubmit="return confirm('{{ auth()->user()->isAdminSatker()
                            ? 'Permintaan hapus akan dikirim ke Super Admin untuk persetujuan. Lanjutkan?'
                            : 'Yakin ingin menghapus pegawai ini?' }}')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash3 me-1"></i> Hapus Pegawai
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: Detail Info --}}
    <div class="col-lg-8">

        {{-- Informasi Pribadi --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i> Informasi Pribadi
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small mb-1">Nama Lengkap</div>
                        <div class="fw-medium">{{ $pegawai->nama }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small mb-1">NIK</div>
                        <div class="fw-medium">{{ $pegawai->nik }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small mb-1">Jenis Kelamin</div>
                        <div class="fw-medium">{{ $pegawai->jenis_kelamin ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small mb-1">Pendidikan Terakhir</div>
                        <div class="fw-medium">{{ $pegawai->pendidikan }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informasi Kepegawaian --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-building me-2 text-primary"></i> Informasi Kepegawaian
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small mb-1">Satker / Unit Kerja</div>
                        <div class="fw-medium">{{ $pegawai->satker->nama_satker ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small mb-1">Status</div>
                        <div>
                            @if($pegawai->status === 'aktif')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Aktif</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Non Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dokumen --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-folder2-open me-2 text-primary"></i> Dokumen
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- KTP --}}
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e5e7eb;">
                            <div style="width:42px;height:42px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-file-earmark-person text-primary" style="font-size:20px;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium" style="font-size:14px;">KTP</div>
                                @if($pegawai->file_ktp)
                                    <a href="{{ route('pegawai.file.show', [$pegawai, 'ktp']) }}" target="_blank" class="text-primary small text-decoration-none">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                    <span class="mx-1 text-muted">|</span>
                                    <a href="{{ route('pegawai.file.download', [$pegawai, 'ktp']) }}" class="text-primary small text-decoration-none">
                                        <i class="bi bi-download me-1"></i> Unduh
                                    </a>
                                @else
                                    <span class="text-muted small">Belum diupload</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- KK --}}
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e5e7eb;">
                            <div style="width:42px;height:42px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-file-earmark-text text-success" style="font-size:20px;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium" style="font-size:14px;">Kartu Keluarga</div>
                                @if($pegawai->file_kk)
                                    <a href="{{ route('pegawai.file.show', [$pegawai, 'kk']) }}" target="_blank" class="text-primary small text-decoration-none">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                    <span class="mx-1 text-muted">|</span>
                                    <a href="{{ route('pegawai.file.download', [$pegawai, 'kk']) }}" class="text-primary small text-decoration-none">
                                        <i class="bi bi-download me-1"></i> Unduh
                                    </a>
                                @else
                                    <span class="text-muted small">Belum diupload</span>
                                @endif
                            </div>
                        </div>
                    {{-- Ijazah --}}
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#fefce8;border:1px solid #fde047;">
                            <div style="width:42px;height:42px;border-radius:10px;background:#fef9c3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-mortarboard text-warning" style="font-size:20px;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium" style="font-size:14px;">Ijazah</div>
                                @if($pegawai->file_ijazah)
                                    <a href="{{ route('pegawai.file.show', [$pegawai, 'ijazah']) }}" target="_blank" class="text-primary small text-decoration-none">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                    <span class="mx-1 text-muted">|</span>
                                    <a href="{{ route('pegawai.file.download', [$pegawai, 'ijazah']) }}" class="text-primary small text-decoration-none">
                                        <i class="bi bi-download me-1"></i> Unduh
                                    </a>
                                @else
                                    <span class="text-muted small">Belum diupload</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
