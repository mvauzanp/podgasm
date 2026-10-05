<?php

namespace App\Jobs;

use App\Services\OlseraApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOlseraDeltaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(OlseraApiService $olseraService): void
    {
        Log::info('SyncOlseraDeltaJob: Memulai background delta sync via Webhook trigger...');
        $result = $olseraService->syncCatalogAndStock(forceFullSync: false);
        Log::info('SyncOlseraDeltaJob selesai: ' . ($result['message'] ?? 'OK'));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SyncOlseraDeltaJob Failed: ' . $exception->getMessage());
    }
}
