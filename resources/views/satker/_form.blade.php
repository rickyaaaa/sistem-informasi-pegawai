<div class="row g-3">

    <div class="col-md-12">
        <label class="form-label">Nama Satker</label>
        <input
            type="text"
            name="nama_satker"
            class="form-control"
            value="{{ old('nama_satker', $satker->nama_satker ?? '') }}"
            required
        >
    </div>

</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        Simpan
    </button>

    <a href="{{ route('satker.index') }}" class="btn btn-outline-secondary">
        Kembali
    </a>
</div>
