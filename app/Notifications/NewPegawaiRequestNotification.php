<?php

namespace App\Notifications;

use App\Models\PegawaiRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPegawaiRequestNotification extends Notification
{
    use Queueable;

    protected $request;

    /**
     * Create a new notification instance.
     */
    public function __construct(PegawaiRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $operator = $this->request->requestedBy->name;
        $satker   = $this->request->satker->nama_satker;
        $action   = $this->request->actionLabel();
        
        // Data info (from payload)
        $namaPegawai = $this->request->data_payload['nama'] ?? 'Unknown';
        $nikPegawai  = $this->request->data_payload['nik'] ?? '-';

        return (new MailMessage)
            ->subject('🔔 Notifikasi HRM: Pengajuan Approval ' . $action)
            ->greeting('Yth. Admin Polda,')
            ->line('Kami informasikan bahwa terdapat pengajuan perubahan data pegawai baru yang memerlukan tinjauan dan persetujuan Anda segera.')
            ->level('info')
            ->line('**Rincian Pengajuan:**')
            ->line('• **Dibuat Oleh:** ' . $operator)
            ->line('• **Satker Asal:** ' . $satker)
            ->line('• **Tipe Perubahan:** ' . $action)
            ->line('• **Nama Pegawai:** ' . $namaPegawai)
            ->line('• **NIK:** ' . $nikPegawai)
            ->action('Proses Persetujuan Sekarang', route('approvals.index'))
            ->line('Terima kasih atas kerja sama Anda dalam menjaga integritas data kepegawaian.')
            ->salutation('Hormat Kami, Tim IT HRM Polda');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pegawai_request_id' => $this->request->id,
            'action_type' => $this->request->action_type,
            'requested_by' => $this->request->requested_by,
        ];
    }
}
