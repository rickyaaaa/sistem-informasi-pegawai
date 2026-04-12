<?php

namespace App\Http\Controllers;

use App\Models\PegawaiRequest;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
    /**
     * List all pegawai requests (all statuses), newest first.
     */
    public function index(Request $request)
    {
        $query = PegawaiRequest::with(['pegawai', 'satker', 'requestedBy', 'approvedBy'])
            ->latest();

        // Optional filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('approval.index', compact('requests'));
    }

    /**
     * Approve a pending request and apply the action.
     */
    public function approve(PegawaiRequest $approvalRequest)
    {
        if (! $approvalRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $payload = $approvalRequest->data_payload ?? [];

        try {
            match ($approvalRequest->action_type) {
                'create' => $this->applyCreate($payload),
                'update' => $this->applyUpdate($approvalRequest->pegawai_id, $payload),
                'delete' => $this->applyDelete($approvalRequest->pegawai_id),
            };
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses permintaan: ' . $e->getMessage());
        }

        $approvalRequest->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Permintaan berhasil disetujui.');
    }

    /**
     * Reject a pending request.
     */
    public function reject(PegawaiRequest $approvalRequest)
    {
        if (! $approvalRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        // If files were uploaded speculatively, delete them
        if (in_array($approvalRequest->action_type, ['create', 'update'])) {
            $payload = $approvalRequest->data_payload ?? [];
            if (! empty($payload['file_ktp'])) {
                Storage::disk('public')->delete($payload['file_ktp']);
            }
            if (! empty($payload['file_kk'])) {
                Storage::disk('public')->delete($payload['file_kk']);
            }
            if (! empty($payload['file_ijazah'])) {
                Storage::disk('public')->delete($payload['file_ijazah']);
            }
        }

        $approvalRequest->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Permintaan berhasil ditolak.');
    }

    // ── Private helpers ──────────────────────────────────────────

    private function applyCreate(array $payload): void
    {
        Pegawai::create($payload);
    }

    private function applyUpdate(int $pegawaiId, array $payload): void
    {
        $pegawai = Pegawai::findOrFail($pegawaiId);

        // If new files are in the payload, delete old ones
        if (! empty($payload['file_ktp']) && $pegawai->file_ktp && $pegawai->file_ktp !== $payload['file_ktp']) {
            Storage::disk('public')->delete($pegawai->file_ktp);
        }
        if (! empty($payload['file_kk']) && $pegawai->file_kk && $pegawai->file_kk !== $payload['file_kk']) {
            Storage::disk('public')->delete($pegawai->file_kk);
        }
        if (! empty($payload['file_ijazah']) && $pegawai->file_ijazah && $pegawai->file_ijazah !== $payload['file_ijazah']) {
            Storage::disk('public')->delete($pegawai->file_ijazah);
        }

        $pegawai->update($payload);
    }

    private function applyDelete(int $pegawaiId): void
    {
        $pegawai = Pegawai::findOrFail($pegawaiId);

        // Clean up files on soft delete
        if ($pegawai->file_ktp) {
            Storage::disk('public')->delete($pegawai->file_ktp);
        }
        if ($pegawai->file_kk) {
            Storage::disk('public')->delete($pegawai->file_kk);
        }
        if ($pegawai->file_ijazah) {
            Storage::disk('public')->delete($pegawai->file_ijazah);
        }

        $pegawai->delete(); // SoftDelete
    }
}
