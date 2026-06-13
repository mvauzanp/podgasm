<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
{
    use Queueable;

    public $order;
    public $statusMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct($order, $statusMessage)
    {
        $this->order = $order;
        $this->statusMessage = $statusMessage;
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update Status Pesanan: ' . $this->order->invoice_number)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line($this->statusMessage)
            ->line('No Invoice: ' . $this->order->invoice_number)
            ->line('Total: Rp ' . number_format($this->order->total_harga, 0, ',', '.'))
            ->action('Lihat Detail Pesanan', url('/history/' . $this->order->id))
            ->line('Terima kasih telah berbelanja di Podgasm HQ!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
