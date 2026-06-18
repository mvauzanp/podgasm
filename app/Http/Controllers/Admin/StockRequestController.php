<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StockApprovalRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StockRequestController extends Controller
{
    /**
     * Menampilkan semua permintaan stok dari berbagai cabang
     */
    public function index()
    {
        // Eager load product, variant, dan user (untuk nama cabang/admin cabang)
        $requests = StockRequest::with(['product', 'variant', 'user'])
                    ->latest()
                    ->paginate(10, ['*'], 'requests_page');

        // Ambil pesanan B2B dari cabang (metode_pembayaran = 'branch_request')
        $branchOrders = \App\Models\Order::with(['user', 'items.product', 'items.variant'])
                        ->where('metode_pembayaran', 'branch_request')
                        ->latest()
                        ->paginate(10, ['*'], 'orders_page');

        return view('pages.admin.stock-request.index', compact('requests', 'branchOrders'));
    }

    /**
     * Menyetujui permintaan dan memperbarui status
     */
    public function approve(StockApprovalRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $stockRequest = StockRequest::with(['product', 'variant'])->findOrFail($id);
            
            if ($stockRequest->status !== 'Pending') {
                return redirect()->back()->withErrors(['msg' => 'Permintaan ini sudah diproses sebelumnya.']);
            }

            $jumlahApproved = (int) $request->jumlah;

            // Kurangi stok dengan Pessimistic Locking
            if ($stockRequest->product_variant_id) {
                $variant = \App\Models\ProductVariant::where('id', $stockRequest->product_variant_id)->lockForUpdate()->first();
                if (!$variant) {
                    return redirect()->back()->withErrors(['msg' => 'Varian produk tidak ditemukan.']);
                }
                if ($variant->stok_aktual < $jumlahApproved) {
                    return redirect()->back()->withErrors(['msg' => 'Stok varian di pusat tidak mencukupi untuk memenuhi permintaan ini.']);
                }
                
                $product = Product::where('id', $stockRequest->product_id)->lockForUpdate()->first();
                if (!$product) {
                    return redirect()->back()->withErrors(['msg' => 'Produk tidak ditemukan.']);
                }

                $variant->decrement('stok_aktual', $jumlahApproved);
                $product->decrement('stok_aktual', $jumlahApproved);
                
                $price = $variant->getB2bPrice($jumlahApproved);
            } else {
                $product = Product::where('id', $stockRequest->product_id)->lockForUpdate()->first();
                if (!$product) {
                    return redirect()->back()->withErrors(['msg' => 'Produk tidak ditemukan.']);
                }
                if ($product->stok_aktual < $jumlahApproved) {
                    return redirect()->back()->withErrors(['msg' => 'Stok pusat tidak mencukupi untuk memenuhi permintaan ini.']);
                }
                
                $product->decrement('stok_aktual', $jumlahApproved);
                $price = $product->harga_jual;
            }

            // Catat mutasi persetujuan ke tabel transactions (Tinjauan Arsitektur Poin 2)
            \App\Models\Transaction::create([
                'product_id'   => $stockRequest->product_id,
                'jumlah'       => $jumlahApproved,
                'total_harga'  => $price * $jumlahApproved,
                'jenis'        => 'B2B',
                'tanggal'      => now()
            ]);

            $stockRequest->update([
                'status' => 'Dikirim',
                'jumlah' => $jumlahApproved,
                'keterangan_admin' => $request->keterangan_admin,
                'tgl_estimasi' => $request->tgl_estimasi
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Permintaan disetujui, stok pusat dikurangi, dan status berubah jadi Dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock Request Approval Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Gagal memproses permintaan: Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Menolak permintaan stok
     */
    public function reject($id)
    {
        try {
            $stockRequest = StockRequest::findOrFail($id);
            
            if ($stockRequest->status !== 'Pending') {
                return redirect()->back()->withErrors(['msg' => 'Permintaan ini sudah diproses sebelumnya.']);
            }

            $stockRequest->update([
                'status' => 'Ditolak'
            ]);

            return redirect()->back()->with('success', 'Permintaan stok telah ditolak.');
        } catch (\Exception $e) {
            Log::error('Stock Request Rejection Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Gagal menolak permintaan: Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Menampilkan detail pesanan B2B Cabang
     */
    public function showOrder($id)
    {
        $order = \App\Models\Order::with(['user', 'items.product', 'items.variant'])->findOrFail($id);
        
        // Proteksi untuk memastikan ini adalah B2B branch request
        if ($order->metode_pembayaran !== 'branch_request') {
            abort(404, 'Pesanan bukan merupakan permintaan stok cabang.');
        }
        
        return view('pages.admin.stock-request.show-order', compact('order'));
    }

    /**
     * Update status dan kuantitas pesanan B2B Cabang
     */
    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:paid,shipped,completed,cancelled'
        ]);

        try {
            DB::beginTransaction();

            $order = \App\Models\Order::with(['user', 'items.product', 'items.variant'])->findOrFail($id);

            if ($order->metode_pembayaran !== 'branch_request') {
                abort(400, 'Tindakan tidak valid.');
            }

            // 1. Jika masih pending_payment, izinkan edit kuantitas sebelum dikonfirmasi
            if ($order->status === 'pending_payment') {

                // 1a. Hapus item yang ditandai admin untuk dihilangkan dari invoice
                if ($request->has('delete_items') && is_array($request->delete_items)) {
                    $deleteIds = array_map('intval', $request->delete_items);
                    \App\Models\OrderItem::whereIn('id', $deleteIds)
                        ->where('order_id', $order->id) // pastikan hanya item milik order ini
                        ->delete();
                    $order->load('items');
                }

                // 1b. Update kuantitas item yang tidak dihapus
                if ($request->has('items') && is_array($request->items)) {
                    $deletedIds = $request->has('delete_items') ? array_map('intval', $request->delete_items) : [];
                    foreach ($request->items as $itemId => $itemData) {
                        if (in_array((int)$itemId, $deletedIds)) continue; // skip item yg sudah dihapus
                        $orderItem = \App\Models\OrderItem::find($itemId);
                        if ($orderItem && isset($itemData['quantity'])) {
                            $newQty = (int)$itemData['quantity'];
                            if ($newQty >= 1) {
                                $orderItem->update(['quantity' => $newQty]);
                            }
                        }
                    }
                    $order->load('items');
                }


                // Tambah barang ekstra yang tidak ada di invoice asli
                if ($request->has('extra_items') && is_array($request->extra_items)) {
                    foreach ($request->extra_items as $extra) {
                        $productId  = isset($extra['product_id'])  ? (int)$extra['product_id']  : 0;
                        $variantId  = isset($extra['variant_id'])  ? (int)$extra['variant_id']  : null;
                        $extraQty   = isset($extra['quantity'])    ? (int)$extra['quantity']     : 0;

                        if (!$productId || $extraQty < 1) continue;

                        $product = Product::find($productId);
                        if (!$product) continue;

                        // Cek apakah item yang sama sudah ada di order (hindari duplikat)
                        $existing = $order->items->first(function ($item) use ($productId, $variantId) {
                            return $item->product_id == $productId && $item->product_variant_id == $variantId;
                        });

                        if ($existing) {
                            // Tambahkan ke kuantitas yang sudah ada
                            $existing->update(['quantity' => $existing->quantity + $extraQty]);
                        } else {
                            // Hitung harga B2B
                            if ($variantId) {
                                $variant = \App\Models\ProductVariant::find($variantId);
                                $price   = $variant ? $variant->getB2bPrice($extraQty) : $product->getB2bPrice($extraQty);
                            } else {
                                $variant = null;
                                $price   = $product->getB2bPrice($extraQty);
                            }

                            \App\Models\OrderItem::create([
                                'order_id'           => $order->id,
                                'product_id'         => $productId,
                                'product_variant_id' => $variantId ?: null,
                                'quantity'           => $extraQty,
                                'price'              => $price,
                            ]);
                        }
                    }
                    $order->load('items');
                }

                // Validasi stok gudang untuk seluruh item (baik asli maupun ekstra)
                foreach ($order->fresh(['items.product', 'items.variant'])->items as $item) {
                    $availableStock = $item->product_variant_id 
                        ? ($item->variant->stok_aktual ?? 0) 
                        : ($item->product->stok_aktual ?? 0);
                    if ($item->quantity > $availableStock) {
                        DB::rollBack();
                        $itemName = $item->product_variant_id 
                            ? "{$item->product->nama_barang} ({$item->variant->nama_varian})" 
                            : $item->product->nama_barang;
                        return redirect()->back()->withErrors(['msg' => "Jumlah permintaan untuk {$itemName} melebihi stok gudang yang tersedia ({$availableStock} unit)."]);
                    }
                }

                $order->recalculateTotal();

                // Kurangi stok jika status diubah ke paid, shipped, atau completed
                if (in_array($request->status, ['paid', 'shipped', 'completed'])) {
                    if (!$order->confirmPayment()) {
                        DB::rollBack();
                        return redirect()->back()->withErrors(['msg' => 'Gagal menyetujui pesanan cabang: Stok gudang pusat tidak mencukupi untuk memenuhi kuantitas permintaan baru.']);
                    }
                }
            }

            // 2. Update status akhir
            if ($request->status === 'cancelled') {
                if ($order->status === 'pending_payment') {
                    $order->cancelOrder();
                } else {
                    // Jika sebelumnya sudah disetujui (stok dipotong) lalu dibatalkan, kembalikan stok pusat
                    if (in_array($order->status, ['paid', 'shipped', 'completed'])) {
                        foreach ($order->items as $item) {
                            $product = Product::find($item->product_id);
                            if ($item->product_variant_id) {
                                $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                                if ($variant) {
                                    $variant->increment('stok_aktual', $item->quantity);
                                }
                            }
                            if ($product) {
                                $product->increment('stok_aktual', $item->quantity);
                            }
                        }
                    }
                    $order->update(['status' => 'cancelled']);
                }
            } else {
                if ($order->status !== $request->status) {
                    $order->update(['status' => $request->status]);
                }
            }

            DB::commit();

            // Kirim Notifikasi Update Status ke Cabang
            $statusLabels = [
                'pending_payment' => 'menunggu persetujuan admin',
                'paid' => 'telah disetujui & sedang diproses',
                'shipped' => 'sedang dalam pengiriman',
                'completed' => 'telah selesai (diterima di cabang)',
                'cancelled' => 'dibatalkan / ditolak oleh admin'
            ];

            $label = $statusLabels[$request->status] ?? $request->status;
            try {
                $order->user->notify(new \App\Notifications\OrderStatusNotification($order, 'Status permintaan stok cabang Anda (' . $order->invoice_number . ') telah diperbarui menjadi: ' . $label . '.'));
            } catch (\Exception $e) {
                Log::warning('Gagal mengirim email notifikasi update status B2B: ' . $e->getMessage());
            }

            return redirect()->back()->with('success', 'Status permintaan stok B2B berhasil diupdate menjadi: ' . ($statusLabels[$request->status] ?? $request->status));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock Request B2B Update Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Gagal memperbarui permintaan stok: ' . $e->getMessage()]);
        }
    }
}