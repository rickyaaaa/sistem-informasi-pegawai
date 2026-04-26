@extends('layouts.admin')

@section('title', 'Manajemen User')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Daftar Operator</h5>
        <small class="text-muted">Total {{ $users->total() }} operator terdaftar</small>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah User
    </a>
</div>

{{-- ── Filter bar ── --}}
<form method="GET" action="{{ route('users.index') }}" class="mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="q" value="{{ request('q') }}"
                   class="form-control form-control-sm"
                   placeholder="Cari nama atau username...">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select form-select-sm">
                <option value="">-- Semua Role --</option>
                <option value="super_admin"  {{ request('role') === 'super_admin'  ? 'selected' : '' }}>ADMIN POLDA</option>
                <option value="admin_satker" {{ request('role') === 'admin_satker' ? 'selected' : '' }}>OPERATOR</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">-- Semua Status --</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non Aktif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-danger flex-grow-1">
                <i class="bi bi-search"></i>
            </button>
            @if(request()->hasAny(['q','role','status']))
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</form>

{{-- ── Tabel Data & Form Bulk Delete ── --}}
<form id="bulkDeleteForm" action="{{ route('users.bulk-destroy') }}" method="POST">
    @csrf
    @method('DELETE')

    <div class="mb-3">
        <button type="submit" id="btnBulkDelete" class="btn btn-sm btn-danger d-none" onclick="return confirm('Apakah Anda yakin ingin menghapus user yang dipilih?')">
            <i class="bi bi-trash3 me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
        </button>
    </div>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th style="width:40px;" class="text-center">
                            <input type="checkbox" id="checkAll" class="form-check-input">
                        </th>
                        <th style="width:50px;">#</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Satker</th>
                        <th style="width:100px;">Status</th>
                        <th class="text-end" style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php /** @var \App\Models\User $user */ @endphp
                        <tr>
                            <td class="text-center">
                                <input type="checkbox"
                                       name="ids[]"
                                       value="{{ $user->id }}"
                                       class="form-check-input checkItem"
                                       {{ $user->isSuperAdmin() || $user->id === auth()->id() ? 'disabled' : '' }}>
                            </td>
                            <td class="text-muted">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm"
                                         style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#dc2626,#991b1b);
                                                color:#fff;display:flex;align-items:center;justify-content:center;
                                                font-size:13px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="fw-medium">{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:10px;">Anda</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-muted small">{{ $user->username }}</td>

                            <td>
                                @if($user->isSuperAdmin())
                                    <span class="badge bg-dark px-2">ADMIN POLDA</span>
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2">OPERATOR</span>
                                @endif
                            </td>

                            <td class="small">{{ $user->satker->nama_satker ?? '—' }}</td>

                            <td>
                                {{-- Toggle status button --}}
                                <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="badge border-0 {{ $user->isActive() ? 'bg-success' : 'bg-danger' }}"
                                            style="cursor:pointer;padding:5px 10px;"
                                            title="{{ $user->isActive() ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}"
                                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        {{ $user->isActive() ? 'Aktif' : 'Non Aktif' }}
                                    </button>
                                </form>
                            </td>

                            <td class="text-end">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                @if(! $user->isSuperAdmin() && $user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus user {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
                                Tidak ada user ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</form>

<div class="mt-3">
    {{ $users->links() }}
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const checkItems = document.querySelectorAll('.checkItem:not([disabled])');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCount = document.getElementById('selectedCount');

    function toggleBulkDeleteButton() {
        const checked = document.querySelectorAll('.checkItem:checked');
        if (checked.length > 0) {
            btnBulkDelete.classList.remove('d-none');
            selectedCount.textContent = checked.length;
        } else {
            btnBulkDelete.classList.add('d-none');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkItems.forEach(item => {
                item.checked = this.checked;
            });
            toggleBulkDeleteButton();
        });
    }

    checkItems.forEach(item => {
        item.addEventListener('change', function() {
            // Update checkAll state
            const allChecked = Array.from(checkItems).every(i => i.checked);
            const someChecked = Array.from(checkItems).some(i => i.checked);

            if (checkAll) {
                checkAll.checked = allChecked;
                checkAll.indeterminate = someChecked && !allChecked;
            }

            toggleBulkDeleteButton();
        });
    });
});
</script>
@endpush

@endsection
