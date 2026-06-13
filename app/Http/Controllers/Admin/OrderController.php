<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Menampilkan semua pesanan dari semua customer
     */
    public function index(Request $request)
    {
        $activeType = $request->input('type', 'b2c');

        // Hitung jumlah pesanan yang belum diproses (pending / pending_payment)
        $unprocessedB2CCount = Order::whereIn('status', ['pending', 'pending_payment'])
            ->where('metode_pembayaran', '!=', 'branch_request')
            ->count();
            
        $unprocessedB2BCount = Order::whereIn('status', ['pending', 'pending_payment'])
            ->where('metode_pembayaran', 'branch_request')
            ->count();

        $query = Order::with(['user', 'items.product', 'items.variant']);

        // Filter B2C vs B2B
        if ($activeType === 'b2b') {
            $query->where('metode_pembayaran', 'branch_request');
        } else {
            $query->where('metode_pembayaran', '!=', 'branch_request');
        }

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search berdasarkan invoice number atau customer name
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($subQ) use ($search) {
                      $subQ->where('name', 'like', '%' . $search . '%')
                           ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        $orders = $query->latest()->paginate(10);
        
        return view('pages.admin.orders.index', compact('orders', 'unprocessedB2CCount', 'unprocessedB2BCount', 'activeType'));
    }

    /**
     * Menampilkan detail pesanan
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.product', 'items.variant'])->findOrFail($id);
        
        return view('pages.admin.orders.show', compact('order'));
    }

    /**
     * Update status pesanan
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,pending_payment,paid,shipped,completed,cancelled'
        ]);

        try {
            $order = Order::findOrFail($id);

            // B2C: Jika status sebelumnya pending_payment dan disetujui (paid/shipped/completed),
            // jalankan confirmPayment() untuk memotong stok.
            if ($order->status === 'pending_payment' && in_array($request->status, ['paid', 'shipped', 'completed'])) {
                if (!$order->confirmPayment()) {
                    return redirect()->back()->withErrors(['msg' => 'Gagal mengonfirmasi pembayaran: Stok gudang pusat tidak mencukupi.']);
                }
            } else {
                $order->update(['status' => $request->status]);
            }

            // Kirim Notifikasi Update Status
            $statusLabels = [
                'pending' => 'menunggu pembayaran',
                'pending_payment' => 'menunggu pembayaran',
                'paid' => 'telah dikonfirmasi dan sedang diproses',
                'processing' => 'sedang diproses',
                'shipped' => 'sedang dalam pengiriman',
                'delivered' => 'telah sampai tujuan',
                'completed' => 'telah selesai',
                'cancelled' => 'dibatalkan'
            ];

            $label = $statusLabels[$request->status] ?? $request->status;
            
            $order->user->notify(new \App\Notifications\OrderStatusNotification($order, 'Status pesanan Anda telah diperbarui menjadi: ' . $label . '.'));

            return redirect()->back()->with('success', 'Status pesanan berhasil diubah menjadi: ' . ucfirst($request->status));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Gagal update status: ' . $e->getMessage()]);
        }
    }

    /**
     * Export orders ke CSV (opsional)
     */
    public function exportCSV()
    {
        $orders = Order::with(['user', 'items.product', 'items.variant'])->latest()->get();
        
        $csvFileName = 'orders_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return response()->streamDownload(function() use ($orders) {
            echo "Invoice Number,Customer Name,Email,Total Harga,Status,Tanggal Pesanan\n";
            
            foreach ($orders as $order) {
                echo implode(',', [
                    $order->invoice_number,
                    $order->nama_penerima ?? $order->user->name,
                    $order->email,
                    $order->total_harga,
                    $order->status,
                    $order->created_at->format('Y-m-d H:i:s')
                ]) . "\n";
            }
        }, $csvFileName);
    }
}
