@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i>
                <span>Edit User: {{ $user->name }}</span>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('users.update', $user) }}" id="userForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Username --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" name="username"
                                   class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', $user->username) }}" required>
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Password (optional) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Password
                                <small class="text-muted fw-normal">(kosongkan jika tidak diganti)</small>
                            </label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        {{-- Role --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" id="roleSelect"
                                    class="form-select @error('role') is-invalid @enderror"
                                    required {{ $user->isSuperAdmin() && $user->id === auth()->id() ? 'disabled' : '' }}>
                                <option value="super_admin"  {{ old('role', $user->role) === 'super_admin'  ? 'selected' : '' }}>Super Admin</option>
                                <option value="admin_satker" {{ old('role', $user->role) === 'admin_satker' ? 'selected' : '' }}>Operator</option>
                            </select>
                            {{-- hidden field when disabled to keep value submitted --}}
                            @if($user->isSuperAdmin() && $user->id === auth()->id())
                                <input type="hidden" name="role" value="super_admin">
                            @endif
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status"
                                    class="form-select @error('status') is-invalid @enderror"
                                    required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                <option value="active"   {{ old('status', $user->status) === 'active'   ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                            @if($user->id === auth()->id())
                                <input type="hidden" name="status" value="{{ $user->status }}">
                                <small class="text-muted">Tidak dapat mengubah status akun sendiri</small>
                            @endif
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Satker --}}
                        <div class="col-md-12" id="satkerField"
                             style="{{ old('role', $user->role) === 'admin_satker' ? '' : 'display:none;' }}">
                            <label class="form-label fw-semibold">
                                Satker <span class="text-danger">*</span>
                            </label>
                            <select name="satker_id"
                                    class="form-select @error('satker_id') is-invalid @enderror">
                                <option value="">-- Pilih Satker --</option>
                                @foreach($satkers as $satker)
                                    <option value="{{ $satker->id }}"
                                        {{ old('satker_id', $user->satker_id) == $satker->id ? 'selected' : '' }}>
                                        {{ $satker->nama_satker }}
                                    </option>
                                @endforeach
                            </select>
                            @error('satker_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    const roleSelect   = document.getElementById('roleSelect');
    const satkerField  = document.getElementById('satkerField');
    const satkerSelect = satkerField ? satkerField.querySelector('select') : null;

    function toggleSatker() {
        if (!roleSelect || !satkerField) return;
        const show = roleSelect.value === 'admin_satker';
        satkerField.style.display = show ? '' : 'none';
        if (satkerSelect) satkerSelect.required = show;
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', toggleSatker);
        toggleSatker();
    }
</script>
@endpush

@endsection
