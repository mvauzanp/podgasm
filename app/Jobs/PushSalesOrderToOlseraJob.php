<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OlseraApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushSalesOrderToOlseraJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60]; // Retry interval 10s, 30s, 60s

    protected int $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(OlseraApiService $olseraService): void
    {
        $order = Order::with(['items.product', 'items.variant'])->find($this->orderId);

        if (!$order) {
            Log::warning("PushSalesOrderToOlseraJob: Order ID {$this->orderId} not found.");
            return;
        }

        try {
            $result = $olseraService->pushSalesOrder($order);
            Log::info("PushSalesOrderToOlseraJob: Order {$order->invoice_number} synced to Olsera.", [
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error("PushSalesOrderToOlseraJob Failed for Order {$order->invoice_number}: " . $e->getMessage());
            throw $e; // Throw exception to trigger queue retry backoff
        }
    }
}
