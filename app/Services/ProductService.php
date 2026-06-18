<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductService
{
    /**
     * Menyimpan produk baru beserta gambarnya dan variannya.
     */
    public function storeProduct(array $data, ?array $uploadedImages = null, ?array $variants = null, bool $hasVariants = false): Product
    {
        return DB::transaction(function () use ($data, $uploadedImages, $variants, $hasVariants) {
            // Generate Slug otomatis
            $data['slug'] = Str::slug($data['nama_barang']);

            // Set default values jika kosong
            $data['is_promo'] = isset($data['is_promo']) ? (bool)$data['is_promo'] : false;
            $data['diskon_persen'] = $data['diskon_persen'] ?? 0;
            $data['lead_time'] = $data['lead_time'] ?? 0;
            $data['rata_penjualan'] = $data['rata_penjualan'] ?? 0;
            $data['nilai_ss'] = $data['nilai_ss'] ?? 0;
            $data['gambar'] = null;

            // Hitung total stok aktual
            if ($hasVariants && !empty($variants)) {
                $data['stok_aktual'] = collect($variants)->sum('stok_aktual');
            } else {
                $data['stok_aktual'] = $data['stok_aktual'] ?? 0;
            }

            // Simpan produk induk
            $product = Product::create($data);

            // Simpan gambar-gambar produk
            if (!empty($uploadedImages)) {
                $primaryPath = null;
                foreach ($uploadedImages as $index => $file) {
                    $path = $file->store('products', 'public');
                    if ($index === 0) {
                        $primaryPath = $path;
                    }
                    $product->images()->create(['path' => $path]);
                }
                
                if ($primaryPath) {
                    $product->update(['gambar' => $primaryPath]);
                }
            }

            // Simpan varian jika diaktifkan
            if ($hasVariants && !empty($variants)) {
                foreach ($variants as $index => $variantData) {
                    $variantPath = null;
                    if (isset($variantData['gambar']) && $variantData['gambar'] instanceof \Illuminate\Http\UploadedFile) {
                        $variantPath = $variantData['gambar']->store('variants', 'public');
                    }

                    $product->variants()->create([
                        'nama_varian' => $variantData['nama_varian'],
                        'kode_barang' => $variantData['kode_barang'] ?? null,
                        'harga_jual' => $variantData['harga_jual'] ?? null,
                        'harga_pokok' => $variantData['harga_pokok'] ?? null,
                        'stok_aktual' => $variantData['stok_aktual'],
                        'tgl_expired' => $variantData['tgl_expired'] ?? null,
                        'tgl_cukai' => $variantData['tgl_cukai'] ?? null,
                        'gambar' => $variantPath,
                    ]);
                }
            }

            return $product;
        });
    }

    /**
     * Memperbarui data produk beserta varian dan gambarnya.
     */
    public function updateProduct(Product $product, array $data, ?array $uploadedImages = null, ?array $deletedImages = null, ?array $variants = null, bool $hasVariants = false): Product
    {
        return DB::transaction(function () use ($product, $data, $uploadedImages, $deletedImages, $variants, $hasVariants) {
            $data['slug'] = Str::slug($data['nama_barang']);
            $data['is_promo'] = isset($data['is_promo']) ? (bool)$data['is_promo'] : false;
            $data['diskon_persen'] = $data['diskon_persen'] ?? 0;
            
            $data['lead_time'] = $data['lead_time'] ?? $product->lead_time;
            $data['rata_penjualan'] = $data['rata_penjualan'] ?? $product->rata_penjualan;
            $data['nilai_ss'] = $data['nilai_ss'] ?? $product->nilai_ss;

            // 1. Hapus gambar yang ditandai untuk dihapus
            if (!empty($deletedImages)) {
                $imagesToDelete = $product->images()->whereIn('id', $deletedImages)->get();
                foreach ($imagesToDelete as $oldImage) {
                    Storage::disk('public')->delete($oldImage->path);
                    $oldImage->delete();
                }
            }

            // 2. Unggah gambar baru jika ada
            if (!empty($uploadedImages)) {
                foreach ($uploadedImages as $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create(['path' => $path]);
                }
            }

            // 3. Sinkronisasi gambar utama (product.gambar)
            $firstImage = $product->images()->first();
            if ($firstImage) {
                $data['gambar'] = $firstImage->path;
            } else {
                if ($product->gambar) {
                    Storage::disk('public')->delete($product->gambar);
                }
                $data['gambar'] = null;
            }

            // Hitung stok aktual induk dari total stok varian
            if ($hasVariants && !empty($variants)) {
                $data['stok_aktual'] = collect($variants)->sum('stok_aktual');
            } else {
                $data['stok_aktual'] = $data['stok_aktual'] ?? 0;
            }

            $product->update($data);

            if ($hasVariants && !empty($variants)) {
                // Update atau buat varian baru
                $keepIds = [];
                foreach ($variants as $index => $variantData) {
                    if (isset($variantData['id']) && $variantData['id']) {
                        $variant = $product->variants()->findOrFail($variantData['id']);
                        
                        $variantPath = $variant->gambar;
                        if (isset($variantData['gambar']) && $variantData['gambar'] instanceof \Illuminate\Http\UploadedFile) {
                            if ($variant->gambar) {
                                Storage::disk('public')->delete($variant->gambar);
                            }
                            $variantPath = $variantData['gambar']->store('variants', 'public');
                        }

                        $variant->update([
                            'nama_varian' => $variantData['nama_varian'],
                            'kode_barang' => $variantData['kode_barang'] ?? null,
                            'harga_jual' => $variantData['harga_jual'] ?? null,
                            'harga_pokok' => $variantData['harga_pokok'] ?? null,
                            'stok_aktual' => $variantData['stok_aktual'],
                            'tgl_expired' => $variantData['tgl_expired'] ?? null,
                            'tgl_cukai' => $variantData['tgl_cukai'] ?? null,
                            'gambar' => $variantPath,
                        ]);
                        $keepIds[] = $variant->id;
                    } else {
                        $variantPath = null;
                        if (isset($variantData['gambar']) && $variantData['gambar'] instanceof \Illuminate\Http\UploadedFile) {
                            $variantPath = $variantData['gambar']->store('variants', 'public');
                        }

                        $newVariant = $product->variants()->create([
                            'nama_varian' => $variantData['nama_varian'],
                            'kode_barang' => $variantData['kode_barang'] ?? null,
                            'harga_jual' => $variantData['harga_jual'] ?? null,
                            'harga_pokok' => $variantData['harga_pokok'] ?? null,
                            'stok_aktual' => $variantData['stok_aktual'],
                            'tgl_expired' => $variantData['tgl_expired'] ?? null,
                            'tgl_cukai' => $variantData['tgl_cukai'] ?? null,
                            'gambar' => $variantPath,
                        ]);
                        $keepIds[] = $newVariant->id;
                    }
                }

                // Hapus varian lama yang tidak disimpan kembali & hapus gambarnya
                $oldVariants = $product->variants()->whereNotIn('id', $keepIds)->get();
                foreach ($oldVariants as $oldVariant) {
                    if ($oldVariant->gambar) {
                        Storage::disk('public')->delete($oldVariant->gambar);
                    }
                    $oldVariant->delete();
                }
            } else {
                // Hapus semua varian jika dimatikan & hapus gambarnya
                $oldVariants = $product->variants()->get();
                foreach ($oldVariants as $oldVariant) {
                    if ($oldVariant->gambar) {
                        Storage::disk('public')->delete($oldVariant->gambar);
                    }
                    $oldVariant->delete();
                }
            }

            return $product;
        });
    }

    /**
     * Memproses import produk massal dari file Excel.
     */
    public function importProductsFromExcel(string $filePath): int
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        
        if (count($rows) <= 1) {
            throw new \Exception('File Excel kosong atau tidak memiliki data produk.');
        }
        
        $categories = Category::all()->pluck('id', 'nama_kategori')->mapWithKeys(function($id, $name) {
            return [strtolower(trim($name)) => $id];
        })->toArray();
        
        $productsToCreate = [];
        $errors = [];
        $rowNumber = 1;
        
        $existingProductNames = Product::pluck('nama_barang')->map(fn($n) => strtolower(trim($n)))->toArray();
        $existingProductCodes = Product::whereNotNull('kode_barang')->pluck('kode_barang')->map(fn($c) => strtolower(trim($c)))->toArray();
        $existingVariantCodes = ProductVariant::whereNotNull('kode_barang')->pluck('kode_barang')->map(fn($c) => strtolower(trim($c)))->toArray();
        
        $sheetCodes = [];
        
        foreach ($rows as $index => $col) {
            $rowNumber = $index;
            if ($rowNumber === 1) {
                continue; // skip header
            }
            
            $kodeBarang = trim($col['A'] ?? '');
            $namaBarang = trim($col['B'] ?? '');
            $kategoriName = trim($col['C'] ?? '');
            $deskripsi = trim($col['D'] ?? '');
            $hargaJual = trim($col['E'] ?? '');
            $hargaPokok = trim($col['F'] ?? '');
            $stokAktual = trim($col['G'] ?? '');
            $namaVarian = trim($col['H'] ?? '');
            $tglExpired = trim($col['I'] ?? '');
            $tglCukai = trim($col['J'] ?? '');
            
            if (empty($namaBarang)) {
                $allEmpty = true;
                foreach ($col as $cVal) {
                    if (!empty(trim($cVal ?? ''))) {
                        $allEmpty = false;
                        break;
                    }
                }
                if ($allEmpty) {
                    continue;
                }
                $errors[] = "Baris {$rowNumber}: Nama Barang wajib diisi.";
                continue;
            }
            
            $kategoriLower = strtolower($kategoriName);
            if (empty($kategoriName)) {
                $errors[] = "Baris {$rowNumber}: Kategori untuk produk '{$namaBarang}' tidak boleh kosong.";
                continue;
            } elseif (!isset($categories[$kategoriLower])) {
                $errors[] = "Baris {$rowNumber}: Kategori '{$kategoriName}' tidak terdaftar di sistem. Silakan sesuaikan dengan lembar 'Daftar Kategori'.";
                continue;
            }
            $categoryId = $categories[$kategoriLower];
            
            if (!is_numeric($hargaJual) || $hargaJual < 0) {
                $errors[] = "Baris {$rowNumber}: Harga Jual harus berupa angka positif.";
            }
            if (!is_numeric($hargaPokok) || $hargaPokok < 0) {
                $errors[] = "Baris {$rowNumber}: Harga Pokok harus berupa angka positif.";
            }
            if (!is_numeric($stokAktual) || $stokAktual < 0) {
                $errors[] = "Baris {$rowNumber}: Stok Aktual harus berupa angka positif.";
            }
            
            if (!empty($tglExpired)) {
                $d = \DateTime::createFromFormat('Y-m-d', $tglExpired);
                if (!$d || $d->format('Y-m-d') !== $tglExpired) {
                    $errors[] = "Baris {$rowNumber}: Format Tanggal Expired harus YYYY-MM-DD.";
                }
            }
            if (!empty($tglCukai)) {
                $d = \DateTime::createFromFormat('Y-m-d', $tglCukai);
                if (!$d || $d->format('Y-m-d') !== $tglCukai) {
                    $errors[] = "Baris {$rowNumber}: Format Tanggal Cukai harus YYYY-MM-DD.";
                }
            }
            
            if (!empty($kodeBarang)) {
                $kodeLower = strtolower($kodeBarang);
                if (in_array($kodeLower, $existingProductCodes) || in_array($kodeLower, $existingVariantCodes)) {
                    $errors[] = "Baris {$rowNumber}: Kode Barang '{$kodeBarang}' sudah digunakan di sistem.";
                }
                if (in_array($kodeLower, $sheetCodes)) {
                    $errors[] = "Baris {$rowNumber}: Kode Barang '{$kodeBarang}' ganda dalam file Excel ini.";
                }
                $sheetCodes[] = $kodeLower;
            }
            
            if (!empty($errors)) {
                continue;
            }
            
            $productKey = strtolower($namaBarang);
            
            if (!isset($productsToCreate[$productKey])) {
                if (in_array($productKey, $existingProductNames)) {
                    $errors[] = "Baris {$rowNumber}: Produk dengan nama '{$namaBarang}' sudah ada di database.";
                    continue;
                }
                
                $productsToCreate[$productKey] = [
                    'nama_barang' => $namaBarang,
                    'category_id' => $categoryId,
                    'kode_barang' => empty($namaVarian) ? $kodeBarang : null,
                    'description' => $deskripsi,
                    'harga_jual' => floatval($hargaJual),
                    'harga_pokok' => floatval($hargaPokok),
                    'stok_aktual' => 0,
                    'lead_time' => 0,
                    'rata_penjualan' => 0,
                    'nilai_ss' => 0,
                    'tgl_expired' => empty($tglExpired) ? null : $tglExpired,
                    'tgl_cukai' => empty($tglCukai) ? null : $tglCukai,
                    'has_variants' => false,
                    'variants' => []
                ];
            }
            
            if (!empty($namaVarian)) {
                $productsToCreate[$productKey]['has_variants'] = true;
                $productsToCreate[$productKey]['variants'][] = [
                    'nama_varian' => $namaVarian,
                    'kode_barang' => $kodeBarang,
                    'harga_jual' => floatval($hargaJual),
                    'harga_pokok' => floatval($hargaPokok),
                    'stok_aktual' => intval($stokAktual),
                    'nilai_ss' => 0,
                    'tgl_expired' => empty($tglExpired) ? null : $tglExpired,
                    'tgl_cukai' => empty($tglCukai) ? null : $tglCukai,
                ];
            } else {
                $productsToCreate[$productKey]['stok_aktual'] = intval($stokAktual);
            }
        }
        
        if (!empty($errors)) {
            throw new \Exception(implode("\n", $errors));
        }
        
        if (empty($productsToCreate)) {
            throw new \Exception('Tidak ada data produk valid yang bisa diimport.');
        }
        
        DB::transaction(function () use ($productsToCreate) {
            foreach ($productsToCreate as $pData) {
                $variants = $pData['variants'];
                unset($pData['variants']);
                
                $pData['slug'] = Str::slug($pData['nama_barang']);
                
                if ($pData['has_variants']) {
                    $pData['stok_aktual'] = collect($variants)->sum('stok_aktual');
                }
                
                $product = Product::create($pData);
                
                if ($pData['has_variants']) {
                    foreach ($variants as $vData) {
                        $product->variants()->create($vData);
                    }
                }
            }
        });
        
        return count($productsToCreate);
    }
}
