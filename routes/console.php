<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =============================================================
// SCHEDULER DINONAKTIFKAN
// =============================================================
// Cron job dijalankan via external cron service (cron-job.org)
// yang hit endpoint HTTP terlindungi token.
//
// Job yang dijalankan:
//   - invoices:remind                          (daily 08:00) — email
//   - invoices:auto-generate                   (monthly 28 08:05) — generate + email
//   - billing:send-reminders --type=active     (monthly 1 08:00) — WhatsApp
//   - billing:send-reminders --type=due        (monthly 15 08:00) — WhatsApp
//
// Lihat SETUP.md untuk konfigurasi cron-job.org.
