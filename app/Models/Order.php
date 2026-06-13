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
        'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * ✅ PERBAIKAN #4: Konfirmasi pembayaran dan kurangi stok
     * Hanya bisa dipanggil saat status = pending_payment
     * 
     * @return bool true jika sukses, false jika gagal
     */
    public function confirmPayment()
    {
        // Validasi status
        if ($this->status !== 'pending_payment') {
            return false;
        }

        try {
            DB::beginTransaction();

            // 1. Update status ke 'paid' (pembayaran sudah dikonfirmasi)
            $this->update(['status' => 'paid']);

            // 2. Kurangi stok untuk setiap item dalam order dengan Pessimistic Locking
            foreach ($this->items as $orderItem) {
                // Gunakan lockForUpdate untuk mencegah race condition
                $product = Product::where('id', $orderItem->product_id)->lockForUpdate()->firstOrFail();
                
                if ($orderItem->product_variant_id) {
                    $variant = ProductVariant::where('id', $orderItem->product_variant_id)->lockForUpdate()->firstOrFail();
                    if ($variant->stok_aktual < $orderItem->quantity) {
                        DB::rollBack();
                        return false;
                    }
                    
                    // Kurangi stok varian dan stok produk induk
                    $variant->decrement('stok_aktual', $orderItem->quantity);
                    $product->decrement('stok_aktual', $orderItem->quantity);
                } else {
                    // ✅ Validasi stok cukup (safety check terakhir)
                    if ($product->stok_aktual < $orderItem->quantity) {
                        DB::rollBack();
                        return false;
                    }
                    
                    // Kurangi stok produk induk
                    $product->decrement('stok_aktual', $orderItem->quantity);
                }

                // 3. Catat mutasi penjualan ke tabel transactions (Tinjauan Arsitektur Poin 2)
                Transaction::create([
                    'product_id'   => $orderItem->product_id,
                    'jumlah'       => $orderItem->quantity,
                    'total_harga'  => $orderItem->price * $orderItem->quantity,
                    'jenis'        => 'keluar',
                    'tanggal'      => now()
                ]);
            }

            DB::commit();
            
            // Kirim Notifikasi Pembayaran Berhasil
            $this->user->notify(new \App\Notifications\OrderStatusNotification($this, 'Pembayaran untuk pesanan Anda telah berhasil dikonfirmasi dan pesanan sedang diproses.'));

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * ✅ PERBAIKAN #4: Batalkan order jika pembayaran belum dikonfirmasi
     * Hanya bisa dibatalkan saat status = pending_payment
     * Stock tidak berkurang karena belum dikurangi dari awal
     * 
     * @return bool true jika sukses, false jika gagal
     */
    public function cancelOrder()
    {
        // Hanya bisa membatalkan order yang menunggu pembayaran
        if ($this->status !== 'pending_payment') {
            return false;
        }

        try {
            // Update status ke 'cancelled'
            $this->update(['status' => 'cancelled']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ✅ Check apakah order sudah dibayar
     * 
     * @return bool
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * ✅ Check apakah order masih menunggu pembayaran
     * 
     * @return bool
     */
    public function isPendingPayment()
    {
        return $this->status === 'pending_payment';
    }

    /**
     * ✅ Check apakah order telah dibatalkan
     * 
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
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

