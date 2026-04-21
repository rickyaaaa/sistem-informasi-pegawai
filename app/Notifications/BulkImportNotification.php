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
            ->subject('📥 Notifikasi HRM: Pengajuan Import Massal')
            ->greeting('Yth. Admin Polda,')
            ->line('Kami informasikan bahwa Operator **' . $this->operator->name . '** baru saja melakukan import data pegawai dalam jumlah banyak melalui file Excel.')
            ->line('Seluruh data tersebut kini telah mengantri di sistem untuk menunggu persetujuan Anda.')
            ->action('Periksa Semua Pengajuan', route('approval.index'))
            ->line('Mohon luangkan waktu untuk meninjau kecocokan data tersebut.')
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
            'operator_name' => $this->operator->name,
            'type' => 'bulk_import',
        ];
    }
}
