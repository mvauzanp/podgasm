<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case SHIPPED = 'shipped';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Mendapatkan label ramah pengguna untuk masing-masing status
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Konfirmasi',
            self::PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::PAID => 'Telah Dikonfirmasi (Sedang Diproses)',
            self::SHIPPED => 'Sedang Dalam Pengiriman',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
