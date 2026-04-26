@extends('layouts.admin')

@section('title', 'APPROVAL PEGAWAI')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Approval Permintaan Pegawai</h5>
        <small class="text-muted">Kelola semua permintaan create / update / delete dari admin satker</small>
    </div>
</div>

{{-- ── Filter Tabs ── --}}
<div class="mb-4">
    <div class="btn-group" role="group">
        <a href="{{ route('approval.index') }}"
           class="btn btn-sm {{ !request('status') ? 'btn-secondary text-white' : 'btn-outline-secondary' }}">
            Semua
        </a>
        <a href="{{ route('approval.index', ['status' => 'pending']) }}"
           class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
            Pending
            @php $cnt = \App\Models\PegawaiRequest::where('status','pending')->count(); @endphp
            @if($cnt > 0)
                <span class="badge bg-dark ms-1">{{ $cnt }}</span>
            @endif
        </a>
        <a href="{{ route('approval.index', ['status' => 'approved']) }}"
           class="btn btn-sm {{ request('status') === 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">
            Approved
        </a>
        <a href="{{ route('approval.index', ['status' => 'rejected']) }}"
           class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">
            Rejected
        </a>
    </div>
</div>

{{-- ── Table ── --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Pegawai / Data</th>
                        <th>Satker</th>
                        <th style="width:100px;">Aksi</th>
                        <th>Diajukan Oleh</th>
                        <th>Tanggal</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:180px;" class="text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            {{-- No --}}
                            <td class="text-muted">
                                {{ ($requests->currentPage() - 1) * $requests->perPage() + $loop->iteration }}
                            </td>

                            {{-- Pegawai / Data --}}
                            <td>
                                @if($req->action_type === 'create')
                                    <span class="text-muted fst-italic">(Pegawai Baru)</span>
                                    <div class="small text-muted mt-1">
                                        {{ $req->data_payload['nama'] ?? '-' }}
                                        &middot; NIK: {{ $req->data_payload['nik'] ?? '-' }}
                                    </div>
                                @elseif($req->pegawai)
                                    <span class="fw-medium">{{ $req->pegawai->nama }}</span>
                                    <div class="small text-muted">NIK: {{ $req->pegawai->nik }}</div>
                                @else
                                    <span class="text-muted fst-italic">Pegawai tidak ditemukan</span>
                                @endif
                            </td>

                            {{-- Satker --}}
                            <td>{{ $req->satker->nama_satker ?? '-' }}</td>

                            {{-- Action Type --}}
                            <td>
                                @if($req->action_type === 'create')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">
                                        <i class="bi bi-plus-circle me-1"></i>Tambah
                                    </span>
                                @elseif($req->action_type === 'update')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2">
                                        <i class="bi bi-pencil me-1"></i>Ubah
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">
                                        <i class="bi bi-trash3 me-1"></i>Hapus
                                    </span>
                                @endif
                            </td>

                            {{-- Requested By --}}
                            <td>
                                <span class="fw-medium">{{ $req->requestedBy->name ?? '-' }}</span>
                            </td>

                            {{-- Date --}}
                            <td class="small text-muted">
                                {{ $req->created_at->format('d M Y H:i') }}
                            </td>

                            {{-- Status Badge --}}
                            <td>
                                @if($req->isPending())
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i>Pending
                                    </span>
                                @elseif($req->isApproved())
                                    <span class="badge bg-success px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i>Approved
                                    </span>
                                @else
                                    <span class="badge bg-danger px-2 py-1">
                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                    </span>
                                    @if(!empty($req->keterangan))
                                        <div class="small text-muted mt-1" style="font-size:11px;">{{ $req->keterangan }}</div>
                                    @endif
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end">
                                @if($req->isPending())
                                    <div class="d-flex gap-1 justify-content-end">
                                        <form action="{{ route('approval.approve', $req) }}"
                                              method="POST"
                                              onsubmit="return confirm('Setujui permintaan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>

                                        {{-- Reject via modal --}}
                                        <button type="button" class="btn btn-danger btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal"
                                                data-req-id="{{ $req->id }}"
                                                data-req-url="{{ route('approval.reject', $req) }}">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </div>
                                @else
                                    <div class="text-end small text-muted lh-sm">
                                        <div class="fw-medium">{{ $req->approvedBy->name ?? '-' }}</div>
                                        <div>{{ $req->approved_at?->format('d M Y H:i') }}</div>
                                    </div>
                                @endif
                            </td>

                        </tr>

                        {{-- Payload detail row --}}
                        @if($req->action_type !== 'delete' && $req->data_payload)
                        <tr class="table-light small">
                            <td></td>
                            <td colspan="7" class="py-2 text-muted">
                                <strong>Payload:</strong>
                                Nama: {{ $req->data_payload['nama'] ?? '-' }}
                                &middot; NIK: {{ $req->data_payload['nik'] ?? '-' }}
                                &middot; Pendidikan: {{ $req->data_payload['pendidikan'] ?? '-' }}
                                &middot; Status: {{ $req->data_payload['status'] ?? '-' }}
                                @if(!empty($req->data_payload['keterangan']))
                                    &middot; Ket: {{ $req->data_payload['keterangan'] }}
                                @endif
                                @if(!empty($req->data_payload['file_ktp']))
                                    @php
                                        $ktpPegawai = $req->pegawai;
                                    @endphp
                                    &middot; KTP: <a target="_blank"
                                        href="{{ $ktpPegawai ? route('pegawai.file.show', [$ktpPegawai, 'ktp']) : asset('storage/' . $req->data_payload['file_ktp']) }}"
                                        class="text-danger">Lihat</a>
                                @endif
                                @if(!empty($req->data_payload['file_kk']))
                                    &middot; KK: <a target="_blank"
                                        href="{{ $ktpPegawai ?? ($req->pegawai) ? route('pegawai.file.show', [$req->pegawai ?? $ktpPegawai, 'kk']) : asset('storage/' . $req->data_payload['file_kk']) }}"
                                        class="text-danger">Lihat</a>
                                @endif
                                @if(!empty($req->data_payload['file_ijazah']))
                                    &middot; Ijazah: <a target="_blank"
                                        href="{{ $req->pegawai ? route('pegawai.file.show', [$req->pegawai, 'ijazah']) : asset('storage/' . $req->data_payload['file_ijazah']) }}"
                                        class="text-danger">Lihat Ijazah</a>
                                @endif
                            </td>
                        </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                Tidak ada permintaan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-3">
    {{ $requests->links() }}
</div>

{{-- ── Reject Modal ── --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-x-circle me-2"></i> Tolak Permintaan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="keterangan" class="form-control" rows="3"
                                  placeholder="Masukkan alasan penolakan..." required></textarea>
                        <small class="text-muted">Alasan ini akan ditampilkan pada daftar permintaan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i> Tolak Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const rejectModal = document.getElementById('rejectModal');
rejectModal.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const url = btn.getAttribute('data-req-url');
    document.getElementById('rejectForm').action = url;
    // Clear textarea
    rejectModal.querySelector('textarea[name="keterangan"]').value = '';
});
</script>
@endpush

@endsection
