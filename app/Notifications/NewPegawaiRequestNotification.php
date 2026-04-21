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
            ->subject('🔔 Pengajuan Approval Baru: ' . $action . ' Pegawai')
            ->greeting('Halo Admin Polda,')
            ->line('Ada pengajuan baru yang memerlukan persetujuan Anda.')
            ->level('info')
            ->line('**Detail Pengajuan:**')
            ->line('• **Operator:** ' . $operator)
            ->line('• **Satker:** ' . $satker)
            ->line('• **Jenis Aksi:** ' . $action)
            ->line('• **Nama Pegawai:** ' . $namaPegawai)
            ->line('• **NIK:** ' . $nikPegawai)
            ->action('Buka Approval Center', route('approvals.index'))
            ->line('Mohon segera tinjau pengajuan ini pada sistem HRM.')
            ->salutation('Salam, Sistem HRM Pegawai');
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
