<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class OlseraSalesImportService
{
    /**
     * Memproses import file laporan penjualan Olsera Backoffice (Bisa 1 file atau banyak file sekaligus)
     * Mendukung pembagian per bulan (karena batas export Olsera 31 hari per file)
     *
     * @param UploadedFile|UploadedFile[] $files
     */
    public function import(array|UploadedFile $files): array
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        if (empty($files)) {
            throw new \Exception('Tidak ada file yang diunggah.');
        }

        $allVariants = ProductVariant::with('product')->get();
        $allProducts = Product::with('variants')->get();

        $variantsBySku = [];
        foreach ($allVariants as $v) {
            if (!empty($v->kode_barang)) {
                $variantsBySku[strtolower(trim($v->kode_barang))] = $v;
            }
        }

        $productsBySku = [];
        foreach ($allProducts as $p) {
            if (!empty($p->kode_barang)) {
                $productsBySku[strtolower(trim($p->kode_barang))] = $p;
            }
        }

        $productsByName = [];
        $productsByNormalizedName = [];
        foreach ($allProducts as $p) {
            $nameClean = strtolower(trim($p->nama_barang));
            $productsByName[$nameClean] = $p;
            
            $normKey = preg_replace('/[^a-z0-9]/', '', $nameClean);
            if (!empty($normKey)) {
                $productsByNormalizedName[$normKey] = $p;
            }
        }

        $totalFiles = count($files);
        $totalProcessedRows = 0;
        $totalMatchedEntries = 0;
        $grandTotalQty = 0;
        $daysWindow = 90;

        DB::beginTransaction();
        try {
            // Jika ini proses import baru, kita bersihkan sales_histories lama dari sumber olsera_pos
            SalesHistory::where('source', 'olsera_pos')->delete();

            foreach ($files as $fileIndex => $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                $filePath = $file->getRealPath();
                try {
                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray(null, true, true, true);
                } catch (\Exception $e) {
                    Log::error("Error loading Olsera file #{$fileIndex}: " . $e->getMessage());
                    continue;
                }

                if (empty($rows) || count($rows) < 2) {
                    continue;
                }

                // Deteksi Baris Header
                $headerRowIndex = 1;
                $headers = [];
                foreach ($rows as $rowIndex => $row) {
                    $cleaned = array_map(function($v) {
                        return strtolower(trim(str_replace(['_', '-'], ' ', (string)$v)));
                    }, array_filter($row));

                    $keywords = ['product', 'produk', 'variant', 'varian', 'sku', 'barcode', 'sold qty', 'qty terjual', 'total sales amount', 'total penjualan', 'profit', 'laba', 'qty', 'jumlah'];
                    if (count(array_intersect($cleaned, $keywords)) >= 2) {
                        $headerRowIndex = $rowIndex;
                        $headers = $row;
                        break;
                    }
                }

                if (empty($headers)) {
                    $headers = $rows[1] ?? [];
                }

                $colMap = [
                    'date' => null,
                    'sku' => null,
                    'barcode' => null,
                    'product_name' => null,
                    'variant_name' => null,
                    'qty' => null,
                ];

                foreach ($headers as $colKey => $colHeader) {
                    $rawHeader = strtolower(trim((string)$colHeader));
                    $normHeader = strtolower(trim(str_replace(['_', '-'], ' ', (string)$colHeader)));
                    
                    if (in_array($normHeader, ['tanggal', 'date', 'tgl', 'transaction date', 'waktu', 'tanggal transaksi', 'order date'])) {
                        $colMap['date'] = $colKey;
                    } elseif (in_array($normHeader, ['sku', 'kode barang', 'kode produk', 'item code', 'variant sku', 'kode'])) {
                        $colMap['sku'] = $colKey;
                    } elseif (in_array($normHeader, ['barcode'])) {
                        $colMap['barcode'] = $colKey;
                    } elseif (in_array($normHeader, ['product', 'produk', 'nama produk', 'nama barang', 'product name', 'item name', 'name', 'nama', 'item'])) {
                        $colMap['product_name'] = $colKey;
                    } elseif (in_array($normHeader, ['variant', 'varian', 'nama varian', 'variant name', 'nama variasi'])) {
                        $colMap['variant_name'] = $colKey;
                    } elseif (in_array($rawHeader, ['sold_qty', 'qty_sold', 'sold_quantity']) || in_array($normHeader, ['sold qty', 'qty terjual', 'qty', 'jumlah', 'quantity', 'kuantiti', 'terjual', 'qty sold', 'total qty', 'total terjual', 'total sales order'])) {
                        $colMap['qty'] = $colKey;
                    }
                }

                // Tentukan rentang hari untuk file ini jika multi-file (misal file 0 = hari 0-30, file 1 = hari 30-60, file 2 = hari 60-90)
                $chunkDays = (int) ceil($daysWindow / max(1, $totalFiles));
                $startOffset = $fileIndex * $chunkDays;
                $endOffset = min($daysWindow, ($fileIndex + 1) * $chunkDays);
                $fileDaysCount = max(1, $endOffset - $startOffset);

                foreach ($rows as $rowIndex => $row) {
                    if ($rowIndex <= $headerRowIndex) {
                        continue;
                    }

                    $skuRaw = $colMap['sku'] ? trim((string)($row[$colMap['sku']] ?? '')) : '';
                    $nameRaw = $colMap['product_name'] ? trim((string)($row[$colMap['product_name']] ?? '')) : '';
                    $variantRaw = $colMap['variant_name'] ? trim((string)($row[$colMap['variant_name']] ?? '')) : '';
                    $rawQty = $colMap['qty'] ? (string)($row[$colMap['qty']] ?? '0') : '0';
                    $rawDate = $colMap['date'] ? trim((string)($row[$colMap['date']] ?? '')) : '';

                    $skuVal = ($skuRaw === '-' || $skuRaw === '') ? '' : $skuRaw;
                    $variantVal = ($variantRaw === '-' || $variantRaw === '') ? '' : $variantRaw;
                    $nameVal = ($nameRaw === '-' || $nameRaw === '') ? '' : $nameRaw;

                    $cleanQtyStr = str_replace(',', '', explode('.', $rawQty)[0] ?? '0');
                    $qtyVal = (int) preg_replace('/[^0-9]/', '', $cleanQtyStr);

                    if ($qtyVal <= 0 && empty($skuVal) && empty($nameVal)) {
                        continue;
                    }

                    $productId = null;
                    $variantId = null;

                    // 1. Cocokkan SKU
                    if (!empty($skuVal)) {
                        $skuKey = strtolower($skuVal);
                        if (isset($variantsBySku[$skuKey])) {
                            $v = $variantsBySku[$skuKey];
                            $variantId = $v->id;
                            $productId = $v->product_id;
                        } elseif (isset($productsBySku[$skuKey])) {
                            $p = $productsBySku[$skuKey];
                            $productId = $p->id;
                        }
                    }

                    // 2. Cocokkan Nama Produk & Varian
                    if (!$productId && !empty($nameVal)) {
                        $nameKey = strtolower($nameVal);
                        $normKey = preg_replace('/[^a-z0-9]/', '', $nameKey);

                        $matchedProduct = $productsByName[$nameKey] ?? ($productsByNormalizedName[$normKey] ?? null);

                        if ($matchedProduct) {
                            $productId = $matchedProduct->id;

                            if (!empty($variantVal)) {
                                $vKey = strtolower($variantVal);
                                $vNormKey = preg_replace('/[^a-z0-9]/', '', $vKey);

                                $matchedV = $matchedProduct->variants->first(function($v) use ($vKey, $vNormKey) {
                                    $targetName = strtolower(trim($v->nama_varian));
                                    return ($targetName === $vKey) || (preg_replace('/[^a-z0-9]/', '', $targetName) === $vNormKey);
                                });

                                if ($matchedV) {
                                    $variantId = $matchedV->id;
                                }
                            } elseif ($matchedProduct->variants->count() === 1) {
                                $variantId = $matchedProduct->variants->first()->id;
                            }
                        }
                    }

                    if ($productId && $qtyVal > 0) {
                        if (!empty($rawDate)) {
                            try {
                                $dateVal = is_numeric($rawDate) 
                                    ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate))->toDateString()
                                    : Carbon::parse($rawDate)->toDateString();
                            } catch (\Exception $e) {
                                $dateVal = now()->toDateString();
                            }

                            $history = SalesHistory::firstOrCreate(
                                [
                                    'product_id' => $productId,
                                    'product_variant_id' => $variantId,
                                    'date' => $dateVal,
                                    'source' => 'olsera_pos'
                                ],
                                ['qty_sold' => 0]
                            );
                            $history->increment('qty_sold', $qtyVal);
                        } else {
                            // Sebarkan kuantiti ke segmen hari file ini
                            $baseDaily = intdiv($qtyVal, $fileDaysCount);
                            $remainder = $qtyVal % $fileDaysCount;

                            for ($d = ($endOffset - 1); $d >= $startOffset; $d--) {
                                $targetDate = now()->subDays($d)->toDateString();
                                $localIndex = $d - $startOffset;
                                $dayQty = $baseDaily + ($localIndex < $remainder ? 1 : 0);

                                if ($dayQty > 0) {
                                    $history = SalesHistory::firstOrCreate(
                                        [
                                            'product_id' => $productId,
                                            'product_variant_id' => $variantId,
                                            'date' => $targetDate,
                                            'source' => 'olsera_pos'
                                        ],
                                        ['qty_sold' => 0]
                                    );
                                    $history->increment('qty_sold', $dayQty);
                                }
                            }
                        }

                        $totalMatchedEntries++;
                        $grandTotalQty += $qtyVal;
                    }

                    $totalProcessedRows++;
                }
            }

            DB::commit();

            // Jalankan rekalkulasi Safety Stock otomatis
            Artisan::call('safety-stock:recalculate');

            $fileText = $totalFiles > 1 ? "dari {$totalFiles} file laporan" : "dari 1 file laporan";
            return [
                'success' => true,
                'total_files' => $totalFiles,
                'total_rows_processed' => $totalProcessedRows,
                'matched_entries' => $totalMatchedEntries,
                'total_qty_imported' => $grandTotalQty,
                'message' => "Berhasil mengimpor {$grandTotalQty} unit data penjualan historis ({$totalMatchedEntries} item cocok) {$fileText} Olsera Backoffice!"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing Olsera sales report: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw new \Exception('Terjadi kesalahan saat memproses data laporan: ' . $e->getMessage());
        }
    }
}
