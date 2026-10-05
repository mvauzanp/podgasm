<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OlseraApiService;

class SyncOlseraStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olsera:sync {--outlet= : ID Outlet/Cabang Olsera tertentu} {--full : Paksa Full Sync seluruh katalog}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Smart Polling & Sinkronisasi otomatis stok katalog Olsera POS (Delta & Full)';

    /**
     * Execute the console command.
     */
    public function handle(OlseraApiService $olseraService): int
    {
        $outletId = $this->option('outlet');
        $forceFull = (bool) $this->option('full');

        $this->info($forceFull 
            ? 'Memulai Full Sync seluruh katalog Olsera POS...' 
            : 'Memulai Smart Polling (Delta Sync) perubahan stok Olsera POS...');

        $result = $olseraService->syncCatalogAndStock($outletId, $forceFull);

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            $this->table(['Metrik', 'Nilai'], [
                ['Mode Eksekusi', $result['mode'] === 'live' ? 'Live Olsera API' : 'Simulasi (Mock Mode)'],
                ['Tipe Polling', ($result['sync_type'] ?? 'full') === 'delta' ? '⚡ Smart Polling (Delta Incremental)' : '📦 Full Sync Katalog'],
                ['Timestamp Acuan', $result['last_timestamp'] ?? 'Awal (Full Sync)'],
                ['Produk Baru', $result['synced_products'] ?? 0],
                ['Stok Diperbarui', $result['updated_stocks'] ?? 0],
            ]);
            return Command::SUCCESS;
        }

        $this->error('❌ ' . $result['message']);
        return Command::FAILURE;
    }
}
