<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ============================================================
 * SendBillingRemindersCommand
 * ============================================================
 *
 * Command ini mengirim 2 jenis pengingat tagihan via EMAIL:
 *
 *   1. Setiap TANGGAL 1 bulan → ingatkan SEMUA customer aktif
 *      yang punya invoice bulan ini (status belum_bayar /
 *      menunggu_verifikasi / ditolak).
 *
 *   2. Setiap TANGGAL 15 bulan → ingatkan customer yang
 *      invoice-nya jatuh tempo tanggal 15 bulan berjalan.
 *
 * Catatan:
 *   - Channel email dulu (WhatsApp menyusul setelah gateway live).
 *   - Skip customer tanpa email.
 *   - try/catch supaya 1 customer gagal tidak menghentikan loop.
 *
 * Penjadwalan via cron-job.org (lihat SETUP.md):
 *   /cron/billing-reminder-active  →  tgl 1  jam 08:00
 *   /cron/billing-reminder-due     →  tgl 15 jam 08:00
 *
 * Cara menjalankan manual:
 *   php artisan billing:send-reminders --type=active
 *   php artisan billing:send-reminders --type=due
 *   php artisan billing:send-reminders --type=active --dry-run
 */
class SendBillingRemindersCommand extends Command
{
    protected $signature = 'billing:send-reminders
        {--type=active : Jenis reminder (active|due)}
        {--dry-run : Hanya simulasi, jangan kirim email}';

    protected $description = 'Kirim pengingat tagihan via email (tanggal 1 & 15 setiap bulan).';

    public function handle(): int
    {
        $type = $this->option('type');
        $isDryRun = (bool) $this->option('dry-run');

        if (!in_array($type, ['active', 'due'], true)) {
            $this->error("Nilai --type tidak valid. Gunakan 'active' atau 'due'.");
            return self::FAILURE;
        }

        $today = Carbon::today();
        $this->info("Memproses reminder '{$type}' untuk tanggal {$today->format('Y-m-d')}...");

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

        foreach ($items as $row) {
            /** @var Invoice $invoice */
            $invoice = $row['invoice'];
            /** @var Customer $customer */
            $customer = $row['customer'];

            $user = $customer->user;
            $email = $user?->email;

            if (empty($email)) {
                $this->line("  [SKIP] {$customer->name} — tidak punya email");
                $skipped++;
                continue;
            }

            $notificationType = $type === 'active'
                ? NotificationType::BILLING_REMINDER_ACTIVE
                : NotificationType::BILLING_REMINDER_DUE;

            if ($isDryRun) {
                $this->line("  [DRY-RUN] akan kirim ke {$customer->name} ({$email})");
                $sent++;
                continue;
            }

            $ok = $this->sendEmail($invoice, $user, $customer, $notificationType);

            if ($ok) {
                $this->info("  [SENT] {$customer->name} ({$email})");
                $sent++;
            } else {
                $this->warn("  [FAIL] {$customer->name} ({$email}) — lihat laravel.log");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Selesai. Terkirim: {$sent}, Gagal: {$failed}, Dilewati: {$skipped}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function sendEmail(Invoice $invoice, $user, Customer $customer, NotificationType $type): bool
    {
        $portalUrl = $customer->getPortalUrl();
        $isActive = $type === NotificationType::BILLING_REMINDER_ACTIVE;

        $subject = $isActive
            ? "Tagihan Baru {$invoice->invoice_number} — K2-Net"
            : "Pengingat: Tagihan {$invoice->invoice_number} jatuh tempo hari ini — K2-Net";

        $body = $isActive ? $this->activeEmailBody($user, $invoice, $portalUrl)
                          : $this->dueEmailBody($user, $invoice, $portalUrl);

        try {
            Mail::send([], [], function ($message) use ($user, $subject, $body) {
                $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->html($body);
            });

            NotificationLog::create([
                'invoice_id'        => $invoice->id,
                'customer_id'       => $customer->id,
                'notification_type' => $type->value,
                'channel'           => 'email',
                'status'            => 'sent',
                'sent_at'           => now(),
                'meta'              => [
                    'email'          => $user->email,
                    'invoice_number' => $invoice->invoice_number,
                    'amount'         => $invoice->formattedAmount(),
                    'due_date'       => $invoice->due_date->format('d M Y'),
                    'portal_url'     => $portalUrl,
                ],
            ]);

            return true;
        } catch (\Throwable $e) {
            NotificationLog::create([
                'invoice_id'        => $invoice->id,
                'customer_id'       => $customer->id,
                'notification_type' => $type->value,
                'channel'           => 'email',
                'status'            => 'failed',
                'sent_at'           => now(),
                'failed_at'         => now(),
                'error_message'     => $e->getMessage(),
                'meta'              => [
                    'email'          => $user->email,
                    'invoice_number' => $invoice->invoice_number,
                ],
            ]);

            Log::error('[BillingReminder] Gagal kirim email', [
                'invoice_id' => $invoice->id,
                'email'      => $user->email,
                'type'       => $type->value,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
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

    protected function activeEmailBody($user, Invoice $invoice, string $portalUrl): string
    {
        $period = $invoice->billing_period->format('F Y');
        $dueDate = $invoice->due_date->format('d M Y');
        $amount = $invoice->formattedAmount();

        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: #0091ea; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin:0;'>K2-Net</h2>
                    <p style='margin:5px 0 0;'>Tagihan Internet Bulanan</p>
                </div>
                <div style='padding: 20px; background: #f9f9f9;'>
                    <p>Halo <strong>{$user->name}</strong>,</p>
                    <p>Tagihan internet K2-Net Anda untuk periode <strong>{$period}</strong> sudah diterbitkan.</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                        <tr>
                            <td style='padding:8px; border:1px solid #ddd;'>No. Tagihan</td>
                            <td style='padding:8px; border:1px solid #ddd; font-weight:bold;'>{$invoice->invoice_number}</td>
                        </tr>
                        <tr style='background:#f0f0f0;'>
                            <td style='padding:8px; border:1px solid #ddd;'>Periode</td>
                            <td style='padding:8px; border:1px solid #ddd;'>{$period}</td>
                        </tr>
                        <tr>
                            <td style='padding:8px; border:1px solid #ddd;'>Jumlah</td>
                            <td style='padding:8px; border:1px solid #ddd; font-weight:bold; color:#0091ea;'>Rp {$amount}</td>
                        </tr>
                        <tr style='background:#f0f0f0;'>
                            <td style='padding:8px; border:1px solid #ddd;'>Jatuh Tempo</td>
                            <td style='padding:8px; border:1px solid #ddd;'>{$dueDate}</td>
                        </tr>
                    </table>
                    <p style='text-align: center; margin: 20px 0;'>
                        <a href='{$portalUrl}' style='background: #0091ea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Bayar Sekarang</a>
                    </p>
                    <p style='color: #666; font-size: 12px; text-align: center;'>
                        Atau salin tautan berikut ke browser:<br/>
                        <a href='{$portalUrl}' style='color: #0091ea;'>{$portalUrl}</a>
                    </p>
                </div>
                <div style='padding: 15px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee;'>
                    K2-Net — Sistem Manajemen Tagihan & Pelanggan
                </div>
            </div>
        ";
    }

    protected function dueEmailBody($user, Invoice $invoice, string $portalUrl): string
    {
        $period = $invoice->billing_period->format('F Y');
        $dueDate = $invoice->due_date->format('d M Y');
        $amount = $invoice->formattedAmount();

        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: #f59e0b; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin:0;'>K2-Net</h2>
                    <p style='margin:5px 0 0;'>Pengingat Jatuh Tempo</p>
                </div>
                <div style='padding: 20px; background: #f9f9f9;'>
                    <p>Halo <strong>{$user->name}</strong>,</p>
                    <p>Ini adalah pengingat bahwa tagihan internet Anda <strong>jatuh tempo hari ini</strong>. Mohon segera lakukan pembayaran agar layanan tidak terganggu.</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                        <tr>
                            <td style='padding:8px; border:1px solid #ddd;'>No. Tagihan</td>
                            <td style='padding:8px; border:1px solid #ddd; font-weight:bold;'>{$invoice->invoice_number}</td>
                        </tr>
                        <tr style='background:#f0f0f0;'>
                            <td style='padding:8px; border:1px solid #ddd;'>Periode</td>
                            <td style='padding:8px; border:1px solid #ddd;'>{$period}</td>
                        </tr>
                        <tr>
                            <td style='padding:8px; border:1px solid #ddd;'>Jumlah</td>
                            <td style='padding:8px; border:1px solid #ddd; font-weight:bold; color:#f59e0b;'>Rp {$amount}</td>
                        </tr>
                        <tr style='background:#f0f0f0;'>
                            <td style='padding:8px; border:1px solid #ddd;'>Jatuh Tempo</td>
                            <td style='padding:8px; border:1px solid #ddd;'>{$dueDate}</td>
                        </tr>
                    </table>
                    <p style='text-align: center; margin: 20px 0;'>
                        <a href='{$portalUrl}' style='background: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Bayar Sekarang</a>
                    </p>
                    <p style='color: #666; font-size: 12px; text-align: center;'>
                        Atau salin tautan berikut ke browser:<br/>
                        <a href='{$portalUrl}' style='color: #0091ea;'>{$portalUrl}</a>
                    </p>
                </div>
                <div style='padding: 15px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee;'>
                    K2-Net — Sistem Manajemen Tagihan & Pelanggan
                </div>
            </div>
        ";
    }
}
