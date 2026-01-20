@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label for="nama" class="form-label">Nama</label>
        <input
            type="text"
            id="nama"
            name="nama"
            value="{{ old('nama', $pegawai->nama ?? '') }}"
            class="form-control @error('nama') is-invalid @enderror"
            required
        >
        @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="nik" class="form-label">NIK</label>
        <input
            type="text"
            id="nik"
            name="nik"
            value="{{ old('nik', $pegawai->nik ?? '') }}"
            class="form-control @error('nik') is-invalid @enderror"
            required
        >
        @error('nik')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="pendidikan" class="form-label">Pendidikan</label>
        <input
            type="text"
            id="pendidikan"
            name="pendidikan"
            value="{{ old('pendidikan', $pegawai->pendidikan ?? '') }}"
            class="form-control @error('pendidikan') is-invalid @enderror"
            placeholder="Contoh: SMA/SMK, D3, S1, S2"
            required
        >
        @error('pendidikan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @php($selectedStatus = old('status', $pegawai->status ?? 'aktif'))
            <option value="aktif" @selected($selectedStatus === 'aktif')>Aktif</option>
            <option value="non_aktif" @selected($selectedStatus === 'non_aktif')>Non Aktif</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Satker</label>

        @if($user->isAdminSatker())
            <input type="hidden" name="satker_id" value="{{ $user->satker_id }}">
            <input
                type="text"
                class="form-control"
                value="{{ $satkers->firstWhere('id', $user->satker_id)?->nama_satker ?? '-' }}"
                disabled
            >
            <div class="form-text">Satker otomatis mengikuti akun Admin Satker.</div>
        @else
            <select name="satker_id" class="form-select @error('satker_id') is-invalid @enderror" required>
                <option value="">-- Pilih Satker --</option>
                @php($selectedSatker = old('satker_id', $pegawai->satker_id ?? ''))
                @foreach($satkers as $satker)
                    <option value="{{ $satker->id }}" @selected((string)$selectedSatker === (string)$satker->id)>
                        {{ $satker->nama_satker }}
                    </option>
                @endforeach
            </select>
            @error('satker_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @endif
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">
        Simpan
    </button>
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary">
        Kembali
    </a>
</div>

