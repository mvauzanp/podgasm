<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-expired {--hours=24 : Batas kedaluwarsa pesanan pending dalam jam}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis membatalkan pesanan pending payment yang kedaluwarsa dan merestorasi kuota voucher';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService): int
    {
        $hours = (int) $this->option('hours');
        if ($hours <= 0) {
            $hours = 24;
        }

        $cutoffTime = now()->subHours($hours);

        $expiredOrders = Order::where('status', OrderStatus::PENDING_PAYMENT->value)
            ->where('created_at', '<=', $cutoffTime)
            ->get();

        $count = $expiredOrders->count();

        if ($count === 0) {
            $this->info("Tidak ada pesanan pending payment yang kedaluwarsa (lebih dari {$hours} jam).");
            return Command::SUCCESS;
        }

        $this->info("Menemukan {$count} pesanan kedaluwarsa. Memulai pembatalan...");

        $cancelledCount = 0;
        foreach ($expiredOrders as $order) {
            try {
                $cancelled = $orderService->cancelOrder($order);
                if ($cancelled) {
                    $cancelledCount++;
                    $this->line("  ✓ Pesanan #{$order->invoice_number} berhasil dibatalkan.");
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Gagal membatalkan pesanan #{$order->invoice_number}: {$e->getMessage()}");
                Log::error("Failed auto-cancelling expired order {$order->invoice_number}: " . $e->getMessage());
            }
        }

        $this->info("✅ Selesai: {$cancelledCount}/{$count} pesanan kedaluwarsa berhasil dibatalkan.");
        return Command::SUCCESS;
    }
}
