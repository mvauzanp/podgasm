<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 
        'nama_penerima', 
        'email', 
        'no_telp', 
        'invoice_number', 
        'total_harga', 
        'voucher_code',
        'voucher_discount',
        'metode_pembayaran', 
        'alamat_pengiriman', 
        'ongkir',
        'kurir',
        'layanan',
        'resi',
        'biteship_order_id',
        'destination_area_id',
        'status',
        'snap_token'
    ];


    public function user() {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Konfirmasi pembayaran dan kurangi stok
     * 
     * @return bool true jika sukses, false jika gagal
     */
    public function confirmPayment()
    {
        return app(\App\Services\OrderService::class)->confirmPayment($this);
    }

    /**
     * Batalkan order jika pembayaran belum dikonfirmasi
     * 
     * @return bool true jika sukses, false jika gagal
     */
    public function cancelOrder()
    {
        return app(\App\Services\OrderService::class)->cancelOrder($this);
    }

    /**
     * Check apakah order sudah dibayar
     * 
     * @return bool
     */
    public function isPaid()
    {
        $statusVal = $this->status instanceof \App\Enums\OrderStatus ? $this->status->value : $this->status;
        return $statusVal === \App\Enums\OrderStatus::PAID->value || $statusVal === 'paid';
    }

    /**
     * Check apakah order masih menunggu pembayaran
     * 
     * @return bool
     */
    public function isPendingPayment()
    {
        $statusVal = $this->status instanceof \App\Enums\OrderStatus ? $this->status->value : $this->status;
        return $statusVal === \App\Enums\OrderStatus::PENDING_PAYMENT->value || $statusVal === 'pending_payment';
    }

    /**
     * Check apakah order telah dibatalkan
     * 
     * @return bool
     */
    public function isCancelled()
    {
        $statusVal = $this->status instanceof \App\Enums\OrderStatus ? $this->status->value : $this->status;
        return $statusVal === \App\Enums\OrderStatus::CANCELLED->value || $statusVal === 'cancelled';
    }

    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($order) {
            if ($order->isForceDeleting()) {
                $order->items()->forceDelete();
            } else {
                $order->items()->delete();
            }
        });
        
        static::restoring(function ($order) {
            $order->items()->restore();
        });
    }

    /**
     * Recalculate order total price
     */
    public function recalculateTotal()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->price * $item->quantity;
        }
        $this->update(['total_harga' => $total]);
    }
}

