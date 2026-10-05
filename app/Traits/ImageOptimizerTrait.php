<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

trait ImageOptimizerTrait
{
    /**
     * Skenario A: Optimasi dan Konversi File Gambar Upload Manual ke WebP
     */
    public function optimizeAndStoreUploadedImage(
        UploadedFile|string $file, 
        string $directory = 'products', 
        int $maxWidth = 1024, 
        int $quality = 88
    ): string {
        try {
            $source = ($file instanceof UploadedFile) ? $file->getRealPath() : $file;
            $image = Image::decode($source);

            $image->scaleDown(width: $maxWidth);

            $encodedWebp = $image->encodeUsingFileExtension('webp', quality: $quality);

            $filename = Str::random(40) . '.webp';
            $fullPath = trim($directory, '/') . '/' . $filename;

            Storage::disk('public')->put($fullPath, (string) $encodedWebp);

            return $fullPath;
        } catch (\Throwable $e) {
            Log::error("Gagal memproses upload gambar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Skenario B: Unduh Gambar dari URL Eksternal (API Olsera POS), Optimasi, dan Simpan sebagai HD WebP
     */
    public function optimizeAndStoreRemoteImage(
        string $imageUrl, 
        string $directory = 'products', 
        int $maxWidth = 1024, 
        int $quality = 88
    ): ?string {
        try {
            $response = Http::timeout(10)->get($imageUrl);
            
            if (!$response->successful() || empty($response->body())) {
                Log::warning("Gagal mengunduh gambar remote dari URL: {$imageUrl}");
                return null;
            }

            $image = Image::decode($response->body());

            $image->scaleDown(width: $maxWidth);

            $encodedWebp = $image->encodeUsingFileExtension('webp', quality: $quality);

            $filename = Str::random(40) . '.webp';
            $fullPath = trim($directory, '/') . '/' . $filename;

            Storage::disk('public')->put($fullPath, (string) $encodedWebp);

            return $fullPath;

        } catch (\Throwable $e) {
            Log::error("Error saat memproses gambar remote Olsera [{$imageUrl}]: " . $e->getMessage());
            return null;
        }
    }
}


