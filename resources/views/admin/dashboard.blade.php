@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ── Search Panel ── */
    .search-panel {
        background: linear-gradient(135deg, #111827 0%, #7f1d1d 100%);
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 28px;
        box-shadow: 0 4px 24px rgba(30,42,59,.18);
    }

    .search-panel .panel-title {
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: .5px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        text-transform: uppercase;
    }

    .search-panel .panel-title i {
        color: #fca5a5;
        font-size: 20px;
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper input {
        border-radius: 10px;
        border: 2px solid rgba(255,255,255,.15);
        background: rgba(255,255,255,.08);
        color: #fff;
        padding: 11px 44px 11px 16px;
        font-size: 14px;
        transition: border-color .2s, background .2s;
    }

    .search-input-wrapper input::placeholder { color: rgba(255,255,255,.45); }
    .search-input-wrapper input:focus {
        outline: none;
        border-color: #dc2626;
        background: rgba(255,255,255,.12);
        box-shadow: 0 0 0 3px rgba(220,38,38,.25);
        color: #fff;
    }

    .search-input-wrapper .search-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255,255,255,.4);
        font-size: 16px;
        pointer-events: none;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: rgba(255,255,255,.55);
        margin-bottom: 8px;
    }

    .satker-select {
        border-radius: 10px;
        border: 2px solid rgba(255,255,255,.15);
        background: rgba(255,255,255,.08);
        color: #fff;
        padding: 11px 14px;
        font-size: 14px;
        transition: border-color .2s;
        appearance: auto;
    }

    .satker-select:focus {
        outline: none;
        border-color: #dc2626;
        background: rgba(255,255,255,.12);
        box-shadow: 0 0 0 3px rgba(220,38,38,.25);
    }

    .satker-select option { background: #111827; color: #fff; }

    .pendidikan-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pend-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .pend-chip input[type="checkbox"] { display: none; }

    .pend-chip .chip-label {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        border: 2px solid rgba(255,255,255,.2);
        color: rgba(255,255,255,.65);
        background: rgba(255,255,255,.06);
        transition: all .18s;
        user-select: none;
        letter-spacing: .3px;
    }

    .pend-chip input:checked + .chip-label {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
        box-shadow: 0 2px 10px rgba(220,38,38,.4);
    }

    .usia-input {
        border-radius: 10px;
        border: 2px solid rgba(255,255,255,.15);
        background: rgba(255,255,255,.08);
        color: #fff;
        padding: 11px 14px;
        font-size: 14px;
        width: 100%;
        transition: border-color .2s;
    }

    .usia-input::placeholder { color: rgba(255,255,255,.35); }

    .usia-input:focus {
        outline: none;
        border-color: #dc2626;
        background: rgba(255,255,255,.12);
        box-shadow: 0 0 0 3px rgba(220,38,38,.25);
        color: #fff;
    }

    .btn-cari {
        background: linear-gradient(135deg, #dc2626, #991b1b);
        border: none;
        border-radius: 10px;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        padding: 11px 32px;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .btn-cari:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220,38,38,.45);
    }

    .btn-reset {
        background: rgba(255,255,255,.08);
        border: 2px solid rgba(255,255,255,.2);
        border-radius: 10px;
        color: rgba(255,255,255,.65);
        font-size: 13px;
        font-weight: 600;
        padding: 11px 20px;
        text-decoration: none;
        transition: background .18s, color .18s;
        letter-spacing: .3px;
    }

    .btn-reset:hover {
        background: rgba(255,255,255,.14);
        color: #fff;
    }

    /* ── Tabs ── */
    .result-tabs .nav-link {
        font-weight: 700;
        color: #9ca3af;
        background: #1f2937;
        border: 1px solid #374151;
        padding: 10px 24px;
        margin-left: 8px;
        border-radius: 8px 8px 0 0;
    }
    .result-tabs .nav-link.active {
        color: #f3f4f6;
        background: #374151;
        border-bottom-color: #374151;
    }
    .tab-content-box {
        background: #374151;
        border: 1px solid #4b5563;
        border-radius: 12px;
        border-top-right-radius: 0;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,.3);
        color: #e5e7eb;
    }
    
    /* ── Card Pegawai ── */
    .pegawai-card {
        background: #1f2937;
        border: 1px solid #374151;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        margin-bottom: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,.3);
        color: #e5e7eb;
    }
    .pegawai-card .foto-wrapper {
        width: 65px;
        height: 80px;
        border-radius: 8px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-right: 18px;
        flex-shrink: 0;
        border: 1px solid #e5e7eb;
    }
    .pegawai-card .foto-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    .pegawai-card .foto-wrapper i { font-size: 32px; color: #9ca3af; }
    
    .pegawai-card .info { flex: 1; }
    .pegawai-card .info-name {
        font-size: 15px;
        font-weight: 700;
        color: #f9fafb;
        margin-bottom: 2px;
        text-transform: uppercase;
    }
    .pegawai-card .info-sub {
        font-size: 12px;
        color: #d1d5db;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .pegawai-card .info-muted {
        font-size: 11px;
        color: #9ca3af;
    }
    .pegawai-card .actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .pegawai-card .actions .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border-radius: 6px;
    }
    
    /* ── Ekspor Tab ── */
    .ekspor-header {
        background: #111827;
        color: #fff;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        font-size: 14px;
    }
    .dt-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #9ca3af;
    }
    .empty-state i { font-size: 48px; margin-bottom: 12px; }
    .empty-state p { font-size: 14px; margin: 0; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Overview</h4>
    <a href="{{ route('dashboard.refresh-cache') }}"
       id="btn-refresh-cache"
       class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2"
       style="font-size:13px; font-weight:600; letter-spacing:.3px;"
       title="Paksa muat ulang statistik dari database">
        <i class="bi bi-arrow-clockwise"></i>
        Refresh Data
    </a>
</div>

{{-- ════════════════════════════════════════════════════════
     PANEL PENCARIAN CANGGIH
════════════════════════════════════════════════════════ --}}
<form method="GET" action="{{ route('dashboard') }}" id="searchForm">
<div class="search-panel">

    <div class="panel-title">
        <i class="bi bi-search"></i>
        Pencarian Personel
    </div>

    <div class="row g-3 align-items-end">

        {{-- Nama / NIK --}}
        <div class="col-md-4">
            <div class="filter-label">Nama / NIK</div>
            <div class="search-input-wrapper">
                <input type="text"
                       name="q"
                       id="q"
                       class="form-control"
                       placeholder="Cari nama atau NIK…"
                       value="{{ request('q') }}"
                       autocomplete="off">
                <i class="bi bi-person-search search-icon"></i>
            </div>
        </div>

        {{-- Satker --}}
        <div class="col-md-4">
            <div class="filter-label">Satker / Satwil</div>
            @if(auth()->user()->isAdminSatker())
                {{-- Operator: locked to own satker --}}
                <select class="form-select satker-select w-100" disabled>
                    <option>{{ auth()->user()->satker->nama_satker ?? '-' }}</option>
                </select>
                <input type="hidden" name="satker_id_search" value="{{ auth()->user()->satker_id }}">
            @else
                <select name="satker_id_search" id="satker_id_search" class="form-select satker-select w-100">
                    <option value="">— Semua Satker/Satwil —</option>
                    @foreach($satkers as $satker)
                        <option value="{{ $satker->id }}"
                            {{ request('satker_id_search') == $satker->id ? 'selected' : '' }}>
                            {{ $satker->nama_satker }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>

        {{-- Usia Maksimal --}}
        <div class="col-md-4">
            <div class="filter-label">Usia Maksimal (tahun)</div>
            <input type="number"
                   name="usia_max"
                   id="usia_max"
                   class="usia-input"
                   placeholder="Contoh: 35"
                   min="18" max="80"
                   value="{{ request('usia_max') }}">
        </div>

        {{-- Pendidikan (multi-checkbox chip) --}}
        <div class="col-12">
            <div class="filter-label">Tingkat Pendidikan <small style="font-size:10px;color:rgba(255,255,255,.35);text-transform:none;">(bisa pilih lebih dari satu)</small></div>
            <div class="pendidikan-grid">
                @foreach($pendidikanList as $pend)
                    @php
                        $checked = in_array($pend, (array) request('pendidikan_search', []));
                    @endphp
                    <label class="pend-chip">
                        <input type="checkbox"
                               name="pendidikan_search[]"
                               value="{{ $pend }}"
                               {{ $checked ? 'checked' : '' }}>
                        <span class="chip-label">{{ $pend }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="col-12 d-flex gap-3 mt-1">
            <button type="submit" class="btn-cari">
                <i class="bi bi-search me-2"></i>Cari
            </button>
            <a href="{{ route('dashboard') }}" class="btn-reset">
                <i class="bi bi-x-circle me-1"></i>Reset
            </a>
        </div>

    </div>
</div>
</form>

{{-- ════════════════════════════════════════════════════════
     HASIL PENCARIAN & TABS
════════════════════════════════════════════════════════ --}}
@if($searchPerformed)
<div class="mb-4">
    
    {{-- Info Hasil Pencarian (Jumlah & Filter Aktif) --}}
    <div class="d-flex justify-content-between align-items-end mb-2">
        <div>
            <span class="badge bg-danger rounded-pill mb-2 px-3 py-2">
                {{ $searchResults->total() }} Data Ditemukan
            </span>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @if(request('q')) <span class="badge bg-white text-dark border">Kata kunci: "{{ request('q') }}"</span> @endif
                @if(request('satker_id_search'))
                    @php $sn = $satkers->firstWhere('id', request('satker_id_search')); @endphp
                    <span class="badge bg-white text-dark border">Satker: {{ $sn?->nama_satker ?? '-' }}</span>
                @endif
                @if(request('pendidikan_search'))
                    <span class="badge bg-white text-dark border">Pendidikan: {{ implode(', ', (array) request('pendidikan_search')) }}</span>
                @endif
                @if(request('usia_max'))
                    <span class="badge bg-white text-dark border">Usia ≤ {{ request('usia_max') }} tahun</span>
                @endif
            </div>
        </div>

        {{-- Nav Tabs --}}
        <ul class="nav nav-tabs border-0 mt-3 result-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active shadow-sm" id="hasil-tab" data-bs-toggle="tab" data-bs-target="#hasil-pane" type="button" role="tab">
                    <i class="bi bi-list-nested me-1"></i>Hasil Pencarian
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link shadow-sm" id="ekspor-tab" data-bs-toggle="tab" data-bs-target="#ekspor-pane" type="button" role="tab">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Ekspor
                </button>
            </li>
        </ul>
    </div>

    {{-- Content Area --}}
    <div class="tab-content tab-content-box" id="myTabContent">

        {{-- TAB 1: HASIL PENCARIAN (CARDS LIST) --}}
        <div class="tab-pane fade show active" id="hasil-pane" role="tabpanel">
            @if($searchResults->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-person-x"></i>
                    <p>Tidak ada data personel yang sesuai.</p>
                </div>
            @else
                <div class="row">
                    @foreach($searchResults as $p)
                        <div class="col-12">
                            <div class="pegawai-card">
                                <div class="foto-wrapper">
                                    @if($p->foto)
                                        <img src="{{ Storage::url($p->foto) }}" alt="Foto">
                                    @else
                                        <i class="bi bi-person-fill"></i>
                                    @endif
                                </div>
                                <div class="info">
                                    <div class="info-name">{{ $p->nama }}</div>
                                    <div class="info-sub">
                                        {{ strtoupper($p->pendidikan) }} / {{ $p->nik }}
                                    </div>
                                    <div class="info-muted">
                                        {{ $p->satker?->nama_satker ?? '-' }} <br>
                                        Status: {{ ucfirst($p->status) }}
                                    </div>
                                </div>
                                <div class="actions">
                                    <a href="{{ route('pegawai.show', $p) }}" class="btn btn-danger text-white" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('pegawai.edit', $p) }}" class="btn btn-success text-white" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($searchResults->hasPages())
                    <div class="mt-3">
                        {{ $searchResults->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- TAB 2: EKSPOR (Tabel Data) --}}
        <div class="tab-pane fade" id="ekspor-pane" role="tabpanel">
            <div class="ekspor-header">
                <div><i class="bi bi-file-earmark-arrow-down me-2"></i>Ekspor Lanjutan +</div>
            </div>

            <div class="dt-toolbar">
                <div>
                    <span class="text-white" style="font-size:13px;">Show</span>
                    <select class="form-select form-select-sm d-inline-block w-auto mx-1" onchange="window.location.href = '?per_page=' + this.value + '&{{ http_build_query(request()->except(['per_page', 'page'])) }}'">
                        <option value="10" {{ request('per_page', 10) == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                    </select>
                    <span class="text-white" style="font-size:13px;">entries</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('pegawai.export') }}?type=pdf&{{ http_build_query(request()->except('page')) }}" class="btn btn-sm btn-danger fw-bold" style="font-size:12px;">PDF Singkat</a>
                    <a href="{{ route('pegawai.export') }}?type=excel&{{ http_build_query(request()->except('page')) }}" class="btn btn-sm btn-success fw-bold" style="font-size:12px;">Excel Singkat</a>
                </div>
            </div>

            <div class="table-responsive border rounded border-secondary">
                <table class="table table-hover mb-0 align-middle table-sm text-white" style="font-size:13px;">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width:40px;"><input type="checkbox" class="form-check-input"></th>
                            <th style="width:50px;">NO</th>
                            <th>NAMA</th>
                            <th>JENIS KELAMIN</th>
                            <th>PENDIDIKAN</th>
                            <th>PRODI</th>
                            <th>UMUR</th>
                            <th>SATKER/SATWIL</th>
                            <th>SUB/BAG</th>
                            <th class="text-center" style="width:80px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($searchResults as $i => $p)
                            <tr>
                                <td class="text-center"><input type="checkbox" class="form-check-input" name="export_ids[]" value="{{ $p->id }}"></td>
                                <td class="text-white">{{ $searchResults->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold text-uppercase text-white">{{ $p->nama }}</td>
                                <td class="text-uppercase text-white">{{ $p->jenis_kelamin ?? '-' }}</td>
                                <td class="text-uppercase text-white">{{ $p->pendidikan }}</td>
                                <td class="text-uppercase text-white">{{ $p->prodi->nama ?? '-' }}</td>
                                <td class="text-white">{{ $p->tgl_lahir ? \Carbon\Carbon::parse($p->tgl_lahir)->age . ' Tahun' : '-' }}</td>
                                <td class="text-uppercase text-white" style="max-width:200px;">
                                    {{ $p->satker?->level === 'sub' ? strtoupper($p->satker?->parent?->nama_satker ?? '-') : strtoupper($p->satker?->nama_satker ?? '-') }}
                                </td>
                                <td class="text-uppercase" style="color:#d1d5db; max-width:180px; font-size:12px;">
                                    {{ $p->satker?->level === 'sub' ? strtoupper($p->satker?->nama_satker) : '-' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('pegawai.show', $p) }}" class="text-danger me-2"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('pegawai.edit', $p) }}" class="text-success"><i class="bi bi-pencil-square"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-white" style="font-size:13px;">
                    Showing {{ $searchResults->firstItem() ?? 0 }} to {{ $searchResults->lastItem() ?? 0 }} of {{ $searchResults->total() }} entries
                </div>
                @if($searchResults->hasPages())
                    <div class="pagination-sm">
                        {{ $searchResults->links() }}
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════
     STATISTIK PEGAWAI NON ASN — style SIPP
════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- Panel Kiri: Statistik Pegawai Non ASN --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="background:#111827; color:#fff; border-radius:14px;">
            <div class="card-body p-4">

                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-people-fill text-warning fs-5"></i>
                        <span class="fw-bold text-uppercase" style="font-size:14px; letter-spacing:.5px;">Statistik Pegawai Non ASN</span>
                    </div>
                </div>

                {{-- Keseluruhan Pegawai --}}
                <div class="mb-4 bg-light bg-opacity-10 p-3 rounded" style="border-left: 4px solid #dc2626;">
                    <div class="text-white-50 fw-semibold text-uppercase" style="font-size:12px; letter-spacing:0.5px;">Keseluruhan Pegawai</div>
                    <div class="d-flex align-items-center gap-3 mt-1">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-people-fill text-warning fs-3"></i>
                            <span class="fw-black" style="font-size:32px; letter-spacing:-1px;">{{ number_format($totalPegawai) }}</span>
                        </div>
                        <div class="d-flex gap-3 text-white-50" style="font-size:14px; margin-left: 20px;">
                            <span><i class="bi bi-gender-male text-danger me-1"></i> Pria: <strong class="text-white">{{ number_format($pegawaiPria) }}</strong></span>
                            <span><i class="bi bi-gender-female text-danger me-1"></i> Wanita: <strong class="text-white">{{ number_format($pegawaiWanita) }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Baris SATKER --}}
                <div class="row g-3 text-center mb-3">
                    <div class="col-4">
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size:11px; letter-spacing:.5px;">Satker</div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                            <i class="bi bi-building-fill" style="color:#fca5a5; font-size:20px;"></i>
                            <span class="fw-bold" style="font-size:22px;">{{ number_format($pegawaiSatkerTotal) }}</span>
                        </div>
                    </div>
                    <div class="col-4 border-start border-secondary">
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size:11px; letter-spacing:.5px;">Pria</div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                            <i class="bi bi-gender-male" style="color:#fca5a5; font-size:18px;"></i>
                            <span class="fw-semibold" style="font-size:20px;">{{ number_format($pegawaiSatkerPria) }}</span>
                        </div>
                    </div>
                    <div class="col-4 border-start border-secondary">
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size:11px; letter-spacing:.5px;">Wanita</div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                            <i class="bi bi-gender-female" style="color:#f472b6; font-size:18px;"></i>
                            <span class="fw-semibold" style="font-size:20px;">{{ number_format($pegawaiSatkerWanita) }}</span>
                        </div>
                    </div>
                </div>

                <hr class="border-secondary opacity-25 my-3">

                {{-- Baris SATWIL --}}
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size:11px; letter-spacing:.5px;">Satwil</div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                            <i class="bi bi-compass-fill" style="color:#34d399; font-size:20px;"></i>
                            <span class="fw-bold" style="font-size:22px;">{{ number_format($pegawaiSatwilTotal) }}</span>
                        </div>
                    </div>
                    <div class="col-4 border-start border-secondary">
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size:11px; letter-spacing:.5px;">Pria</div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                            <i class="bi bi-gender-male" style="color:#34d399; font-size:18px;"></i>
                            <span class="fw-semibold" style="font-size:20px;">{{ number_format($pegawaiSatwilPria) }}</span>
                        </div>
                    </div>
                    <div class="col-4 border-start border-secondary">
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size:11px; letter-spacing:.5px;">Wanita</div>
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                            <i class="bi bi-gender-female" style="color:#f472b6; font-size:18px;"></i>
                            <span class="fw-semibold" style="font-size:20px;">{{ number_format($pegawaiSatwilWanita) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Panel Kanan: Grafik Status & Pendidikan --}}
    <div class="col-lg-6">
        <div class="row g-4 h-100">
            <div class="col-12">
                <div class="card stat-card p-4 h-100">
                    <h6 class="fw-semibold mb-3">Status Pegawai (Aktif / Non Aktif)</h6>
                    <div style="position:relative; height:150px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card stat-card p-4 h-100">
                    <h6 class="fw-semibold mb-3">Pendidikan Pegawai</h6>
                    <div style="position:relative; height:150px;">
                        <canvas id="pendidikanChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Aktif', 'Non Aktif'],
        datasets: [{
            data: [{{ $pegawaiAktif }}, {{ $pegawaiNonAktif }}],
            backgroundColor: ['#16a34a', '#dc2626']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

new Chart(document.getElementById('pendidikanChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($pendidikanStats->keys()) !!},
        datasets: [{
            label: 'Total',
            data: {!! json_encode($pendidikanStats->values()) !!},
            backgroundColor: '#7c3aed',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>

@endsection
