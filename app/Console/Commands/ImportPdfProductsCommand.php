<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;

class ImportPdfProductsCommand extends Command
{
    protected $signature = 'import:pdf';
    protected $description = 'Import products, variants, and sales transactions from Penjualan berdasarkan SKU-2026-06-01__2026-06-30.pdf';

    public function handle()
    {
        $filePath = base_path('Penjualan berdasarkan SKU-2026-06-01__2026-06-30.pdf');
        if (!file_exists($filePath)) {
            $this->error('File PDF tidak ditemukan di root project.');
            return 1;
        }

        $this->info('Mulai membaca file PDF...');
        $parser = new \Smalot\PdfParser\Parser();
        try {
            $pdf = $parser->parseFile($filePath);
        } catch (\Exception $e) {
            $this->error('Gagal membaca PDF: ' . $e->getMessage());
            return 1;
        }

        $text = $pdf->getText();

        // Kategori mapping
        $categoryMapping = [
            'AKSESORIS' => 18,
            'CARTRIDGE' => 16,
            'LIQUID RELX' => 3, // Saltnic
            'MOD' => 7,
            'OCC' => 16,
            'POD CLOSE SYSTEM' => 27,
            'POD DEVICE' => 6,
            'POD OPEN SYSTEM' => 6,
            'SALTNIC LIQUID' => 3,
            'FREEBASE' => 2,
            'FREEBASE LIQUID' => 2,
            'DISPOSABLE POD' => 26, // Disposable (ID 26)
            'COIL OPEN SISTEM' => 16, // Coil & Cartridge (ID 16)
        ];

        // Split text by group name followed by quantity pattern to prevent false matches inside product names
        // Allowing optional letters before the group name to handle word concatenation in PDF (e.g. DAYLIQUID, MELODYLIQUID)
        $groupRegex = '/\b(?:[a-zA-Z]*)(AKSESORIS|CARTRIDGE|LIQUID\s+RELX|MOD|OCC|POD\s+CLOSE\s+(?:SYSTEM|SISTEM|SYESTEM)|POD\s+DEVICE|POD\s+OPEN\s+(?:SYSTEM|SISTEM|SYESTEM)|SALTNIC\s+LIQUID|FREE\s*BASE(?:\s+LIQUID)?|COIL\s+OPEN\s+(?:SYSTEM|SISTEM|SYESTEM)|DISPOSABLE\s+POD|TEREA\s+IQOS)\b(?:\s+|\t+)(\d+)(?:\s*IDR|\s*\t)/i';

        $segments = preg_split($groupRegex, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $this->info('Membersihkan data transaksi lama bulan Juni 2026...');
        OrderItem::whereBetween('created_at', ['2026-06-01 00:00:00', '2026-06-30 23:59:59'])->delete();
        Order::whereBetween('created_at', ['2026-06-01 00:00:00', '2026-06-30 23:59:59'])->delete();

        $this->info('Mulai mengimpor produk dan transaksi penjualan...');
        $importedProducts = 0;
        $importedVariants = 0;
        $importedOrders = 0;
        $nextProductName = '';

        $variantPatterns = [
            '/\b\d+MG\b/i',
            '/\b\d+[\.,]?\d*\s*(OHM|\(RDA\)|\(RDL\)|\(RDL\/MTL\))\b/i',
            '/\b(0\.6|0\.8|0\.19|0\.2|0\.4|0\.7)\b/',
            '/\b(GUNMETAL|BLACK CARBON|BLACK SHADOW|BROWN SHADOW|LIGHT BROWN SHADOW|METAL BLUE|METAL SILVER|PINK RIPPLE|LUMINOUS FORGED|SILK BROWN|TITANIUM BLUE|PEARLY WHITE|POLO STRIPE|VINTAGE DENIM|CLEAN WHITE|MIDNIGHT BLUE|TWINS RED|CHAMPAGNE GOLD|RAW BLACK|SAGE ESTATE|EMERALD GREEN|ONYX BLACK|SOFT PINK|COOL BLACK|KEYBOARD|TITANIUM SILVER|NEON GREEN|NEON PINK|PRESTIGE GREY|BLACK CHECKMATE|BLUE|BLACK|PINK|WHITE|GRAY|GREY|PURPLE|RED|ORANGE|GREEN|YELLOW|BROWN|GOLD|SILVER|GREEN LIGHT|ABSOLUT BLACK|HOT PINK|SPACE GREY|WILD PURPLE|BLACK GOLD|CHEQUERED GREY|CORAL ORANGE|TRACTION GREY|BORDEAUX RED|MINT GREEN|ULTRA CARBON|BROWN LEATHER|PURPLE SILK|RACE RED|SKY BLUE|TITANIUM SILK|GLOSSY GRAY|MISTY LEAF|AQUA \(MINT ICE\)|APEX SPIRIT)\b/i',
            '/\b(AVOCADO SMOOTHIE|BANANA FREEZE|BLUEBERRY POP|CLASSIC TOBACCO|GREEN GRAPE|HIBISCIUS ICE TEA|ICED BLACK TEA|ICED TEA|ICY GRAPE|ICY ION WATER|ICY ROSE|JASMINE MILK TEA|LIME ICE|LONGJING ICE TEA|LUDOU ICE|LYCHEE ICE|LYCHEE LEMONADE|MANGO BLACKCURRANT|MANGOSTEEN ICE|MELON HONEY|MENTHOL EXTRA|MINT GUM|OOLONG ICE TEA|PINEAPPLE DELIGHT|SMOOTH MANGO|SNOW STRAWBERRY|STRAWBERRY BURST|SUMMER LYCHEE|TANGY PURPLE|TARO SCOOP|WATERMELON ICE 5%|WATERMELON|BACCOBERRY|BERRY HANNA|LYCHEE|TEA|MATCHA|BLACKCURRANT LYCHEE TEA|PASSION FRUIT MANGO TEA|FRESH MANGO|ICY APPLE|ICY BERRY|ICY HONEYDEW|ICY MANGO|ICY WATERMELON|ICY BLACKCURRANT|ICY GRAPE|ICY LYCHEE|ICY PASSIONFRUIT|ICY STRAWBERRY|VICI GUAVA|VIDI SOURSOP|ORI MILK|PEACH MILK|TIRAMISU ICE CREAM|VANILLA ICE CREAM|GRAPE|MAGIC WATER|MILKY MANGO|MILKY MELON|MILKY PINEAPPLE|MILKY STRAWBERRY|KIWI PINEAPPLE|SOUR MILK MANGO|SOUR MILK ORI|SOUR MILK STRAWBERRY|APPLE|AVOCADO|BANANA|BLACKCURRANT|GUAVA|HONEYDEW|MANGO|ORANGE|PASSION FRUIT|PINEAPPLE|SOURSOP|STRAWBERRY|BLUE ICY CREAM|PINK ICY CREAM|WHITE ICY CHILL|15MG|35MG|30MG|BANANA KIWI|BLACKCURRANT LYCHEE|GRAPE APPLE|MANGO BASE|MANGO PEACH|STRAWBERRY WATERMELON|BOLD MANGO|MANGO MADNES|WATERMELON STRAWBERRY|YACOOL|MATCHA LYCHEE|MATCHA STRAWBERRY|BAILYES|THAI TEA|LYCHEE PINEAPPLE|RED APPLE|APPLE KIWI|GRAPE BERRY|GUAVA MANGO|PEACH BERRY|COOKIES N CREAM,3MG|COOKIES N CREAM,6MG|STRAWBERRY ICE CREAM,3MG|STRAWBERRY ICE CREAM,6MG|TARO ICE CREAM,3MG|TARO ICE CREAM,6MG|MANGO RASPBERRY|COOKIES ICE CREAM V7|HONEYDEW ICE CREAM V10|TARO ICE CREAM V6|SOUR PUNCH|STRAWBERRY SOURSOP|LEMON TEA|LYCHEE TEA|PEACH TEA|GALAPAGOS|MALDIVES|BARBADOS|SATORINI|FRAPPUCINO|SARSAPARILLA|GEMINI|BLACK CURRANT|MISTY LEAF|AQUA|APEX SPIRIT|AQUA \(MINT ICE\))\b/i'
        ];

        for ($i = 0; $i < count($segments); $i++) {
            $segment = trim($segments[$i]);
            if (empty($segment)) continue;

            $compareSegment = preg_replace('/\s+/', ' ', strtoupper($segment));

            if (in_array($compareSegment, ['AKSESORIS', 'CARTRIDGE', 'LIQUID RELX', 'MOD', 'OCC', 'POD CLOSE SYSTEM', 'POD CLOSE SISTEM', 'POD CLOSE SYESTEM', 'POD DEVICE', 'POD OPEN SYSTEM', 'POD OPEN SISTEM', 'POD OPEN SYESTEM', 'SALTNIC LIQUID', 'FREEBASE', 'FREEBASE LIQUID', 'FREE BASE', 'FREE BASE LIQUID', 'COIL OPEN SYSTEM', 'COIL OPEN SISTEM', 'COIL OPEN SYESTEM', 'DISPOSABLE POD', 'TEREA IQOS'])) {
                $groupName = $compareSegment;
                
                // Normalisasi nama grup
                if (str_contains($groupName, 'FREE') && str_contains($groupName, 'BASE')) $groupName = 'FREEBASE';
                if (str_contains($groupName, 'COIL OPEN')) $groupName = 'COIL OPEN SISTEM';
                if (str_contains($groupName, 'POD CLOSE')) $groupName = 'POD CLOSE SYSTEM';
                if (str_contains($groupName, 'POD OPEN')) $groupName = 'POD OPEN SYSTEM';

                // Skip TEREA IQOS sesuai permintaan user
                if ($groupName === 'TEREA IQOS') {
                    $i += 2; // skip Qty
                    continue;
                }

                $qty = intval($segments[$i + 1]);
                $i += 2; // skip group name dan qty
                if ($i >= count($segments)) break;
                $content = trim($segments[$i]);

                $tokens = preg_split('/\s+/', $content);
                
                $pricingTokens = [];
                $productNameTokens = [];
                $isPricing = true;

                foreach ($tokens as $token) {
                    $token = trim($token);
                    if (empty($token)) continue;

                    if ($isPricing) {
                        if (preg_match('/^-?[\d\.]+$/', $token) || $token === 'IDR' || $token === '-' || str_contains($token, 'IDR') || str_contains($token, 'Qty') || str_contains($token, 'Terjual') || str_contains($token, 'Sales') || str_contains($token, 'Order')) {
                            $pricingTokens[] = $token;
                        } else {
                            $isPricing = false;
                            $productNameTokens[] = $token;
                        }
                    } else {
                        $productNameTokens[] = $token;
                    }
                }

                // Rekonstruksi data harga
                $pricingText = implode(' ', $pricingTokens);
                preg_match_all('/-?\s*[\d\.]+/', $pricingText, $numMatches);
                $numbers = $numMatches[0] ?? [];

                $revenue = 0;
                $cost = 0;
                $salesOrderCount = 0;
                if (count($numbers) >= 2) {
                    $revenue = floatval(str_replace(['.', ' '], '', $numbers[0]));
                    $cost = floatval(str_replace(['.', ' '], '', $numbers[1]));
                }
                if (count($numbers) >= 4) {
                    $salesOrderCount = intval(str_replace(['.', ' '], '', $numbers[3]));
                }

                $unitPrice = $qty > 0 ? round($revenue / $qty, 2) : 0;
                $unitCost = $qty > 0 ? round($cost / $qty, 2) : 0;

                $currentProductName = trim($nextProductName);
                
                // Bersihkan fragment header
                $cleanProductName = preg_replace('/Produk\s+Alternative\s+Name\s+Varian\s+Grup\s+Sku\s*Barcode\s*Brand\s+Qty\s+Terjual\s+Currency\s+Total\s+Penjualan\s+Total\s+Harga\s+Modal\s+Laba\s*Total\s+Sales\s+Order\s+Rata-\s*rata\s+Qty\s+Terjual/is', '', $currentProductName);
                $cleanProductName = preg_replace('/Tanggal\s+Jual.*?\d{4}/is', '', $cleanProductName);
                
                // Bersihkan khusus produk pertama
                if (str_contains($cleanProductName, 'BATERAI VRK')) {
                    $cleanProductName = preg_replace('/^.*?(BATERAI VRK)/is', '$1', $cleanProductName);
                }
                
                $cleanProductName = str_replace('PODGASM KERTAJAYA', '', $cleanProductName);
                
                // bersihkan newline dan spasi ganda
                $cleanProductName = str_replace(["\n", "\r"], ' ', $cleanProductName);
                $cleanProductName = preg_replace('/\s+/', ' ', $cleanProductName);
                $cleanProductName = trim($cleanProductName);

                if (empty($cleanProductName)) {
                    $nextProductName = implode(' ', $productNameTokens);
                    continue;
                }

                // Terapkan penyesuaian harga sesuai instruksi user
                if (str_contains($cleanProductName, 'DRIPSTATE ORIGINAL BUTTER BREAD') && str_contains($cleanProductName, '6MG')) {
                    $unitPrice = 140000;
                    $unitCost = 110000;
                }
                if (str_contains($cleanProductName, 'PROVEN STRAWBERRY') && str_contains($cleanProductName, '3MG')) {
                    $unitPrice = 190000;
                    $unitCost = 140000;
                }
                if (str_contains($cleanProductName, 'TICKETS STRAWBERRY COOKIES')) {
                    $unitPrice = 125000;
                    $unitCost = 100500;
                }
                if (str_contains($cleanProductName, 'RELX SALTNIC MINGLE SERIES')) {
                    $unitPrice = 110000;
                    $unitCost = 85500;
                }
                if (str_contains($cleanProductName, 'WOW V4 ICE CREAM MATCHA PISTACHIO') && str_contains($cleanProductName, '35MG')) {
                    $unitPrice = 130000;
                    $unitCost = 98250;
                }
                if (str_contains($cleanProductName, 'BLACKWOOD MASTERPIECE CARAMEL PISTACHIO') && str_contains($cleanProductName, '40MG')) {
                    $unitCost = 110000;
                }
                if (str_contains($cleanProductName, 'BLACKWOOD MASTERPIECE VANILLA CUSTARD') && str_contains($cleanProductName, '40MG')) {
                    $unitCost = 110000;
                }
                if (str_contains($cleanProductName, 'ALA CARTE ICE CREAM')) {
                    $unitCost = 110000;
                }
                if (str_contains($cleanProductName, 'ALA CARTE BUTTER BREAK')) {
                    $unitCost = 100000;
                }

                // Split product name dan variant name
                $productName = $cleanProductName;
                $variantName = '';

                foreach ($variantPatterns as $pattern) {
                    if (preg_match($pattern, $cleanProductName, $varMatches, PREG_OFFSET_CAPTURE)) {
                        $varOffset = $varMatches[0][1];
                        $productName = trim(substr($cleanProductName, 0, $varOffset));
                        $variantName = trim(substr($cleanProductName, $varOffset));
                        break;
                    }
                }

                $productName = rtrim($productName, ' -|/\\,');
                $categoryId = $categoryMapping[$groupName] ?? 24;

                // Cari atau buat produk utama
                $product = Product::where('nama_barang', $productName)->first();

                if (!$product) {
                    $product = Product::create([
                        'category_id' => $categoryId,
                        'kode_barang' => $this->generateProductCode($categoryId, $productName),
                        'nama_barang' => $productName,
                        'slug' => Str::slug($productName),
                        'description' => 'Produk baru terimpor dari laporan penjualan Juni 2026.',
                        'harga_jual' => $variantName ? 0 : ($unitPrice > 0 ? $unitPrice : 100000),
                        'harga_pokok' => $variantName ? 0 : ($unitCost > 0 ? $unitCost : 80000),
                        'stok_aktual' => $variantName ? 0 : $qty,
                        'nilai_ss' => 0,
                        'lead_time' => 4,
                        'rata_penjualan' => 0
                    ]);
                    $importedProducts++;
                }

                // Jika kode_barang utama kosong, isi terlebih dahulu
                if (empty($product->kode_barang)) {
                    $product->update([
                        'kode_barang' => $this->generateProductCode($categoryId, $productName)
                    ]);
                }

                // Simpan varian jika terdeteksi
                $targetVariantId = null;
                if (!empty($variantName)) {
                    $existingVariant = $product->variants()->where('nama_varian', $variantName)->first();
                    if (!$existingVariant) {
                        $baseVariantCode = $product->kode_barang . '-' . strtoupper(substr(md5($variantName), 0, 4));
                        $variantCode = $baseVariantCode;
                        
                        $counter = 1;
                        while (ProductVariant::where('kode_barang', $variantCode)->exists()) {
                            $variantCode = $baseVariantCode . $counter;
                            $counter++;
                        }
                        
                        $existingVariant = $product->variants()->create([
                            'nama_varian' => $variantName,
                            'kode_barang' => $variantCode,
                            'harga_jual' => $unitPrice > 0 ? $unitPrice : 100000,
                            'harga_pokok' => $unitCost > 0 ? $unitCost : 80000,
                            'stok_aktual' => $qty,
                            'nilai_ss' => 0,
                            'lead_time' => 4,
                            'rata_penjualan' => 0
                        ]);
                        
                        $product->increment('stok_aktual', $qty);
                        $importedVariants++;
                    }
                    $targetVariantId = $existingVariant->id;
                }

                // Input Transaksi Penjualan ke orders & order_items
                if ($qty > 0 && $salesOrderCount > 0) {
                    // Distribusikan qty ke salesOrderCount
                    $qtys = array_fill(0, $salesOrderCount, 1);
                    $remaining = $qty - $salesOrderCount;
                    for ($j = 0; $j < $remaining; $j++) {
                        $idx = rand(0, $salesOrderCount - 1);
                        $qtys[$idx]++;
                    }
                    
                    // Buat order untuk setiap kuantitas terdistribusi
                    foreach ($qtys as $itemQty) {
                        $randomDay = rand(1, 30);
                        $randomDayStr = str_pad($randomDay, 2, '0', STR_PAD_LEFT);
                        $createdAt = "2026-06-{$randomDayStr} " . str_pad(rand(8, 20), 2, '0', STR_PAD_LEFT) . ":" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ":" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
                        
                        $invoice = 'INV/202606' . $randomDayStr . '/' . rand(100000, 999999);
                        
                        $order = Order::create([
                            'user_id' => 1,
                            'nama_penerima' => 'Customer Demo ' . rand(1, 100),
                            'email' => 'customer' . rand(1, 100) . '@example.com',
                            'no_telp' => '08' . rand(100000000, 999999999),
                            'invoice_number' => $invoice,
                            'total_harga' => $unitPrice * $itemQty,
                            'metode_pembayaran' => 'cash',
                            'status' => 'completed',
                            'alamat_pengiriman' => 'Alamat Demo Customer',
                            'ongkir' => 0,
                            'kurir' => 'manual',
                            'layanan' => 'reguler',
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt
                        ]);
                        
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $targetVariantId,
                            'quantity' => $itemQty,
                            'price' => $unitPrice,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt
                        ]);
                        
                        $importedOrders++;
                    }
                }

                $nextProductName = implode(' ', $productNameTokens);
            } else {
                $nextProductName = $segment;
            }
        }

        $this->info("Import Selesai! Berhasil mengunggah {$importedProducts} produk, {$importedVariants} varian, dan {$importedOrders} transaksi penjualan baru.");
        return 0;
    }

    private function generateProductCode($categoryId, $name)
    {
        $prefix = 'PROD';
        switch ($categoryId) {
            case 1:
            case 2:
            case 3:
                $prefix = 'LIQ'; break;
            case 16:
                $prefix = 'CRT'; break;
            case 4:
            case 6:
            case 27:
                $prefix = 'POD'; break;
            case 7:
                $prefix = 'MOD'; break;
            case 18:
                $prefix = 'ACC'; break;
            case 26:
                $prefix = 'DSP'; break;
        }

        $rand = strtoupper(substr(md5($name), 0, 4));
        return $prefix . '-' . $rand . '-' . rand(100, 999);
    }
}
