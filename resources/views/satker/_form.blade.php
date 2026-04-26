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

    <div class="col-md-12 mt-3">
        <label class="form-label">Satker Induk (Opsional, Pilih jika ini Sub-Satker)</label>
        <select name="parent_id" class="form-select">
            <option value="">-- Tidak Ada / Satker Utama --</option>
            @if(isset($parents))
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id', $satker->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->nama_satker }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-danger">
        Simpan
    </button>

    <a href="{{ route('satker.index') }}" class="btn btn-outline-secondary">
        Kembali
    </a>
</div>
