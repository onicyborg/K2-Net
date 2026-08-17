<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\SystemConfiguration;
use App\Notifications\EmailTemplates;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceReminderCommand extends Command
{
    protected $signature = 'invoices:remind';

    protected $description = 'Kirim reminder tagihan H-3 sebelum jatuh tempo dan H+3 setelah jatuh tempo via email';

    public function handle(): int
    {
        $today = Carbon::today();

        $this->info("Memproses reminder tagihan untuk {$today->format('Y-m-d')}...");

        $invoices = Invoice::with('customer.user')
            ->whereIn('status', ['belum_bayar', 'ditolak'])
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($invoices as $invoice) {
            if (!$invoice->customer) {
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $daysUntilDue = $today->diffInDays($dueDate, false); // negatif = lewat

            // H-3: 3 hari sebelum jatuh tempo
            if ($daysUntilDue == 3) {
                $sent += $this->sendIfNotSent($invoice, 'reminder_before', $today);
                continue;
            }

            // H+3: 3 hari setelah jatuh tempo (sudah lewat)
            if ($daysUntilDue == -3) {
                $sent += $this->sendIfNotSent($invoice, 'reminder_overdue', $today);
                continue;
            }
        }

        $this->info("Selesai. Dikirim: {$sent}, Dilewati: {$skipped}");
        return Command::SUCCESS;
    }

    private function sendIfNotSent(Invoice $invoice, string $type, Carbon $today): int
    {
        $alreadySent = NotificationLog::where('invoice_id', $invoice->id)
            ->where('notification_type', $type)
            ->whereDate('created_at', $today->toDateString())
            ->exists();

        if ($alreadySent) {
            $this->line("  [SKIP] {$invoice->invoice_number} ({$type}) — sudah dikirim hari ini");
            return 0;
        }

        $this->sendEmail($invoice, $type);
        $this->info("  [SENT] {$invoice->invoice_number} ({$type})");
        return 1;
    }

    private function sendEmail(Invoice $invoice, string $type): void
    {
        $customer = $invoice->customer;
        $user = $customer->user;
        $portalUrl = $customer->getPortalUrl();

        if (!$user?->email) {
            return;
        }

        $subject = $type === 'reminder_before'
            ? "Reminder: Tagihan {$invoice->invoice_number} jatuh tempo 3 hari lagi — K2-Net"
            : "Penting: Tagihan {$invoice->invoice_number} sudah lewat jatuh tempo — K2-Net";

        $isOverdue = $type === 'reminder_overdue';

        try {
            Mail::send([], [], function ($message) use ($user, $invoice, $portalUrl, $subject, $isOverdue) {
                $accentColor = $isOverdue ? '#dc2626' : EmailTemplates::PRIMARY_COLOR;
                $logoCid = EmailTemplates::attachLogo($message);

                $content = EmailTemplates::greeting($user->name);
                $content .= EmailTemplates::paragraph(
                    $isOverdue
                        ? 'Tagihan Anda sudah <strong>melewati</strong> tanggal jatuh tempo. Segera lakukan pembayaran agar layanan tidak terganggu.'
                        : 'Tagihan Anda akan jatuh tempo dalam <strong>3 hari</strong>. Harap segera lakukan pembayaran.'
                );
                $content .= EmailTemplates::invoiceTable([
                    [
                        'invoice_number' => $invoice->invoice_number,
                        'billing_period' => $invoice->billing_period->format('F Y'),
                        'amount' => 'Rp ' . $invoice->formattedAmount(),
                        'due_date' => $invoice->due_date->format('d M Y'),
                    ],
                ]);
                $content .= EmailTemplates::ctaButton($portalUrl, 'Bayar Sekarang', $accentColor);
                $content .= EmailTemplates::fallbackLink($portalUrl);

                $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->html(EmailTemplates::wrapper(
                        $content,
                        $isOverdue ? 'Tagihan Lewat Jatuh Tempo' : 'Pengingat Tagihan',
                        'K2-Net — Sistem Manajemen Tagihan & Pelanggan',
                        $accentColor,
                        $logoCid
                    ));
            });

            NotificationLog::create([
                'invoice_id'         => $invoice->id,
                'customer_id'        => $customer->id,
                'notification_type'  => $type,
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

            // WhatsApp — log only (gateway not integrated yet)
            NotificationLog::create([
                'invoice_id'         => $invoice->id,
                'customer_id'        => $customer->id,
                'notification_type'  => $type,
                'channel'           => 'whatsapp',
                'status'            => 'sent',
                'sent_at'           => now(),
                'meta'              => [
                    'whatsapp_number' => $customer->whatsapp_number_full ?? $customer->whatsapp_number,
                    'invoice_number'   => $invoice->invoice_number,
                    'amount'           => $invoice->formattedAmount(),
                    'due_date'         => $invoice->due_date->format('d M Y'),
                    'portal_url'       => $portalUrl,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->warn("  [EMAIL FAILED] {$user->email} — {$e->getMessage()}");
            Log::error('[InvoiceReminder] Gagal kirim email', [
                'invoice_id'  => $invoice->id,
                'email'       => $user->email,
                'type'        => $type,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
