<?php

namespace App\Http\Controllers;

use App\Models\PegawaiRequest;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
     * Task 15: Kirim email ke subbagpnslampung@gmail.com setelah approval.
     */
    public function approve(PegawaiRequest $approvalRequest)
    {
        if (!$approvalRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $payload = $approvalRequest->data_payload ?? [];

        // ── Bungkus dalam transaksi DB agar atomic ─────────────
        try {
            DB::transaction(function () use ($approvalRequest, $payload) {
                match ($approvalRequest->action_type) {
                    'create' => $this->applyCreate($payload),
                    'update' => $this->applyUpdate($approvalRequest->pegawai_id, $payload),
                    'delete' => $this->applyDelete($approvalRequest->pegawai_id),
                };

                $approvalRequest->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses permintaan: ' . $e->getMessage());
        }

        // ── Email di luar transaksi (non-kritis, tidak boleh rollback approval) ──
        $actionLabel = $approvalRequest->actionLabel();
        $namaLengkap = $payload['nama'] ?? ($approvalRequest->pegawai->nama ?? '-');
        $satkerName = optional($approvalRequest->satker)->nama_satker ?? '-';
        $approvedBy = auth()->user()->name;

        try {
            Mail::raw(
                "Permintaan {$actionLabel} pegawai telah disetujui.\n\n"
                . "Pegawai : {$namaLengkap}\n"
                . "Satker  : {$satkerName}\n"
                . "Disetujui oleh: {$approvedBy}\n"
                . "Waktu   : " . now()->format('d M Y H:i') . "\n\n"
                . "-- Sistem Informasi Pegawai Non ASN --",
                function ($message) {
                    $email = env('APPROVAL_NOTIFICATION_EMAIL', 'subbagpnslampung@gmail.com');
                    $message->to($email)
                        ->subject('[Approval] Permintaan Data Pegawai Disetujui');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send approval email: ' . $e->getMessage());
        }

        // Log audit trail
        Log::info('Approval granted', [
            'request_id' => $approvalRequest->id,
            'approved_by' => auth()->id(),
            'action_type' => $approvalRequest->action_type,
            'pegawai_nik' => $payload['nik'] ?? null,
        ]);

        return back()->with('success', 'Permintaan berhasil disetujui dan notifikasi email telah dikirim.');
    }

    /**
     * Reject a pending request.
     * Task 16: Minta input alasan penolakan via modal.
     */
    public function reject(Request $request, PegawaiRequest $approvalRequest)
    {
        if (!$approvalRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $request->validate([
            'keterangan' => ['required', 'string', 'max:500'],
        ], [
            'keterangan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if (in_array($approvalRequest->action_type, ['create', 'update'])) {
            $payload = $approvalRequest->data_payload ?? [];

            if (!empty($payload['foto'])) {
                Storage::disk('public')->delete($payload['foto']);
            }

            if (!empty($payload['file_ktp'])) {
                Storage::disk('public')->delete($payload['file_ktp']);
            }
            if (!empty($payload['file_kk'])) {
                Storage::disk('public')->delete($payload['file_kk']);
            }
            if (!empty($payload['file_ijazah'])) {
                Storage::disk('public')->delete($payload['file_ijazah']);
            }
        }

        $approvalRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'keterangan' => $request->input('keterangan'),
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
        if (!empty($payload['file_ktp']) && $pegawai->file_ktp && $pegawai->file_ktp !== $payload['file_ktp']) {
            Storage::disk('public')->delete($pegawai->file_ktp);
        }
        if (!empty($payload['file_kk']) && $pegawai->file_kk && $pegawai->file_kk !== $payload['file_kk']) {
            Storage::disk('public')->delete($pegawai->file_kk);
        }
        if (!empty($payload['file_ijazah']) && $pegawai->file_ijazah && $pegawai->file_ijazah !== $payload['file_ijazah']) {
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
