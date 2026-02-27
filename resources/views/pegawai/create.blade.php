@extends('layouts.admin')

@section('title', 'Tambah Pegawai')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Approval notice for admin_satker --}}
        @if(auth()->user()->isAdminSatker())
            <div class="alert border-0 mb-3 d-flex align-items-start gap-3"
                 style="background:#fffbeb;border-left:4px solid #f59e0b !important;border-radius:10px;padding:14px 18px;">
                <i class="bi bi-hourglass-split text-warning mt-1" style="font-size:18px;flex-shrink:0;"></i>
                <div>
                    <div class="fw-semibold mb-1" style="font-size:14px;color:#92400e;">Tambah pegawai memerlukan persetujuan</div>
                    <div style="font-size:13px;color:#78350f;">
                        Data baru akan dikirim sebagai permintaan dan ditambahkan ke sistem setelah disetujui Super Admin.
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                <i class="bi bi-person-plus-fill"></i>
                Tambah Data Pegawai
            </div>

            <div class="card-body p-4">
                <form method="POST"
                      action="{{ route('pegawai.store') }}"
                      enctype="multipart/form-data">
                    @csrf

                    @include('pegawai._form')

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        @if(auth()->user()->isAdminSatker())
                            <button type="submit" class="btn btn-warning px-4">
                                <i class="bi bi-send me-1"></i> Kirim untuk Persetujuan
                            </button>
                        @else
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

