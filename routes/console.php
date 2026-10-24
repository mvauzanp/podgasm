<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| AUTOMATED CRON SCHEDULES
|--------------------------------------------------------------------------
*/

// 1. API Polling otomatis stok & katalog Olsera POS setiap 1 menit (In-Process Execution aman untuk Shared Hosting)
Schedule::call(function () {
    Artisan::call('olsera:sync');
})->everyMinute()->name('olsera-sync-inprocess');

// 2. Rekalkulasi otomatis statistik Safety Stock (Data 90 Hari Olsera & E-Commerce) setiap hari pukul 00:00
Schedule::call(function () {
    Artisan::call('safety-stock:recalculate');
})->dailyAt('00:00')->name('safety-stock-recalculate-inprocess');

// 3. Otomatis batalkan pesanan pending payment yang tidak dibayar lebih dari 24 jam setiap jam
Schedule::call(function () {
    Artisan::call('orders:cancel-expired', ['--hours' => 24]);
})->hourly()->name('orders-cancel-expired-inprocess');

