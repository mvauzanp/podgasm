# 📘 MASTER WALKTHROUGH & DOKUMENTASI LENGKAP PROYEK PODGASM
**Sistem Hybrid E-Commerce B2C/B2B & Multi-Branch Supply Chain Management**

---

## 📑 Daftar Isi
1. [Ringkasan Eksekutif & Tujuan Proyek](#1-ringkasan-eksekutif--tujuan-proyek)
2. [Arsitektur Sistem & Peran Pengguna (User Personas)](#2-arsitektur-sistem--peran-pengguna-user-personas)
3. [Kronologi & Tahapan Pengembangan Lengkap (Fase 1 s.d. Fase 8)](#3-kronologi--tahapan-pengembangan-lengkap)
   - [Fase 1: Fondasi E-Commerce B2C & Luxury UX](#fase-1-fondasi-e-commerce-b2c--luxury-ux)
   - [Fase 2: Ekosistem Kemitraan Grosir (B2B Reseller)](#fase-2-ekosistem-kemitraan-grosir-b2b-reseller)
   - [Fase 3: Operasional Multi-Cabang & Rantai Pasok](#fase-3-operasional-multi-cabang--rantai-pasok)
   - [Fase 4: Integrasi Olsera POS Open API (Driver Layer)](#fase-4-integrasi-olsera-pos-open-api-driver-layer)
   - [Fase 5: Komputasi Statistik Safety Stock & Klasifikasi ABC](#fase-5-komputasi-statistik-safety-stock--klasifikasi-abc)
   - [Fase 6: Pengamanan Sistem, Anti-Fraud & Audit Trail](#fase-6-pengamanan-sistem-anti-fraud--audit-trail)
   - [Fase 7: Message Queue, Background Scheduler & Optimasi WebP](#fase-7-message-queue-background-scheduler--optimasi-webp)
   - [Fase 8: Pre-Flight Checklist & Verifikasi Logika Bisnis](#fase-8-pre-flight-checklist--verifikasi-logika-bisnis)
4. [Daftar File Utama Codebase](#4-daftar-file-utama-codebase)
5. [Panduan Deployment ke Server Production](#5-panduan-deployment-ke-server-production)

---

## 1. 🎯 Ringkasan Eksekutif & Tujuan Proyek

**Podgasm** dibangun untuk menjawab kebutuhan bisnis ritel dan distribusi produk *vape, e-liquid, pods, dan aksesoris* yang memiliki toko online ritel publik, kemitraan grosir (reseller), serta jaringan toko fisik (*multi-branch*).

### 🛠️ Spesifikasi Teknologi (Tech Stack):
* **Backend:** Laravel 11/13, PHP 8.3+, MySQL / MariaDB (Optimized Indexes).
* **Frontend:** HTML5 Semantic, CSS3 Custom Tokens, Bootstrap 5.3, Swiper.js, AOS Animation.
* **Payment Gateway:** Midtrans Snap API (QRIS, Virtual Account, Kartu Kredit).
* **Logistik & Ekspedisi:** Biteship API (JNE, J&T, SiCepat, Anteraja) dengan origin terkunci di Gudang Pusat.
* **POS Synchronization:** Olsera POS Open API Driver Layer (Dual-Mode: Mock Sandbox & Live API).
* **Asset Optimization:** Intervention Image v4 (Auto-resize 800px & Konversi WebP 80%).
* **Process Management:** Laravel Queue & Linux Supervisor Worker.

---

## 2. 👥 Arsitektur Sistem & Peran Pengguna (User Personas)

```
                       ┌───────────────────────────────┐
                       │       SUPERVISOR / ADMIN      │
                       │          (Gudang Pusat)       │
                       └───────────────┬───────────────┘
                                       │
         ┌─────────────────────────────┼─────────────────────────────┐
         │                             │                             │
         ▼                             ▼                             ▼
┌──────────────────┐          ┌──────────────────┐          ┌──────────────────┐
│   TOKO CABANG    │          │   MITRA GROSIR   │          │ PELANGGAN RITEL  │
│(Branch Internal) │          │  (B2B Reseller)  │          │  (B2C Customer)  │
└──────────────────┘          └──────────────────┘          └──────────────────┘
```

1. **Admin / Supervisor HQ (Gudang Pusat):**
   * Memantau persediaan stok terpusat (HQ) dan konsolidasi seluruh cabang.
   * Menyetujui/menolak pengajuan *Stock Request* cabang dan registrasi mitra B2B.
   * Mengatur sinkronisasi Olsera POS, banner promo, kupon voucher, dan audit trail.
   * Memantau antrean background worker di panel *Failed Jobs*.
2. **Staf Toko Cabang (Branch Staff):**
   * Mengelola inventori fisik lokal di toko cabang masing-masing.
   * Mengajukan permintaan barang ke pusat (*Stock Request*) dan melacak pengiriman (*Tracking*).
   * Melakukan **Konfirmasi Terima Barang (*Goods Receipt*)** yang otomatis menambah `branch_stocks`.
3. **Mitra Grosir / Reseller (B2B):**
   * Mendaftar dengan upload KTP/bukti toko untuk diverifikasi admin.
   * Berbelanja produk dengan harga grosir bertingkat (*Tier Pricing*).
4. **Pelanggan Ritel (B2C):**
   * Menjelajahi katalog dengan fitur *Search Debounce*, *Filter & Sort*, dan *Age Verification Gate 21+*.
   * Membeli produk via Midtrans Snap, menggunakan kupon voucher diskon, dan live chat CS.

---

## 3. ⏱️ Kronologi & Tahapan Pengembangan Lengkap

---

### Fase 1: Fondasi E-Commerce B2C & Luxury UX
* **Age Verification Modal (21+):**
  - Pop-up verifikasi usia modern dengan *backdrop blur*.
  - Menggunakan `sessionStorage` cerdas: muncul 1x di awal kunjungan dan tidak mengganggu navigasi, filter, maupun pencarian produk.
  - 100% *SEO-Friendly*: Googlebot/crawler dapat mengindeks konten tanpa terblokir.
* **Theme Switcher (Dark & Light Mode):**
  - Transisi mulus antara mode terang dan mode gelap mewah (*Luxury Dark Theme*) tanpa *white-flash*.
* **Katalog & Navigasi Produk:**
  - Mega Menu & Sub-Category Carousel.
  - Filter & Sort Real-Time (*Harga Terendah/Tertinggi, Nama A-Z, Produk Terbaru*).
  - Offcanvas Cart (Keranjang belanja drawer AJAX).
* **Payment Gateway Midtrans:**
  - Integrasi Snap Token untuk QRIS (GoPay, OVO, ShopeePay), VA Bank, dan Kartu Kredit.

---

### Fase 2: Ekosistem Kemitraan Grosir (B2B Reseller)
* **Pendaftaran Mitra B2B Online:** Calon reseller mengisi data usaha dan mengunggah dokumen/KTP.
* **Panel Approval Admin HQ:** Admin meninjau data permohonan, menyetujui (*Approve*), atau menolak (*Reject*).
* **Tier Pricing Engine:** Penetapan harga grosir bertingkat berdasarkan kuantitas minimal order (MOQ) pada tabel `b2b_prices`.

---

### Fase 3: Operasional Multi-Cabang & Rantai Pasok
* **Portal Khusus Staf Cabang:** Dashboard ringkasan stok lokal, pengajuan request, dan status pengiriman.
* **Stock Request & Live Tracking:** Staf cabang dapat memilih produk/varian yang menipis dengan prioritas *Normal* atau *Urgent*.
* **Fitur Goods Receipt ("Terima Barang"):**
  - Saat pasokan tiba di toko cabang, staf menekan tombol **"Terima Barang"**.
  - Sistem secara otomatis mengubah status menjadi `Selesai` dan **menambahkan unit stok ke tabel `branch_stocks`**.

---

### Fase 4: Integrasi Olsera POS Open API (Driver Layer)
* **Driver Architecture (Plug-and-Play):**
  - **Mock Sandbox Mode:** Memungkinkan seluruh sistem berjalan lancar di lingkungan development sebelum kredensial API resmi Olsera diterima.
  - **Live API Mode:** Otomatis aktif begitu `OLSERA_APP_ID`, `OLSERA_SECRET_KEY`, dan `OLSERA_ACCESS_TOKEN` diisi di file `.env`.
* **Multi-Outlet Mapping:** Pemetaan stok antara Gudang Pusat dan Toko Cabang fisik.
* **Manual & Auto Sync:**
  - Tombol *1-Click Sync* di panel admin.
  - Command console: `php artisan olsera:sync`.

---

### Fase 5: Komputasi Statistik Safety Stock & Klasifikasi ABC
* **Algoritma Safety Stock Berbasis Standar Deviasi:**
  - Menghitung standar deviasi penjualan harian 30 hari terakhir.
  - Mengelompokkan SKU ke dalam **Kelas A (Pareto 70% omset)**, **Kelas B (20%)**, dan **Kelas C (10%)**.
  - Menentukan *Service Level* (98%, 95%, 90%) dengan *Z-Score* dan *Lead Time*.
* **Laporan Inventori Multi-Cabang:**
  - Analisis risiko kerugian akibat barang kedaluwarsa (*Expired Date*) dan pita cukai lama.
  - Filter laporan: *Gudang Pusat vs Toko Cabang Spesifik vs Konsolidasi Seluruh Cabang*.

---

### Fase 6: Pengamanan Sistem, Anti-Fraud & Audit Trail
* **Audit Trail Otomatis (`LogsActivity` Trait):**
  - Tabel `activity_logs` mencatat setiap aksi `created`, `updated`, dan `deleted` pada model krusial (`StockRequest`, `BranchStock`, `Order`).
  - Mencatat *old values*, *new values*, IP address, dan User Agent.
  - **Smart Actor Detection:** Mengenali otomatis apakah aktor perubahan adalah *Admin HQ*, *Branch Staff*, *Customer*, atau *System (CLI / Webhook Worker)*.
* **Keamanan Endpoint Webhook Olsera (`VerifyOlseraWebhook`):**
  - Memverifikasi secret token pada header `Authorization: Bearer` atau `X-Olsera-Secret`.
  - Menggunakan `hash_equals()` untuk mencegah *timing attack vulnerabilities*.
* **Proteksi Harga Backend (B2B Tier Pricing):**
  - Request checkout 100% tidak menerima input harga dari frontend; seluruh kalkulasi subtotal dan diskon dihitung murni di server.

---

### Fase 7: Message Queue, Background Scheduler & Optimasi WebP
* **Automated API Polling Olsera (`routes/console.php`):**
  - Menggunakan metode *Pull-Based API Polling* langsung ke endpoint resmi Olsera (`GET /product` & `GET /inventory/stock`) **setiap 5 menit** (`Schedule::command('olsera:sync')->everyFiveMinutes()`).
  - Arsitektur ini menggantikan metode webhook tradisional, memastikan data stok dan varian selalu konsisten langsung dari server pusat Olsera.
* **Automated Cron Scheduler:**
  - `olsera:sync` &rarr; Berjalan otomatis setiap 5 menit (API Polling).
  - `safety-stock:recalculate` &rarr; Berjalan otomatis setiap hari pukul 01:00 pagi.
* **Auto-Compression & WebP Conversion (`ImageOptimizerTrait`):**
  - **Skenario A:** Upload manual oleh admin otomatis di-resize dan dikonversi ke WebP kualitas tinggi.
  - **Skenario B:** Sync API Olsera otomatis mengunduh gambar remote HD (1024px dari CloudFront CDN), mengompresinya, dan menyimpannya ke storage lokal.
* **Live Search Debounce (~280ms):** Mencegah request spamming saat pengunjung mengetik di kolom pencarian navbar.

---

### Fase 8: Pre-Flight Checklist & Verifikasi Logika Bisnis
* **Stock Reservation & Restock Atomic:**
  - Method `cancelOrder()` pada [OrderService.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Services/OrderService.php) otomatis mengembalikan stok produk/varian ke database dan memulihkan kuota voucher belanja jika pesanan dibatalkan/kedaluwarsa.
* **Origin Pengiriman Logistik:**
  - Dikunci ke Area ID Gudang Pusat (HQ) Jakarta Selatan (`IDNP3CL10`) via `.env`.
* **Panel Monitoring Failed Jobs:**
  - Modul [FailedJobController.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Http/Controllers/Admin/FailedJobController.php) dan view [index.blade.php](file:///c:/laragon/www/penjualan-podgasm-main/resources/views/pages/admin/failed-jobs/index.blade.php) memungkinkan Admin HQ memantau antrean gagal dan melakukan *Retry* atau *Flush*.
* **Supervisor Linux Configuration:**
  - File template [podgasm-worker.conf](file:///c:/laragon/www/penjualan-podgasm-main/podgasm-worker.conf) telah siap digunakan untuk server Ubuntu.

---

## 4. 📂 Daftar File Utama Codebase

| Modul | File Lokasi | Deskripsi |
|---|---|---|
| **Olsera POS Driver** | [app/Services/OlseraApiService.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Services/OlseraApiService.php) | Adapter sinkronisasi stok & katalog (Mock & Live Mode). |
| **Olsera Controller** | [app/Http/Controllers/Admin/OlseraSyncController.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Http/Controllers/Admin/OlseraSyncController.php) | Panel sync dan webhook ingestion. |
| **Webhook Security** | [app/Http/Middleware/VerifyOlseraWebhook.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Http/Middleware/VerifyOlseraWebhook.php) | Middleware validasi secret token webhook Olsera. |
| **Webhook Job** | [app/Jobs/ProcessOlseraWebhookJob.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Jobs/ProcessOlseraWebhookJob.php) | Background worker update stok real-time dengan retry. |
| **Audit Trail Trait** | [app/Traits/LogsActivity.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Traits/LogsActivity.php) | Trait pencatat histori perubahan data multi-cabang. |
| **Image Optimizer** | [app/Traits/ImageOptimizerTrait.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Traits/ImageOptimizerTrait.php) | Auto-resize 800px & konversi format WebP 80%. |
| **Order & Restock** | [app/Services/OrderService.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Services/OrderService.php) | Checkout engine, Midtrans confirm, & atomic restock cancel. |
| **Midtrans Service** | [app/Services/MidtransService.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Services/MidtransService.php) | Snap Token generator & webhook callback handler. |
| **Safety Stock** | [app/Http/Controllers/Admin/SafetyStockController.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Http/Controllers/Admin/SafetyStockController.php) | Algoritma klasifikasi ABC & standar deviasi 30 hari. |
| **Failed Jobs UI** | [app/Http/Controllers/Admin/FailedJobController.php](file:///c:/laragon/www/penjualan-podgasm-main/app/Http/Controllers/Admin/FailedJobController.php) | Controller monitoring antrean gagal untuk Admin HQ. |
| **Supervisor Conf** | [podgasm-worker.conf](file:///c:/laragon/www/penjualan-podgasm-main/podgasm-worker.conf) | Konfigurasi background worker untuk server Ubuntu Linux. |

---

## 5. 🚀 Panduan Deployment ke Server Production

### 1. Konfigurasi Environment (`.env`):
```env
APP_NAME=Podgasm
APP_ENV=production
APP_DEBUG=false
APP_URL=https://podgasm.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=podgasm_db
DB_USERNAME=podgasm_user
DB_PASSWORD=secret_password

# Queue Driver
QUEUE_CONNECTION=database

# Midtrans Payment Gateway
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxx
MIDTRANS_IS_PRODUCTION=true

# Biteship Logistics
BITESHIP_API_KEY=biteship_live_xxxxxxxxx
BITESHIP_ORIGIN_AREA_ID=IDNP3CL10

# Olsera POS API
OLSERA_APP_ID=olsera_app_id_anda
OLSERA_SECRET_KEY=olsera_secret_key_anda
OLSERA_ACCESS_TOKEN=olsera_access_token_anda
OLSERA_WEBHOOK_SECRET=olsera_webhook_secret_anda
OLSERA_MOCK_MODE=false
```

### 2. Perintah Build & Optimasi di Server:
```bash
# 1. Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 2. Database Migration & Symlink
php artisan migrate --force
php artisan storage:link

# 3. Cache Optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Pasang Linux Cron & Supervisor:
```bash
# A. Daftarkan Laravel Task Scheduler di crontab Linux
crontab -e
# Tambahkan baris:
* * * * * cd /var/www/podgasm && php artisan schedule:run >> /dev/null 2>&1

# B. Pasang Supervisor Worker
sudo cp /var/www/podgasm/podgasm-worker.conf /etc/supervisor/conf.d/podgasm-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start podgasm-worker:*
```

---

*Dokumentasi ini mencakup seluruh arsitektur dan siklus pengembangan Podgasm dari awal hingga rilis production.*
