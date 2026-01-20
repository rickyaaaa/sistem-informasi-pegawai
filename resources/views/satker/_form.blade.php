@csrf

<div class="mb-3">
    <label for="nama_satker" class="form-label">Nama Satker</label>
    <input
        type="text"
        id="nama_satker"
        name="nama_satker"
        value="{{ old('nama_satker', $satker->nama_satker ?? '') }}"
        class="form-control @error('nama_satker') is-invalid @enderror"
        placeholder="Contoh: Bagian Umum"
        required
    >
    @error('nama_satker')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        Simpan
    </button>
    <a href="{{ route('satker.index') }}" class="btn btn-outline-secondary">
        Kembali
    </a>
</div>

