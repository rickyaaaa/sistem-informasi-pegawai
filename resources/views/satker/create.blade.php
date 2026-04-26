@extends('layouts.admin')

@section('title', 'Tambah Satker/Satwil')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white d-flex align-items-center gap-2">
                <i class="bi bi-building-fill-add"></i>
                <h5 class="mb-0">Form Tambah Satker/Satwil</h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('satker.store') }}">
                    @csrf

                    {{-- Tipe Satuan --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tipe Satuan</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_satuan" id="tipe_satker"
                                    value="satker" {{ old('tipe_satuan', 'satker') === 'satker' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipe_satker">Satker</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_satuan" id="tipe_satwil"
                                    value="satwil" {{ old('tipe_satuan') === 'satwil' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipe_satwil">Satwil</label>
                            </div>
                        </div>
                    </div>

                    {{-- Nama Satker/Satwil --}}
                    <div class="mb-4">
                        <label for="nama_satker" class="form-label fw-semibold">Nama Satker/Satwil</label>

                        {{-- Dropdown pilih dari daftar --}}
                        <select id="nama_satker_select" class="form-select form-select-lg mb-2
                            @error('nama_satker') is-invalid @enderror">
                            <option value="">-- Pilih dari daftar atau ketik manual --</option>
                        </select>

                        {{-- Input manual / hasil pilih --}}
                        <input
                            type="text"
                            id="nama_satker"
                            name="nama_satker"
                            value="{{ old('nama_satker') }}"
                            class="form-control @error('nama_satker') is-invalid @enderror"
                            placeholder="Atau ketik nama manual..."
                        >
                        @error('nama_satker')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Pilih dari daftar untuk auto-isi sub-unit, atau ketik manual.</div>
                    </div>

                    {{-- Satker Induk (opsional — terisi otomatis jika dari tombol Tambah Sub-Unit) --}}
                    <div class="mb-4">
                        <label for="parent_id" class="form-label fw-semibold">
                            Satker Induk
                            <span class="text-muted fw-normal">(opsional — kosongkan untuk Satker Induk baru)</span>
                        </label>
                        <select id="parent_id" name="parent_id" class="form-select">
                            <option value="">-- Tidak Ada / Satker Utama --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"
                                    {{ (old('parent_id', $preselectedParentId) == $parent->id) ? 'selected' : '' }}>
                                    {{ $parent->nama_satker }}
                                </option>
                            @endforeach
                        </select>
                        @if($preselectedParentId)
                            <div class="form-text text-success">
                                <i class="bi bi-info-circle me-1"></i>
                                Sub-unit ini akan ditambahkan ke satker induk yang sudah dipilih di atas.
                            </div>
                        @endif
                    </div>

                    {{-- Sub-Unit otomatis (hanya tampil jika TIDAK dalam mode sub-unit) --}}
                    @if(!$preselectedParentId)
                    <div id="sub-unit-section" class="mb-4" style="display:none;">
                        <label class="form-label fw-semibold">
                            Sub-Unit
                            <span class="badge bg-secondary ms-1" id="sub-unit-count">0</span>
                        </label>
                        <div class="alert alert-info py-2 px-3 small mb-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Sub-unit berikut akan otomatis ditambahkan berdasarkan data standar.
                            Hilangkan centang untuk mengecualikan sub-unit tertentu.
                        </div>
                        <div id="sub-unit-list" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            {{-- Diisi via JS --}}
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">
                        @if($preselectedParentId)
                            <a href="{{ route('satker.edit', $preselectedParentId) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Satker Induk
                            </a>
                        @else
                            <a href="{{ route('satker.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        @endif
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Data Sub-Unit ──────────────────────────────────────────────────
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

// ── Logika Utama ───────────────────────────────────────────────────
const tipeRadios   = document.querySelectorAll('input[name="tipe_satuan"]');
const selectEl     = document.getElementById('nama_satker_select');
const inputEl      = document.getElementById('nama_satker');
const subSection   = document.getElementById('sub-unit-section');
const subList      = document.getElementById('sub-unit-list');
const subCount     = document.getElementById('sub-unit-count');

function getCurrentTipe() {
    return document.querySelector('input[name="tipe_satuan"]:checked')?.value || 'satker';
}

function getCurrentData() {
    return getCurrentTipe() === 'satker' ? SUB_SATKER : SUB_SATWIL;
}

function populateSelect() {
    const data = getCurrentData();
    selectEl.innerHTML = '<option value="">-- Pilih dari daftar atau ketik manual --</option>';
    Object.keys(data).forEach(nama => {
        const opt = document.createElement('option');
        opt.value = nama;
        opt.textContent = nama;
        selectEl.appendChild(opt);
    });
    // Reset sub jika tipe berubah
    inputEl.value = '';
    hideSubSection();
}

function renderSubList(subs) {
    subList.innerHTML = '';
    subs.forEach((sub, i) => {
        const id = `sub_${i}`;
        const div = document.createElement('div');
        div.className = 'form-check mb-1';
        div.innerHTML = `
            <input class="form-check-input" type="checkbox" name="sub_units[]"
                   value="${sub}" id="${id}" checked>
            <label class="form-check-label small" for="${id}">${sub}</label>
        `;
        subList.appendChild(div);
    });
    subCount.textContent = subs.length;
    subSection.style.display = 'block';
}

function hideSubSection() {
    subSection.style.display = 'none';
    subList.innerHTML = '';
}

// Ketika pilih dari dropdown
selectEl.addEventListener('change', function () {
    const nama = this.value;
    inputEl.value = nama;
    if (!nama) { hideSubSection(); return; }

    const data = getCurrentData();
    const subs = data[nama];
    if (subs && subs.length > 0) {
        renderSubList(subs);
    } else {
        hideSubSection();
    }
});

// Ketika ganti tipe satuan
tipeRadios.forEach(radio => {
    radio.addEventListener('change', populateSelect);
});

// Ketika ketik manual → coba cocokkan
inputEl.addEventListener('input', function () {
    const val = this.value.trim().toUpperCase();
    const data = getCurrentData();
    if (data[val]) {
        selectEl.value = val;
        renderSubList(data[val]);
    } else {
        selectEl.value = '';
        hideSubSection();
    }
});

// Init
populateSelect();
</script>
@endpush