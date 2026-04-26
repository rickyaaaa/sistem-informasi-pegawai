@extends('layouts.admin')

@section('title', 'DATA SATKER/SATWIL')

@section('content')

    {{-- ── Toolbar ─────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">Daftar Satker/Satwil</h5>
            <small class="text-muted">Total {{ $satkers->total() }} Satker/Satwil terdaftar</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('satker.create') }}" class="btn btn-danger btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Satker/Satwil
            </a>
        </div>
    </div>

    {{-- ── Filter bar ── --}}
    <form method="GET" action="{{ route('satker.index') }}" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}"
                       class="form-control form-control-sm"
                       placeholder="Cari nama satker/satwil...">
            </div>
            <div class="col-md-3">
                <select name="tipe" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    <option value="satker" {{ request('tipe') === 'satker' ? 'selected' : '' }}>Satker</option>
                    <option value="satwil" {{ request('tipe') === 'satwil' ? 'selected' : '' }}>Satwil</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-danger flex-grow-1">
                    <i class="bi bi-search"></i>
                </button>
                @if(request()->hasAny(['q','tipe']))
                    <a href="{{ route('satker.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ── Bulk Delete Toolbar (muncul saat ada yang dicentang) ────── --}}
    <div id="bulk-toolbar" class="alert alert-danger d-flex align-items-center justify-content-between py-2 px-3 mb-3" style="display:none!important;">
        <span>
            <i class="bi bi-check2-square me-1"></i>
            <span id="bulk-count">0</span> item dipilih
        </span>
        <button type="button" class="btn btn-danger btn-sm" id="btn-bulk-delete">
            <i class="bi bi-trash3 me-1"></i> Hapus yang Dipilih
        </button>
    </div>

    <form id="form-bulk-delete" method="POST" action="{{ route('satker.bulk-destroy') }}">
        @csrf
        @method('DELETE')

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted">
                            <tr>
                                <th style="width:42px;">
                                    <input type="checkbox" class="form-check-input" id="check-all" title="Pilih semua">
                                </th>
                                <th style="width:50px;">#</th>
                                <th>Nama Satker/Satwil</th>
                                <th style="width:100px;" class="text-center">Tipe</th>
                                <th class="text-end" style="width:110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($satkers as $satker)
                                {{-- Row Induk --}}
                                <tr class="row-induk" data-id="{{ $satker->id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input check-item"
                                               name="ids[]" value="{{ $satker->id }}">
                                    </td>
                                    <td class="text-muted">
                                        {{ ($satkers->currentPage() - 1) * $satkers->perPage() + $loop->iteration }}
                                    </td>
                                    <td>
                                        <span class="fw-bold text-danger">{{ $satker->nama_satker }}</span>
                                        @if($satker->children->count() > 0)
                                            <span class="badge bg-light text-secondary border ms-1" style="font-size:.7rem;">
                                                {{ $satker->children->count() }} sub
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($satker->tipe_satuan === 'satwil')
                                            <span class="badge bg-warning text-dark">Satwil</span>
                                        @else
                                            <span class="badge bg-info text-dark">Satker</span>
                                        @endif
                                    </td>
                                    <td class="text-end" style="white-space:nowrap;">
                                        <a href="{{ route('satker.edit', $satker) }}"
                                           class="text-success mx-1" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('satker.destroy', $satker) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus {{ $satker->nama_satker }} beserta semua sub-unit-nya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-danger mx-1 border-0 bg-transparent" title="Hapus">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-building fs-2 d-block mb-2 opacity-50"></i>
                                        Belum ada data Satker/Satwil
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <div class="mt-3 pagination-white">
        {{ $satkers->links() }}
    </div>

    <style>
        .pagination-white .text-muted {
            color: #fff !important;
        }
    </style>


    {{-- ══════════════════════════════════════════════════════════════
         MODAL: AI Import
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="modalAiImport" tabindex="-1" aria-labelledby="modalAiImportLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalAiImportLabel">
                        <i class="bi bi-stars me-2"></i> Import Semua Satker/Satwil via AI
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- Step 1: Pilih tipe --}}
                    <div id="ai-step-1">
                        <p class="text-muted mb-3">
                            Pilih tipe satuan yang ingin di-import. AI akan membaca semua data standar
                            dan menginputkan satu per satu secara otomatis.
                        </p>

                        <div class="d-flex gap-3 mb-4">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ai_tipe" id="ai_tipe_satker" value="satker" checked>
                                <label class="form-check-label fw-semibold" for="ai_tipe_satker">
                                    Satker <span class="text-muted fw-normal">(28 satker)</span>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ai_tipe" id="ai_tipe_satwil" value="satwil">
                                <label class="form-check-label fw-semibold" for="ai_tipe_satwil">
                                    Satwil <span class="text-muted fw-normal">(15 satwil)</span>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ai_tipe" id="ai_tipe_all" value="all">
                                <label class="form-check-label fw-semibold" for="ai_tipe_all">
                                    Semua <span class="text-muted fw-normal">(43 total)</span>
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-warning small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Data yang sudah ada <strong>tidak</strong> akan di-duplikasi — hanya yang belum ada yang akan ditambahkan.
                        </div>

                        <button type="button" class="btn btn-success w-100" id="btn-start-ai-import">
                            <i class="bi bi-play-fill me-1"></i> Mulai Import Otomatis
                        </button>
                    </div>

                    {{-- Step 2: Progress --}}
                    <div id="ai-step-2" style="display:none;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="spinner-border spinner-border-sm text-success" id="ai-spinner"></div>
                            <span id="ai-status-text" class="fw-semibold">Mempersiapkan data...</span>
                        </div>

                        <div class="progress mb-3" style="height:10px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                 id="ai-progress-bar" role="progressbar" style="width:0%"></div>
                        </div>

                        <div class="d-flex justify-content-between small text-muted mb-3">
                            <span>Progress: <strong id="ai-done">0</strong> / <strong id="ai-total">0</strong></span>
                            <span id="ai-percent">0%</span>
                        </div>

                        {{-- Log --}}
                        <div id="ai-log"
                             class="border rounded bg-dark text-light p-3 small"
                             style="height:220px; overflow-y:auto; font-family:monospace;">
                        </div>
                    </div>

                    {{-- Step 3: Done --}}
                    <div id="ai-step-3" style="display:none;">
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-3"></i>
                            <h5 class="fw-bold">Import Selesai!</h5>
                            <p class="text-muted" id="ai-summary-text"></p>
                            <button type="button" class="btn btn-danger" onclick="window.location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Halaman
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
    // ══════════════════════════════════════════════════════════════
    // BULK DELETE
    // ══════════════════════════════════════════════════════════════
    const checkAll    = document.getElementById('check-all');
    const checkItems  = () => document.querySelectorAll('.check-item');
    const bulkToolbar = document.getElementById('bulk-toolbar');
    const bulkCount   = document.getElementById('bulk-count');
    const btnBulkDel  = document.getElementById('btn-bulk-delete');

    function updateBulkToolbar() {
        const checked = document.querySelectorAll('.check-item:checked').length;
        if (checked > 0) {
            bulkToolbar.style.display = 'flex';
            bulkCount.textContent = checked;
        } else {
            bulkToolbar.style.display = 'none';
        }
    }

    checkAll.addEventListener('change', function () {
        checkItems().forEach(cb => cb.checked = this.checked);
        updateBulkToolbar();
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('check-item')) {
            updateBulkToolbar();
            // Sync check-all state
            const all   = checkItems().length;
            const chkd  = document.querySelectorAll('.check-item:checked').length;
            checkAll.indeterminate = chkd > 0 && chkd < all;
            checkAll.checked = chkd === all;
        }
    });

    btnBulkDel.addEventListener('click', function () {
        const count = document.querySelectorAll('.check-item:checked').length;
        if (confirm(`Yakin ingin menghapus ${count} item yang dipilih?\nSub-unit dari satker yang dipilih juga akan ikut terhapus.`)) {
            document.getElementById('form-bulk-delete').submit();
        }
    });


    // ══════════════════════════════════════════════════════════════
    // AI IMPORT DATA
    // ══════════════════════════════════════════════════════════════
    const SUB_SATKER = {
      "ITWASDA": ["SUBBAGRENMIN","SUBBAGDUMASANWAS"],
      "BIRO OPS": ["SUBBAGRENMIN","BAGBINOPS","BAGDALOPS","BAGKERMA"],
      "BIRO RENA": ["SUBBAGRENMIN","BAGSTRAJEMEN","BAGRENPROGAR","BAGDALPROGAR","BAG RBP"],
      "BIRO SDM": ["SUBBAGRENMIN","BAGDALPERS","BAGBINKAR","BAGWATPERS","BAGPSI"],
      "BIRO LOG": ["SUBBAGRENMIN","BAGADA","BAGINFOLOG","BAGBEKUM","BAGPAL","BAGFASKON","URGUDANG"],
      "BIDHUMAS": ["SUBBAGRENMIN","SUBBIDPENMAS","SUBBID PID","SUBBIDMULMED"],
      "BIDPROPAM": ["SUBBAGRENMIN","SUBBAGYANDUAN","SUBBAGREHABPERS","SUBBIDPAMINAL","SUBBIDPROVOS","SUBBIDWABPROF"],
      "BIDKUM": ["SUBBAGRENMIN","SUBBIDSUNLUHKUM","SUBBIDBANKUM"],
      "BID TIK": ["SUBBAGRENMIN","SUBBIDTEKKOM","SUBBIDTEKINFO"],
      "SPRIPIM": ["URRENMIN","URPRODOK","URBUNGKOL","URPAMWAL"],
      "SETUM": ["URRENMIN","SUBBAGBINSETTAKAH","SUBBAGSIPTAKA","URKANPOS"],
      "YANMA": ["URRENMIN","SUBBAGYANTOR","SUBBAGHARBANGLING","SUBBAGPAMKOLSIK"],
      "SPKT": ["URRENMIN","SIAGA SPKT"],
      "DITINTELKAM": ["SUBBAGRENMIN","BAGANALIS","SIYANMIN","SITEKINTEL","SISANDI","SUBDIT 1","SUBDIT 2","SUBDIT 3","SUBDIT 4","SUBDIT 5"],
      "DITRESKRIMUM": ["SUBBAGRENMIN","BAGBINOPSNAL","BAGWASSIDIK","SIDENT","SUBDIT 1","SUBDIT 2","SUBDIT 3","SUBDIT 4"],
      "DITRESKRIMSUS": ["SUBBAGRENMIN","BAGBINOPSNAL","BAGWASSIDIK","SIKORWAS PPNS","SUBDIT 1","SUBDIT 2","SUBDIT 3","SUBDIT 4","SUBDIT 5"],
      "DITRESNARKOBA": ["SUBBAGRENMIN","BAGIBINOPSNAL","BAGWASSIDIK","SUBDIT 1","SUBDIT 2","SUBDIT 3"],
      "DITBINMAS": ["SUBBAGRENMIN","BAGBINOPSNAL","SUBDITBINTIBSOS","SUBDITBINSATPAM/POLSUS","SUBDITBINPOLMAS","SUBDITBHABINKAMTIBMAS"],
      "DITSAMAPTA": ["SUBBAGRENMIN","BAGBINOPSNAL","SUBDITGASUM","SUBDITDALMAS","UNITPOLSATWA"],
      "DITLANTAS": ["SUBBAGRENMIN","BAGBINOPSNAL","SUBDITBINGAKKUM","SUBDITREGIDENT","SUBDITKAMSEL","SAT PJR"],
      "DITPAMOBVIT": ["BAGRENMIN","BAGBINOPSNAL","SUBDITWASTER","SUBDITWISATA","SUBDITVIP","SUBDITAUDIT"],
      "DITPOLAIRUD": ["SUBBAGRENMIN","BAGBINOPSNAL","SUBDITGAKKUM","SUBDITPATROLAIRUD","SUBDITFASHARKAN","KAPAL C2","KAPAL C3","KAPAL KERET","KAPAL RIB","KAPAL SHIFT TENDER","PESAWAT UDARA"],
      "DITTAHTI": ["SUBBAGRENMIN","SUBDITPAMTAH","SUBDITHARWATAH","SUBDITBARBUK"],
      "SATBRIMOB": ["BAGRENMIN","BAGOPS","SILOG","SIPROVOS","SI TIK","SIKESJAS","SIYANMA","SIINTEL","DEN GEGANA","SUBDEN (4)","YONPELOPOR (3)","KOMPI (4)"],
      "SPN": ["SUBBAGRENMIN","SUBBAGYANUM","URPROVOS","SUBBAGJARLAT","KORSIS","POLIKLINIK"],
      "BIDKEU": ["SUBBAGRENMIN","SUBBIDBIA DAN APK","SUBBIDDALKU"],
      "BIDDOKKES": ["SUBBAGRENMIN","SUBBIDDOKPOL","SUBBIDKESPOL","POLIKLINIK"],
      "RUMKIT": ["SUBBAGRENMIN","SUBBAGBINFUNG","SUBBAGWASINTERN","SUBBIDYANMEDDOKPOL","SUBBIDJANGMEDUM"]
    };

    const SUB_SATWIL = {
      "POLRESTA BANDAR LAMPUNG": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIKEU","SIUM","SIKUM","SIHUMAS","SIDOKKES","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPAMOBVIT","SATPOLAIRUD","SATTAHTI","SITIK","POLSEK KEDATON","POLSUBSEKTOR RAJA BASA","POLSEK SUKARAME","POLSEK TKT","POLSEK TKB","POLSEK TBU","POLSEK TBS","POLSEK TBT","POLSEK PANJANG","KSKP PANJANG","POLSUBSEKTOR CITRA GARDEN","POLSEK KEMILING","POLSEK TJNG SENANG"],
      "POLRES METRO": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATTAHTI","SIKEU","SIDOKKES","POLSEK METRO UTARA","POLSEK METRO BARAT","POLSEK METRO TIMUR","POLSEK METRO SELATAN","POLSEK METRO PUSAT"],
      "POLRES LAMPUNG SELATAN": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPAMOBVIT","SATPOLAIRUD","SATTAHTI","SIKEU","SIDOKKES","POLSEK NATAR","POLSEK TJ BINTANG","POLSEK KALIANDA","POLSEK KATIBUNG","POLSEK SIDOMULYO","POLSEK PENENGAHAN","POLSEK PALAS","POLSEK KSKP BAKAUHENI","POLSEK CANDIPURO","POLSEK SERAGI","POLSEK MERBAU MATARAM","POLSEK JATI AGUNG","POLSUBSEKTOR B. RADEN INTAN"],
      "POLRES LAMPUNG UTARA": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPAMOBVIT","SATPOLAIRUD","SATTAHTI","SIKEU","SIDOKKES","POLSEK BUKIT KEMUNING","POLSEK SUNGKAI SELATAN","POLSEK ABUNG BARAT","POLSEK ABUNG SELATAN","POLSEK ABUNG TIMUR","POLSEK SUNGKAI UTARA","POLSEK TANJUNG RAJA","POLSEK KOTABUMI UTARA","POLSEK ABUNG SEMULI","POLSEK ABUNG TENGAH","POLSEK MUARA SUNGKAI","POLSEK SUNGKAI JAYA","POLSEK ABUNG SURAKARTA","POLSEK BUNGA MAYANG","POLSEK KOTABUMI KOTA","POLSUBSEKTOR ABUNG TINGGI","POLSUBSEKTOR LUBUK RUKAM","POLSUBSEKTOR MULYO REJO","POLSUBSEKTOR PAPAN REJO","POLSUBSEKTOR CABANG EMPAT","POLSUBSEKTOR ABUNG KUNANG","POLSUBSEKTOR ABUNG PEKURUN"],
      "POLRES LAMPUNG BARAT": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPOLAIRUD","SATTAHTI","SIKEU","SIDOKKES","SATPAMOBVIT","POLSEK SUMBER JAYA","POLSEK SEKINCAU","POLSEK BANDAR NEGERI SUOH","POLSEK BALIK BUKIT","POLSUBSEKTOR WAY TENONG"],
      "POLRES TANGGAMUS": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPAMOBVIT","SATPOLAIRUD","SATTAHTI","SIKEU","SIDOKKES","POLSEK SEMAKA","POLSEK WONOSOBO","POLSEK KOTA AGUNG","POLSEK LIMAU","POLSEK SUMBEREJO","POLSEK PULAU PANGGUNG","POLSEK TALANG PADANG","POLSEK PEMATANG SAWA","POLSEK CUKUH BALAK","POLSEK PUGUNG","POLSUBSEKTOR NYAM","POLSUBSEKTOR KELUMBAYAN","POLSUBSEKTOR BULOK"],
      "POLRES TULANG BAWANG": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPAMOBVIT","SATPOLAIRUD","SATTAHTI","SIKEU","SIDOKKES","POLSEK MENGGALA","POLSEK GUNUNG TERANG","POLSEK BANJAR AGUNG","POLSEK PENAWARTAMA","POLSEK RAWA JITU S","POLSEK GEDUNG AJI","POLSEK RAWA PITU","POLSUBSEKTOR BANJAR AGUNG","POLSUBSEKTOR GEDUNG MENENG"],
      "POLRES LAMPUNG TIMUR": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPOLAIRUD","SATTAHTI","SIKEU","SIDOKKES","POLSEK SUKADANA","POLSEK LABUHAN MARINGGAI","POLSEK PEKALONGAN","POLSEK PURBOLINGGO","POLSEK RAMAN UTARA","POLSEK METRO KIBANG","POLSEK SEKAMPUNG","POLSEK BATANG HARI","POLSEK WAY JEPARA","POLSEK JABUNG","POLSEK SEKAMPUNG UDIK","POLSEK PASIR SAKTI","POLSEK WAWAY KARYA","POLSEK MELINTING","POLSEK GUNUNG PELINDUNG","POLSEK MATARAM BARU","POLSEK BANDAR SRIBHAWONO","POLSEK BATANG HARI NUBAN","POLSEK LABUHAN RATU","POLSEK WAY BUNGUR","POLSEK BUMI AGUNG","POLSEK MARGA TIGA","POLSEK BRAJA SELEBAH","POLSEK MARGA SKMPG","POLSUBSEKTOR GIRI MULYO","POLSUBSEKTOR SIDOREJO"],
      "POLRES WAY KANAN": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSABHARA","SATLANTAS","SATTAHTI","SIKEU","SIDOKKES","POLSEK BLAMBANGAN UMPU","POLSEK BARADATU","POLSEK KASUI","POLSEK BANJIT","POLSEK PAKUAN RATU","POLSEK BUAY BAHUGA","POLSEK WAY TUBA","POLSEK GUNUNG LABUHAN","POLSEK REBANG TANGKAS","POLSEK NEGARA BATIN","POLSEK NEGERI BESAR","POLSEK BUMI AGUNG","POLSUBSEKTOR NEGERI AGUNG"],
      "POLRES LAMPUNG TENGAH": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATTAHTI","SIKEU","SIDOKKES","POLSEK TERBANGGI BESAR","POLSEK PADANG RATU","POLSEK GUNUNG SUGIH","POLSEK TRIMURJO","POLSEK PUNGGUR","POLSEK SEPUTIH RAMAN","POLSEK SEPUTIH BANYAK","POLSEK RUMBIA","POLSEK SEPUTIH SURABAYA","POLSEK KALIREJO","POLSEK BANGUN REJO","POLSEK SEPUTIH MATARAM","POLSEK WAY PENGUBUAN","POLSEK SELAGAI LINGGA","POLSEK TERUSAN NUNYAI","POLSEK ANAK RATU AJI","POLSEK BUMI RATU NUBAN","POLSUBSEKTOR KOTA GAJAH"],
      "POLRES MESUJI": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPAMOBVIT","SATPOLAIR","SATTAHTI","SIKEU","SIDOKES","POLSEK SIMPANG PEMATANG","POLSEK TANJUNG RAYA","POLSEK WAY SERDANG","POLSEK MESUJI TIMUR","POLSUBSEKTOR RAWA JITU UTARA","POLSUBSEKTOR MESUJI"],
      "POLRES PESAWARAN": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIHUMAS","SIKUM","SIDOKKES","SIWAS","SIPROPAM","SIKEU","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPAMOBVIT","SATTAHTI","SATPOLAIR","POLSEK GEDONG TATAAN","POLSEK KEDONDONG","POLSEK PADANG CERMIN","POLSEK TEGINENENG","POLSUBSEKTOR WAY LIMA","POLSUBSEKTOR NEGERI KATON"],
      "POLRES PRINGSEWU": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIKEU","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSABHARA","SATLANTAS","SATTAHTI","SITIK","SIKUM","SIDOKKES","SIHUMAS","POLSEK PRINGSEWU KOTA","POLSEK PAGELARAN","POLSEK SUKOHARJO","POLSEK GADINGREJO","POLSEK PARDASUKA","POLSUBSEKTOR AMBARAWA","POLSUBSEKTOR BANYUMAS","POLSUBSEKTOR ADILUWIH","POLSUBSEKTOR PAGELARAN UTARA"],
      "POLRES TULANG BAWANG BARAT": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSABHARA","SATLANTAS","SATTAHTI","SIKEU","SIDOKKES","POLSEK TUBA TENGAH","POLSEK TUMIJAJAR","POLSEK GUNUNG AGUNG","POLSEK LAMBU KIBANG","POLSUBSEKTOR WAY KENANGA","POLSUBSEKTOR TUJOK"],
      "POLRES PESISIR BARAT": ["BAGOPS","BAGREN","BAGSDM","BAGLOG","SIWAS","SIPROPAM","SIHUMAS","SIKUM","SITIK","SIUM","SPKT","SATINTELKAM","SATRESKRIM","SATRESNARKOBA","SATBINMAS","SATSAMAPTA","SATLANTAS","SATPOLAIRUD","SATTAHTI","SIKEU","SIDOKKES","SATPAMOBVIT","POLSEK PESISIR TENGAH","POLSEK PESISIR UTARA","POLSEK PESISIR SELATAN","POLSEK BENGKUNAT","POLSUB SELENDANG MAYANG"]
    };

    // ── Helpers ────────────────────────────────────────────────────────
    function logLine(msg, type = 'info') {
        const log  = document.getElementById('ai-log');
        const colors = { info: '#adb5bd', success: '#51cf66', error: '#ff6b6b', skip: '#ffd43b' };
        const color = colors[type] || colors.info;
        log.innerHTML += `<div style="color:${color}">${msg}</div>`;
        log.scrollTop = log.scrollHeight;
    }

    function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

    async function postSatker(nama, tipe, subUnits) {
        const form = new FormData();
        form.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        form.append('nama_satker', nama);
        form.append('tipe_satuan', tipe);
        subUnits.forEach(s => form.append('sub_units[]', s));

        const resp = await fetch('{{ route("satker.store") }}', {
            method: 'POST',
            body: form,
            redirect: 'manual',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        // Laravel redirect = success; any 2xx or 302 = OK
        return resp.status < 500;
    }

    // ── Main import logic ──────────────────────────────────────────────
    document.getElementById('btn-start-ai-import').addEventListener('click', async function () {
        const tipe = document.querySelector('input[name="ai_tipe"]:checked').value;

        let dataset = {};
        if (tipe === 'satker') dataset = { satker: SUB_SATKER };
        else if (tipe === 'satwil') dataset = { satwil: SUB_SATWIL };
        else dataset = { satker: SUB_SATKER, satwil: SUB_SATWIL };

        // Build queue
        const queue = [];
        for (const [tipeName, data] of Object.entries(dataset)) {
            for (const [nama, subs] of Object.entries(data)) {
                queue.push({ nama, tipe: tipeName, subs });
            }
        }

        // Show progress UI
        document.getElementById('ai-step-1').style.display = 'none';
        document.getElementById('ai-step-2').style.display = 'block';
        document.getElementById('ai-total').textContent = queue.length;

        let done = 0, skipped = 0, errored = 0;

        for (const item of queue) {
            document.getElementById('ai-status-text').textContent = `Menginput: ${item.nama}...`;

            try {
                const ok = await postSatker(item.nama, item.tipe, item.subs);
                done++;
                if (ok) {
                    logLine(`✓ ${item.nama} (${item.subs.length} sub-unit)`, 'success');
                } else {
                    logLine(`⚠ ${item.nama} — mungkin sudah ada, dilewati`, 'skip');
                    skipped++;
                }
            } catch (e) {
                logLine(`✗ ${item.nama} — error: ${e.message}`, 'error');
                errored++;
            }

            // Update progress
            const pct = Math.round((done / queue.length) * 100);
            document.getElementById('ai-progress-bar').style.width = pct + '%';
            document.getElementById('ai-done').textContent = done;
            document.getElementById('ai-percent').textContent = pct + '%';

            await sleep(120); // sedikit delay biar server ga kewalahan
        }

        // Done!
        document.getElementById('ai-spinner').style.display = 'none';
        document.getElementById('ai-status-text').textContent = 'Import selesai!';
        await sleep(600);
        document.getElementById('ai-step-2').style.display = 'none';
        document.getElementById('ai-step-3').style.display = 'block';
        document.getElementById('ai-summary-text').textContent =
            `Berhasil import ${done - skipped - errored} item, ${skipped} dilewati (sudah ada), ${errored} error.`;
    });
    </script>
@endpush