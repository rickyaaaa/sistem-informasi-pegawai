<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkImportNotification extends Notification
{
    use Queueable;

    protected $operator;

    /**
     * Create a new notification instance.
     */
    public function __construct($operator)
    {
        $this->operator = $operator;
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
        return (new MailMessage)
            ->subject('📥 Notifikasi Import Excel: Pengajuan Baru')
            ->greeting('Halo Admin Polda,')
            ->line('Operator ' . $this->operator->name . ' baru saja melakukan import data pegawai dari Excel.')
            ->line('Seluruh data yang di-import telah masuk ke daftar tunggu persetujuan (Approval Center).')
            ->action('Cek Semua Pengajuan', route('approvals.index'))
            ->line('Mohon segera periksa pengajuan massal ini.')
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
            'operator_name' => $this->operator->name,
            'type' => 'bulk_import',
        ];
    }
}
