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
        <label class="form-label">Nama</label>
        <input
            type="text"
            name="nama"
            class="form-control"
            value="{{ old('nama', $pegawai->nama ?? '') }}"
            required
        >
    </div>

    {{-- NIK --}}
    <div class="col-md-6">
        <label class="form-label">NIK</label>
        <input
            type="text"
            name="nik"
            class="form-control"
            value="{{ old('nik', $pegawai->nik ?? '') }}"
            required
        >
    </div>

    {{-- Jenis Kelamin --}}
    <div class="col-md-6">
        <label class="form-label">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="form-select" required>
            <option value="">-- Pilih Jenis Kelamin --</option>
            <option value="Laki-laki" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') === 'Laki-laki')>Laki-laki</option>
            <option value="Perempuan" @selected(old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') === 'Perempuan')>Perempuan</option>
        </select>
    </div>

    {{-- Pendidikan --}}
    <div class="col-md-6">
        <label class="form-label">Pendidikan</label>
        <select name="pendidikan" class="form-select" required>
            <option value="">-- Pilih Pendidikan --</option>
            @foreach(['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S1 Profesi', 'S2', 'S2 Profesi'] as $level)
                <option value="{{ $level }}" @selected(old('pendidikan', $pegawai->pendidikan ?? '') === $level)>
                    {{ $level }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="aktif" @selected(old('status', $pegawai->status ?? '') === 'aktif')>Aktif</option>
            <option value="non_aktif" @selected(old('status', $pegawai->status ?? '') === 'non_aktif')>Non Aktif</option>
        </select>
    </div>

    {{-- Satker Induk (Tahap 1) --}}
    <div class="col-md-6">
        <label class="form-label">Satker / Satwil Induk</label>

        @if($user->isAdminSatker())
            {{-- Operator: locked to their assigned induk --}}
            <input type="hidden" id="induk_satker_id" value="{{ $user->satker_id }}">
            <input type="text" class="form-control" value="{{ $user->satker->nama_satker ?? '-' }}" disabled>
        @else
            {{-- Superadmin: choose induk --}}
            <select id="induk_satker_id" class="form-select" required>
                <option value="">-- Pilih Induk --</option>
                @foreach($indukSatkers as $induk)
                    <option
                        value="{{ $induk->id }}"
                        @selected(old('_induk_id', (isset($pegawai) ? optional($pegawai->satker)->parent_id : '')) == $induk->id)
                    >
                        {{ $induk->nama_satker }} ({{ $induk->tipe_satuan }})
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Sub-Bagian (Tahap 2) --}}
    <div class="col-md-6">
        <label class="form-label">Sub-Bagian</label>
        <select name="satker_id" id="sub_satker_id" class="form-select" required>
            <option value="">-- Pilih Induk Terlebih Dahulu --</option>
            {{-- Pre-fill for edit mode --}}
            @if(isset($subSatkers) && $subSatkers->count())
                @foreach($subSatkers as $sub)
                    <option
                        value="{{ $sub->id }}"
                        @selected(old('satker_id', $pegawai->satker_id ?? '') == $sub->id)
                    >
                        {{ $sub->nama_satker }}
                    </option>
                @endforeach
            @endif
        </select>
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

</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div>

{{-- Dependent Dropdown Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const indukSelect = document.getElementById('induk_satker_id');
    const subSelect   = document.getElementById('sub_satker_id');
    const preSelected = "{{ old('satker_id', $pegawai->satker_id ?? '') }}";

    function loadSubSatker(parentId, selectedId) {
        // Reset sub dropdown
        subSelect.innerHTML = '<option value="">Memuat...</option>';
        subSelect.disabled = true;

        if (!parentId) {
            subSelect.innerHTML = '<option value="">-- Pilih Induk Terlebih Dahulu --</option>';
            return;
        }

        fetch(`/api/get-sub-satker/${parentId}`)
            .then(res => res.json())
            .then(data => {
                subSelect.innerHTML = '<option value="">-- Pilih Sub-Bagian --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.nama_satker;
                    if (String(item.id) === String(selectedId)) {
                        opt.selected = true;
                    }
                    subSelect.appendChild(opt);
                });
                subSelect.disabled = false;
            })
            .catch(() => {
                subSelect.innerHTML = '<option value="">Gagal memuat data</option>';
            });
    }

    // Listen for change on induk dropdown (works for both select and hidden input)
    if (indukSelect.tagName === 'SELECT') {
        indukSelect.addEventListener('change', function () {
            loadSubSatker(this.value, '');
        });
    }

    // Auto-load sub-units on page load if an induk is already selected
    const initialInduk = indukSelect.value;
    if (initialInduk) {
        loadSubSatker(initialInduk, preSelected);
    }
});
</script>