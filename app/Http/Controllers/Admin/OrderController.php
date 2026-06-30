<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Enums\OrderStatus;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Menampilkan semua pesanan dari semua customer
     */
    public function index(Request $request)
    {
        $activeType = $request->input('type', 'b2c');

        // Hitung jumlah pesanan yang belum diproses (pending / pending_payment)
        $unprocessedB2CCount = Order::whereIn('status', [OrderStatus::PENDING, OrderStatus::PENDING_PAYMENT])
            ->where('metode_pembayaran', '!=', 'branch_request')
            ->count();
            
        $unprocessedB2BCount = Order::whereIn('status', [OrderStatus::PENDING, OrderStatus::PENDING_PAYMENT])
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
        $allowedStatuses = implode(',', array_column(OrderStatus::cases(), 'value'));
        $request->validate([
            'status' => 'required|in:' . $allowedStatuses
        ]);

        try {
            $order = Order::findOrFail($id);
            $newStatus = OrderStatus::from($request->status);

            // B2C: Jika status sebelumnya pending_payment dan disetujui (paid/shipped/completed),
            // jalankan confirmPayment() untuk memotong stok.
            if ($order->isPendingPayment() && in_array($newStatus, [OrderStatus::PAID, OrderStatus::SHIPPED, OrderStatus::COMPLETED])) {
                if (!$this->orderService->confirmPayment($order)) {
                    return redirect()->back()->withErrors(['msg' => 'Gagal mengonfirmasi pembayaran: Stok gudang pusat tidak mencukupi.']);
                }
            } else {
                $order->update(['status' => $newStatus->value]);
            }

            // Kirim Notifikasi Update Status dengan label Enum
            $label = strtolower($newStatus->label());
            $order->user->notify(new \App\Notifications\OrderStatusNotification($order, 'Status pesanan Anda telah diperbarui menjadi: ' . $label . '.'));

            return redirect()->back()->with('success', 'Status pesanan berhasil diubah menjadi: ' . $newStatus->label());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating status for Order ID ' . $id . ': ' . $e->getMessage(), [
                'exception' => $e,
                'status' => $request->status
            ]);
            return redirect()->back()->withErrors(['msg' => 'Gagal memperbarui status pesanan: Terjadi kesalahan sistem.']);
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

    /**
     * Memproses pesanan pengiriman ke kurir via Biteship API
     */
    public function shipWithBiteship(Request $request, $id)
    {
        try {
            $order = Order::with(['user', 'items.product', 'items.variant'])->findOrFail($id);

            if ($order->biteship_order_id) {
                return redirect()->back()->withErrors(['msg' => 'Pesanan ini sudah pernah dikirim ke Biteship sebelumnya.']);
            }

            if (empty($order->destination_area_id) || empty($order->kurir) || empty($order->layanan)) {
                return redirect()->back()->withErrors(['msg' => 'Informasi pengiriman tidak lengkap (Area ID/Kurir/Layanan kosong). Hubungi admin untuk melengkapi data order.']);
            }

            // Format items sesuai Biteship API
            $biteshipItems = [];
            foreach ($order->items as $item) {
                $biteshipItems[] = [
                    'name' => $item->product->nama_barang . ($item->variant ? ' (' . $item->variant->nama_varian . ')' : ''),
                    'value' => (int) $item->price,
                    'quantity' => (int) $item->quantity,
                    'weight' => 200 // default weight 200gr per item
                ];
            }

            $orderData = [
                'recipient_name' => $order->nama_penerima,
                'recipient_phone' => $order->no_telp,
                'recipient_email' => $order->email,
                'recipient_address' => $order->alamat_pengiriman,
                'recipient_area_id' => $order->destination_area_id,
                'courier_company' => $order->kurir,
                'courier_type' => $order->layanan,
                'items' => $biteshipItems
            ];

            $biteship = app(\App\Services\BiteshipService::class);
            $response = $biteship->createOrder($orderData);

            if ($response['success']) {
                $order->update([
                    'biteship_order_id' => $response['id'],
                    'resi' => $response['waybill_id'] ?? $response['courier']['waybill_id'] ?? 'MOCK-AWB-' . strtoupper(\Illuminate\Support\Str::random(10)),
                    'status' => OrderStatus::SHIPPED->value
                ]);

                // Kirim notifikasi ke pelanggan
                if ($order->user) {
                    $order->user->notify(new \App\Notifications\OrderStatusNotification($order, 'Pesanan Anda telah diserahkan ke kurir (' . strtoupper($order->kurir) . ') dengan nomor resi: ' . $order->resi . '.'));
                }

                $msg = 'Pesanan berhasil dikirim ke Biteship! ' . ($response['message'] ?? '');
                return redirect()->back()->with('success', $msg);
            }

            return redirect()->back()->withErrors(['msg' => 'Gagal membuat pesanan Biteship: ' . ($response['message'] ?? 'Unknown Error')]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error shipping with Biteship for Order ID ' . $id . ': ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->back()->withErrors(['msg' => 'Gagal memproses pengiriman: ' . $e->getMessage()]);
        }
    }
}
