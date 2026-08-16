<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * ============================================================
 * SendBillingRemindersCommand
 * ============================================================
 *
 * Command ini mengirim 2 jenis pengingat tagihan via WhatsApp:
 *
 *   1. Setiap TANGGAL 1 bulan → ingatkan SEMUA customer aktif
 *      yang punya invoice bulan ini (status belum_bayar /
 *      menunggu_verifikasi / ditolak).
 *
 *   2. Setiap TANGGAL 15 bulan → ingatkan customer yang
 *      invoice-nya jatuh tempo tanggal 15 bulan berjalan.
 *
 * Anti-banned:
 *   - Ada jeda (delay) antar pesan, default 7 detik.
 *   - Skip customer tanpa nomor WhatsApp.
 *   - Gunakan try/catch supaya 1 customer gagal tidak menghentikan loop.
 *
 * Penjadwalan didefinisikan di routes/console.php:
 *   Schedule::command('billing:send-reminders', ['--type=active'])->monthlyOn(1, '08:00');
 *   Schedule::command('billing:send-reminders', ['--type=due'])->monthlyOn(15, '08:00');
 *
 * Cara menjalankan manual untuk testing:
 *   php artisan billing:send-reminders --type=active
 *   php artisan billing:send-reminders --type=due
 *   php artisan billing:send-reminders --type=active --dry-run
 */
class SendBillingRemindersCommand extends Command
{
    /**
     * Nama & argumen command.
     *
     * --type=active → kirim reminder tagihan aktif (tgl 1)
     * --type=due    → kirim reminder jatuh tempo (tgl 15)
     * --dry-run     → hanya tampilkan ringkasan, tidak kirim
     */
    protected $signature = 'billing:send-reminders
        {--type=active : Jenis reminder (active|due)}
        {--dry-run : Hanya simulasi, jangan kirim pesan}';

    protected $description = 'Kirim pengingat tagihan via WhatsApp (tanggal 1 & 15 setiap bulan).';

    /**
     * Inject WhatsAppService via constructor (auto-resolved oleh Laravel).
     */
    public function handle(WhatsAppService $wa): int
    {
        $type = $this->option('type');
        $isDryRun = (bool) $this->option('dry-run');

        if (!in_array($type, ['active', 'due'], true)) {
            $this->error("Nilai --type tidak valid. Gunakan 'active' atau 'due'.");
            return self::FAILURE;
        }

        $today = Carbon::today();
        $this->info("Memproses reminder '{$type}' untuk tanggal {$today->toDateString()}...");

        // ============================================================
        // Feature Flag — kalau channel WhatsApp dimatikan via .env,
        // keluar lebih awal. Email channel tidak terpengaruh karena
        // ditangani command terpisah (invoices:remind).
        // ============================================================
        if (!$wa->isEnabled()) {
            $this->warn('Notifikasi WhatsApp dinonaktifkan (WA_NOTIFICATIONS_ENABLED=false). Command selesai tanpa kirim pesan.');
            return self::SUCCESS;
        }

        // Ambil kandidat customer + invoice sesuai jenis reminder.
        $items = $type === 'active'
            ? $this->collectActiveBillingInvoices()
            : $this->collectDueDateInvoices($today);

        if ($items->isEmpty()) {
            $this->warn('Tidak ada customer yang perlu dikirimi reminder.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$items->count()} invoice untuk diproses.");
        $this->newLine();

        $sent = 0;
        $skipped = 0;
        $failed = 0;
        $delayMs = (int) config('whatsapp.broadcast_delay_ms', 7000);

        foreach ($items as $row) {
            /** @var Invoice $invoice */
            $invoice = $row['invoice'];
            /** @var Customer $customer */
            $customer = $row['customer'];

            $number = $customer->whatsapp_number_full
                ?: $customer->whatsapp_number;

            if (empty($number)) {
                $this->line("  [SKIP] {$customer->name} — tidak punya nomor WhatsApp");
                $skipped++;
                continue;
            }

            $message = $type === 'active'
                ? $this->buildActiveMessage($customer, $invoice)
                : $this->buildDueMessage($customer, $invoice);

            if ($isDryRun) {
                $this->line("  [DRY-RUN] akan kirim ke {$customer->name} ({$number})");
                $this->line('    ' . str_replace("\n", ' / ', $message));
                $sent++;
                continue;
            }

            // Kirim via service (otomatis log ke notification_logs).
            $notificationType = $type === 'active'
                ? NotificationType::BILLING_REMINDER_ACTIVE
                : NotificationType::BILLING_REMINDER_DUE;

            $ok = $wa->send($customer, $message, $notificationType, $invoice);

            if ($ok) {
                $this->info("  [SENT] {$customer->name} ({$number})");
                $sent++;
            } else {
                $this->warn("  [FAIL] {$customer->name} ({$number}) — lihat notification_logs");
                $failed++;
            }

            // ---------- ANTI-BANNED: jeda antar pesan ----------
            // Jangan kirim terlalu cepat; WhatsApp bisa menandai
            // sebagai spam. Default 7 detik, configurable via env.
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        $this->newLine();
        $this->info("Selesai. Terkirim: {$sent}, Gagal: {$failed}, Dilewati: {$skipped}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Kumpulkan invoice "aktif" (semua customer aktif yang
     * punya invoice bulan ini yang belum lunas).
     *
     * @return \Illuminate\Support\Collection<int, array{invoice: Invoice, customer: Customer}>
     */
    protected function collectActiveBillingInvoices()
    {
        $today = Carbon::today();

        return Invoice::query()
            ->with('customer')
            ->whereHas('customer', fn ($q) => $q->where('status', 'aktif'))
            ->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'ditolak'])
            ->whereMonth('billing_period', $today->month)
            ->whereYear('billing_period', $today->year)
            ->orderBy('due_date')
            ->get()
            ->map(fn ($invoice) => [
                'invoice'  => $invoice,
                'customer' => $invoice->customer,
            ]);
    }

    /**
     * Kumpulkan invoice yang jatuh tempo TANGGAL 15 bulan ini
     * (untuk cron tanggal 15).
     *
     * @return \Illuminate\Support\Collection<int, array{invoice: Invoice, customer: Customer}>
     */
    protected function collectDueDateInvoices(Carbon $today)
    {
        return Invoice::query()
            ->with('customer')
            ->whereHas('customer', fn ($q) => $q->where('status', 'aktif'))
            ->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'ditolak'])
            ->whereDay('due_date', $today->day)
            ->whereMonth('due_date', $today->month)
            ->whereYear('due_date', $today->year)
            ->orderBy('due_date')
            ->get()
            ->map(fn ($invoice) => [
                'invoice'  => $invoice,
                'customer' => $invoice->customer,
            ]);
    }

    /**
     * Template pesan reminder tagihan AKTIF (tanggal 1).
     */
    protected function buildActiveMessage(Customer $customer, Invoice $invoice): string
    {
        $portalUrl = $customer->getPortalUrl();
        $period = $invoice->billing_period->format('F Y');
        $dueDate = $invoice->due_date->format('d M Y');
        $amount = $invoice->formattedAmount();

        return
            "Halo *{$customer->name}*,\n\n" .
            "Tagihan internet K2-Net Anda untuk periode *{$period}* sudah diterbitkan.\n\n" .
            "📄 No. Tagihan : {$invoice->invoice_number}\n" .
            "💰 Jumlah      : {$amount}\n" .
            "📅 Jatuh Tempo : {$dueDate}\n\n" .
            "Bayar sekarang melalui portal:\n{$portalUrl}\n\n" .
            "Terima kasih 🙏\n— Tim K2-Net";
    }

    /**
     * Template pesan reminder JATUH TEMPO (tanggal 15).
     */
    protected function buildDueMessage(Customer $customer, Invoice $invoice): string
    {
        $portalUrl = $customer->getPortalUrl();
        $period = $invoice->billing_period->format('F Y');
        $dueDate = $invoice->due_date->format('d M Y');
        $amount = $invoice->formattedAmount();

        return
            "Halo *{$customer->name}*,\n\n" .
            "⚠️ Ini adalah pengingat bahwa tagihan internet Anda *jatuh tempo HARI INI*.\n\n" .
            "📄 No. Tagihan : {$invoice->invoice_number}\n" .
            "💰 Jumlah      : {$amount}\n" .
            "📅 Jatuh Tempo : {$dueDate}\n\n" .
            "Mohon segera lakukan pembayaran sebelum layanan dinonaktifkan:\n{$portalUrl}\n\n" .
            "Terima kasih 🙏\n— Tim K2-Net";
    }
}
