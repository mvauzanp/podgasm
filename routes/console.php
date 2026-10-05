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

// 1. API Polling otomatis stok & katalog Olsera POS setiap 5 menit (Pull-based Architecture)
Schedule::command('olsera:sync')->everyFiveMinutes()->withoutOverlapping();

// 2. Rekalkulasi otomatis statistik Safety Stock (Data 90 Hari Olsera & E-Commerce) setiap hari pukul 00:00
Schedule::command('safety-stock:recalculate')->dailyAt('00:00')->withoutOverlapping();

// 3. Otomatis batalkan pesanan pending payment yang tidak dibayar lebih dari 24 jam setiap jam
Schedule::command('orders:cancel-expired --hours=24')->hourly()->withoutOverlapping();


