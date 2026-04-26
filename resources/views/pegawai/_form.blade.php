<div class="row g-3">

    {{-- Foto Profil --}}
    <div class="col-12">
        <label class="form-label">Foto Profil</label>
        <div class="d-flex align-items-center gap-3">
            @if(isset($pegawai) && $pegawai->foto)
                <img src="{{ asset('storage/' . $pegawai->foto) }}"
                     alt="Foto {{ $pegawai->nama }}"
                     id="fotoPreview"
                     style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #e5e7eb;">
            @else
                <div id="fotoPreview"
                     style="width:80px;height:80px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;border:3px solid #e5e7eb;">
                    <i class="bi bi-person-fill" style="font-size:32px;color:#9ca3af;"></i>
                </div>
            @endif
            <div>
                <input type="file" name="foto" class="form-control form-control-sm" accept=".jpg,.jpeg,.png" id="fotoInput">
                <small class="text-muted">Format: JPG, JPEG, PNG. Maks 2MB.</small>
            </div>
        </div>
    </div>

    {{-- Nama --}}
    <div class="col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control"
               value="{{ old('nama', $pegawai->nama ?? '') }}" required>
    </div>

    {{-- NIK --}}
    <div class="col-md-6">
        <label class="form-label">NIK <span class="text-danger">*</span></label>
        <input type="text" name="nik" class="form-control"
               value="{{ old('nik', $pegawai->nik ?? '') }}"
               maxlength="16" pattern="\d{16}" title="NIK harus 16 digit angka" required>
        <small class="text-muted">16 digit angka</small>
    </div>

    {{-- Tanggal Lahir --}}
    <div class="col-md-6">
        <label class="form-label">Tanggal Lahir</label>
        {{-- Hidden input yang menyimpan nilai Y-m-d untuk backend --}}
        <input type="hidden" name="tgl_lahir" id="tgl_lahir_hidden"
               value="{{ old('tgl_lahir', isset($pegawai) && $pegawai->tgl_lahir ? $pegawai->tgl_lahir->format('Y-m-d') : '') }}">
        {{-- Input tampilan Flatpickr --}}
        <input type="text" id="tgl_lahir_picker" class="form-control"
               placeholder="Pilih tanggal lahir..."
               autocomplete="off" readonly>
    </div>

    {{-- Jenis Kelamin --}}
    <div class="col-md-6">
        <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
        <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
            <option value="">-- Pilih Jenis Kelamin --</option>
            <option value="Pria" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') === 'Pria')>Pria</option>
            <option value="Wanita" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') === 'Wanita')>Wanita</option>
        </select>
        @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Pendidikan --}}
    <div class="col-md-6">
        <label class="form-label">Tingkat Pendidikan <span class="text-danger">*</span></label>
        <select name="pendidikan" id="pendidikan" class="form-select @error('pendidikan') is-invalid @enderror">
            <option value="">-- Pilih Pendidikan --</option>
            @foreach(['SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S1 Profesi', 'S2', 'S2 Profesi', 'S3'] as $level)
                <option value="{{ $level }}" @selected(old('pendidikan', $pegawai->pendidikan ?? '') === $level)>
                    {{ $level }}
                </option>
            @endforeach
        </select>
        @error('pendidikan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Prodi (Dynamic) --}}
    <div class="col-md-6" id="prodi_wrapper" style="display:none;">
        <label class="form-label">Program Studi / Jurusan</label>
        <select name="prodi_id" id="prodi_id" class="form-select">
            <option value="">-- Pilih Pendidikan Dulu --</option>
        </select>
    </div>

    {{-- Input Manual Prodi (muncul saat pilih "Lainnya") --}}
    <div class="col-md-6" id="prodi_lainnya_wrapper" style="display:none;">
        <label class="form-label">Nama Jurusan (Manual)</label>
        <input type="text" name="prodi_lainnya" id="prodi_lainnya_input" class="form-control"
               placeholder="Ketik nama jurusan..."
               value="{{ old('prodi_lainnya', '') }}">
        <small class="text-muted">Jurusan baru akan otomatis tersimpan ke database.</small>
    </div>

    {{-- Tanggal Mulai Kerja --}}
    <div class="col-md-6">
        <label class="form-label">Tanggal Mulai Kerja</label>
        {{-- Hidden input yang menyimpan nilai Y-m-d untuk backend --}}
        <input type="hidden" name="tgl_kerja" id="tgl_kerja_hidden"
               value="{{ old('tgl_kerja', isset($pegawai) && $pegawai->tgl_kerja ? $pegawai->tgl_kerja->format('Y-m-d') : '') }}">
        {{-- Input tampilan Flatpickr --}}
        <input type="text" id="tgl_kerja_picker" class="form-control"
               placeholder="Pilih tanggal mulai kerja..."
               autocomplete="off" readonly>
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="aktif" @selected(old('status', $pegawai->status ?? '') === 'aktif')>Aktif</option>
            <option value="non_aktif" @selected(old('status', $pegawai->status ?? '') === 'non_aktif')>Non Aktif</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- K-II Status --}}
    <div class="col-md-6">
        <label class="form-label">K-II / Non K-II <span class="text-danger">*</span></label>
        <select name="status_k2" id="status_k2" class="form-select @error('status_k2') is-invalid @enderror">
            <option value="Non K-II" @selected(old('status_k2', $pegawai->status_k2 ?? '') === 'Non K-II')>Non K-II</option>
            <option value="K-II" @selected(old('status_k2', $pegawai->status_k2 ?? '') === 'K-II')>K-II</option>
        </select>
        @error('status_k2') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Nomor Registrasi K-II --}}
    <div class="col-md-6" id="nomor_k2_wrapper" style="display:none;">
        <label class="form-label">Nomor Registrasi K-II</label>
        <input type="text" name="nomor_k2" id="nomor_k2" class="form-control"
               value="{{ old('nomor_k2', $pegawai->nomor_k2 ?? '') }}" placeholder="Masukkan Nomor Registrasi K-II">
    </div>

    {{-- ═══════════════ TRIPLE DROPDOWN SATKER ═══════════════ --}}

    {{-- Tahap 1: Kategori Satker/Satwil --}}
    <div class="col-md-4">
        <label class="form-label">Kategori <span class="text-danger">*</span></label>
        @if($user->isAdminSatker())
            @php $opSatker = $user->satker; @endphp
            <input type="text" class="form-control" value="{{ ucfirst($opSatker->tipe_satuan ?? '-') }}" disabled>
            <input type="hidden" id="kategori_satker" value="{{ $opSatker->tipe_satuan ?? '' }}">
        @else
            <select id="kategori_satker" class="form-select @error('satker_id') is-invalid @enderror">
                <option value="">-- Pilih Kategori --</option>
                <option value="satker" @selected(old('_kategori', (isset($pegawai) ? optional($pegawai->satker)->tipe_satuan : '')) === 'satker')>Satker</option>
                <option value="satwil" @selected(old('_kategori', (isset($pegawai) ? optional($pegawai->satker)->tipe_satuan : '')) === 'satwil')>Satwil</option>
            </select>
        @endif
    </div>

    {{-- Tahap 2: Nama Satker/Satwil (Induk) --}}
    <div class="col-md-4">
        <label class="form-label">Nama Satker/Satwil <span class="text-danger">*</span></label>
        @if($user->isAdminSatker())
            <input type="text" class="form-control" value="{{ $user->satker->nama_satker ?? '-' }}" disabled>
            <input type="hidden" id="induk_satker_id" value="{{ $user->satker_id }}">
        @else
            <select id="induk_satker_id" class="form-select @error('satker_id') is-invalid @enderror">
                <option value="">-- Pilih Kategori --</option>
            </select>
        @endif
    </div>

    {{-- Tahap 3: Unit Kerja (Sub-Bagian via AJAX) --}}
    <div class="col-md-4">
        <label class="form-label">Unit Kerja <span class="text-danger">*</span></label>
        <select name="satker_id" id="sub_satker_id" class="form-select @error('satker_id') is-invalid @enderror">
            <option value="">-- Pilih Satker --</option>
            @if(isset($subSatkers) && $subSatkers->count())
                @foreach($subSatkers as $sub)
                    <option value="{{ $sub->id }}"
                        @selected(old('satker_id', $pegawai->satker_id ?? '') == $sub->id)>
                        {{ $sub->nama_satker }}
                    </option>
                @endforeach
            @endif
        </select>
        @error('satker_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Keterangan --}}
    <div class="col-12">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="2"
                  maxlength="500">{{ old('keterangan', $pegawai->keterangan ?? '') }}</textarea>
    </div>

    {{-- Upload KTP --}}
    <div class="col-md-6">
        <label class="form-label">Upload KTP</label>
        <input type="file" name="file_ktp" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        @isset($pegawai)
            @if($pegawai->file_ktp)
                <small class="text-muted">
                    <a target="_blank" href="{{ asset('storage/'.$pegawai->file_ktp) }}">Lihat KTP</a>
                </small>
            @endif
        @endisset
    </div>

    {{-- Upload KK --}}
    <div class="col-md-6">
        <label class="form-label">Upload KK</label>
        <input type="file" name="file_kk" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        @isset($pegawai)
            @if($pegawai->file_kk)
                <small class="text-muted">
                    <a target="_blank" href="{{ asset('storage/'.$pegawai->file_kk) }}">Lihat KK</a>
                </small>
            @endif
        @endisset
    </div>

    {{-- Upload Ijazah --}}
    <div class="col-md-6">
        <label class="form-label">Upload Ijazah</label>
        <input type="file" name="file_ijazah" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        @isset($pegawai)
            @if($pegawai->file_ijazah)
                <small class="text-muted">
                    <a target="_blank" href="{{ asset('storage/'.$pegawai->file_ijazah) }}">Lihat Ijazah</a>
                </small>
            @endif
        @endisset
    </div>

</div>


{{-- Flatpickr CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
{{-- Flatpickr JS --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
{{-- Flatpickr Locale Indonesia --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Element references ──────────────────────────────────────
    const kategoriSelect   = document.getElementById('kategori_satker');
    const indukSelect      = document.getElementById('induk_satker_id');
    const subSelect        = document.getElementById('sub_satker_id');
    const pendidikanSel    = document.getElementById('pendidikan');
    const prodiSelect      = document.getElementById('prodi_id');
    const prodiWrapper     = document.getElementById('prodi_wrapper');
    const prodiLainnyaWrap = document.getElementById('prodi_lainnya_wrapper');
    const prodiLainnyaInp  = document.getElementById('prodi_lainnya_input');
    const statusK2Sel      = document.getElementById('status_k2');
    const nomorK2Wrap      = document.getElementById('nomor_k2_wrapper');
    const nomorK2Inp       = document.getElementById('nomor_k2');

    // ── Flatpickr Date Pickers ──────────────────────────────────
    const flatpickrConfig = {
        locale: 'id',
        dateFormat: 'd/m/Y',        // tampilan ke user
        altInput: false,
        allowInput: false,
        disableMobile: true,        // gunakan flatpickr juga di mobile, bukan native
        maxDate: 'today',
        onChange: function (selectedDates, dateStr, instance) {
            // Tulis ke hidden input dalam format Y-m-d untuk backend
            if (selectedDates.length > 0) {
                const d = selectedDates[0];
                const yyyy = d.getFullYear();
                const mm   = String(d.getMonth() + 1).padStart(2, '0');
                const dd   = String(d.getDate()).padStart(2, '0');
                instance._hiddenInput.value = `${yyyy}-${mm}-${dd}`;
            } else {
                instance._hiddenInput.value = '';
            }
        }
    };

    // Helper: inisialisasi flatpickr dengan nilai dari hidden input
    function initDatePicker(pickerId, hiddenId) {
        const hidden = document.getElementById(hiddenId);
        const picker = document.getElementById(pickerId);
        if (!picker || !hidden) return;

        const fp = flatpickr(picker, flatpickrConfig);
        fp._hiddenInput = hidden;

        // Set nilai awal dari hidden input (format Y-m-d)
        if (hidden.value) {
            const parts = hidden.value.split('-'); // [Y, m, d]
            if (parts.length === 3) {
                // Flatpickr setDate menerima format sesuai dateFormat
                fp.setDate(`${parts[2]}/${parts[1]}/${parts[0]}`, false);
            }
        }
    }

    initDatePicker('tgl_lahir_picker', 'tgl_lahir_hidden');
    initDatePicker('tgl_kerja_picker', 'tgl_kerja_hidden');

    // ── End Flatpickr ───────────────────────────────────────────

    const preSelectedSatker = "{{ old('satker_id', $pegawai->satker_id ?? '') }}";
    const preSelectedProdi  = "{{ old('prodi_id', $pegawai->prodi_id ?? '') }}";
    const preSelectedInduk  = "{{ old('_induk_id', (isset($pegawai) ? (optional($pegawai->satker)->level === 'induk' ? $pegawai->satker_id : optional($pegawai->satker)->parent_id) : '')) }}";

    // Master induk data passed from server
    const allIndukSatkers = @json($indukSatkers->map(fn($s) => ['id' => $s->id, 'nama_satker' => $s->nama_satker, 'tipe_satuan' => $s->tipe_satuan]));

    // ══════════════════════════════════════════════════════════════
    // 1. TRIPLE DROPDOWN: Kategori → Induk → Sub-unit
    // ══════════════════════════════════════════════════════════════

    function filterIndukByKategori(kategori, selectedId) {
        if (!indukSelect || indukSelect.tagName !== 'SELECT') return;
        indukSelect.innerHTML = '<option value="">-- Pilih Satker/Satwil --</option>';
        subSelect.innerHTML = '<option value="">-- Pilih Unit Kerja --</option>';

        if (!kategori) return;

        const filtered = allIndukSatkers.filter(s => s.tipe_satuan === kategori);
        filtered.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.nama_satker;
            if (String(s.id) === String(selectedId)) opt.selected = true;
            indukSelect.appendChild(opt);
        });
    }

    function loadSubSatker(parentId, selectedId) {
        subSelect.innerHTML = '<option value="">Memuat...</option>';
        subSelect.disabled = true;

        if (!parentId) {
            subSelect.innerHTML = '<option value="">-- Pilih Satker --</option>';
            return;
        }

        fetch(`/api/get-sub-satker/${parentId}`)
            .then(res => res.json())
            .then(data => {
                subSelect.innerHTML = '<option value="">-- Pilih Unit Kerja --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.nama_satker;
                    if (String(item.id) === String(selectedId)) opt.selected = true;
                    subSelect.appendChild(opt);
                });
                subSelect.disabled = false;
            })
            .catch(() => {
                subSelect.innerHTML = '<option value="">Gagal memuat data</option>';
            });
    }

    if (kategoriSelect && kategoriSelect.tagName === 'SELECT') {
        kategoriSelect.addEventListener('change', function () {
            filterIndukByKategori(this.value, '');
        });
    }

    if (indukSelect && indukSelect.tagName === 'SELECT') {
        indukSelect.addEventListener('change', function () {
            loadSubSatker(this.value, '');
        });
    }

    // ══════════════════════════════════════════════════════════════
    // 2. DYNAMIC PRODI by Pendidikan
    // ══════════════════════════════════════════════════════════════

    const pendidikanKategoriMap = {
        'SD':          null,         // hidden
        'SMP':         null,         // hidden
        'SMA/SMK':     'SMA/SMK',
        'D3':          'Perguruan Tinggi',
        'S1':          'Perguruan Tinggi',
        'S1 Profesi':  'Perguruan Tinggi',
        'S2':          'Perguruan Tinggi',
        'S2 Profesi':  'Perguruan Tinggi',
        'S3':          'Perguruan Tinggi',
    };

    // ID of the "Lainnya" option (detected dynamically from response)
    let lainnyaIds = [];

    function loadProdi(kategori, selectedId) {
        // Hide "Lainnya" manual input by default
        prodiLainnyaWrap.style.display = 'none';
        prodiLainnyaInp.value = '';

        if (!kategori) {
            // SD / SMP → hide prodi
            prodiWrapper.style.display = 'none';
            prodiSelect.innerHTML = '<option value="">-- Pilih Pendidikan --</option>';
            prodiSelect.value = '';
            return;
        }

        prodiWrapper.style.display = '';
        prodiSelect.innerHTML = '<option value="">Memuat...</option>';

        fetch(`/api/get-prodi?kategori=${encodeURIComponent(kategori)}`)
            .then(res => res.json())
            .then(data => {
                prodiSelect.innerHTML = '<option value="">-- Pilih Jurusan --</option>';
                lainnyaIds = [];

                data.forEach(item => {
                    // Skip "Tanpa Jurusan" from dropdown (it's for SD/SMP)
                    if (item.nama === 'Tanpa Jurusan') return;

                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.nama;
                    if (item.nama === 'Lainnya') {
                        lainnyaIds.push(String(item.id));
                    }
                    if (String(item.id) === String(selectedId)) opt.selected = true;
                    prodiSelect.appendChild(opt);
                });
            })
            .catch(() => {
                prodiSelect.innerHTML = '<option value="">Gagal memuat data</option>';
            });
    }

    // Show/hide "Lainnya" manual input when prodi dropdown changes
    prodiSelect.addEventListener('change', function () {
        if (lainnyaIds.includes(this.value)) {
            prodiLainnyaWrap.style.display = '';
            prodiLainnyaInp.focus();
        } else {
            prodiLainnyaWrap.style.display = 'none';
            prodiLainnyaInp.value = '';
        }
    });

    pendidikanSel.addEventListener('change', function () {
        const kat = pendidikanKategoriMap[this.value] ?? null;
        loadProdi(kat, '');
    });

    // ══════════════════════════════════════════════════════════════
    // 3. INITIAL LOAD (for edit mode / old() values)
    // ══════════════════════════════════════════════════════════════

    // 3a. Triple dropdown init
    const initialKategori = kategoriSelect ? kategoriSelect.value : '';
    if (initialKategori && kategoriSelect.tagName === 'SELECT') {
        filterIndukByKategori(initialKategori, preSelectedInduk);
    }

    const initialInduk = indukSelect ? indukSelect.value : '';
    if (initialInduk) {
        loadSubSatker(initialInduk, preSelectedSatker);
    }

    // 3b. Prodi init
    const initialPendidikan = pendidikanSel.value;
    if (initialPendidikan) {
        const kat = pendidikanKategoriMap[initialPendidikan] ?? null;
        loadProdi(kat, preSelectedProdi);
    }

    // 3c. K-II Init
    function toggleNomorK2() {
        if (statusK2Sel.value === 'K-II') {
            nomorK2Wrap.style.display = '';
            // Only require if visibly showing, handled via validation but good practice to add required star dynamically if needed
            // nomorK2Inp.required = true; 
        } else {
            nomorK2Wrap.style.display = 'none';
            nomorK2Inp.value = '';
            // nomorK2Inp.required = false;
        }
    }

    if (statusK2Sel) {
        statusK2Sel.addEventListener('change', toggleNomorK2);
        toggleNomorK2(); // Set initial state
    }
});
</script>