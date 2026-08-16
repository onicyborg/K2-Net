<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =============================================================
// SCHEDULER K2-NET — Jadwal Notifikasi WhatsApp
// =============================================================
// Laravel 11/12 tidak punya Kernel.php lagi; semua jadwal ditulis
// di sini menggunakan facade Schedule.
//
// Cara kerja:
//   - php artisan schedule:run dipanggil tiap menit oleh cron OS.
//   - Setiap method monthlyOn(D, 'HH:MM') akan trigger command
//     hanya pada tanggal D bulan tersebut, pada jam HH:MM.
//
// Di shared hosting cPanel, tambahkan satu entry cron:
//   * * * * * cd /home/<user>/k2-net && php artisan schedule:run >> /dev/null 2>&1
// (lihat SETUP.md untuk detail lengkap).

// ----------------------------------------------------------
// Reminder H-3 & H+3 via email (command existing).
// ----------------------------------------------------------
Schedule::command('invoices:remind')->dailyAt('08:00');

// ----------------------------------------------------------
// Auto-generate invoice bulanan (command existing).
// ----------------------------------------------------------
Schedule::command('invoices:auto-generate')->monthlyOn(28, '08:05');

// ----------------------------------------------------------
// KONDISI 2: TANGGAL 1 — Pengingat tagihan aktif.
// ----------------------------------------------------------
// Trigger pada tanggal 1 setiap bulan, jam 08:00 WIB.
// Memanggil command 'billing:send-reminders --type=active'
// yang mengirim WhatsApp ke SEMUA customer dengan invoice
// bulan ini yang belum lunas.
Schedule::command('billing:send-reminders', ['--type=active'])
    ->monthlyOn(1, '08:00')
    ->name('billing-reminder-active')
    ->withoutOverlapping()
    ->onOneServer();

// ----------------------------------------------------------
// KONDISI 3: TANGGAL 15 — Pengingat jatuh tempo.
// ----------------------------------------------------------
// Trigger pada tanggal 15 setiap bulan, jam 08:00 WIB.
// Memanggil command 'billing:send-reminders --type=due'
// yang mengirim WhatsApp ke customer yang invoice-nya
// jatuh tempo HARI INI (tanggal 15).
Schedule::command('billing:send-reminders', ['--type=due'])
    ->monthlyOn(15, '08:00')
    ->name('billing-reminder-due')
    ->withoutOverlapping()
    ->onOneServer();
