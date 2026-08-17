<?php

namespace App\Console\Commands;

use App\Events\InvoiceCreated;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\SystemConfiguration;
use App\Notifications\EmailTemplates;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AutoGenerateInvoiceCommand extends Command
{
    protected $signature = 'invoices:auto-generate';

    protected $description = 'Generate invoice otomatis untuk semua pelanggan aktif bulan depan + kirim email';

    public function handle(): int
    {
        $nextMonth = Carbon::today()->addMonthNoOverflow()->startOfMonth();
        $dueDay = SystemConfiguration::getValue('invoice_due_day', 15);

        $this->info("Generate invoice untuk periode {$nextMonth->format('F Y')}...");

        $customers = Customer::where('status', 'aktif')
            ->with(['package', 'user'])
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($customers as $customer) {
            if (!$customer->package) {
                $this->line("  [SKIP] {$customer->name} — tidak punya paket");
                continue;
            }

            $dueDate = $nextMonth->copy()->addMonth()->day($dueDay)->startOfDay();

            $existing = Invoice::where('customer_id', $customer->id)
                ->where('billing_period', $nextMonth->toDateString())
                ->first();

            if ($existing) {
                $this->line("  [SKIP] {$customer->name} — invoice sudah ada");
                $skipped++;
                continue;
            }

            $invoiceNumber = $this->generateInvoiceNumber($nextMonth);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id'    => $customer->id,
                'billing_period' => $nextMonth->toDateString(),
                'amount'         => $customer->package->price,
                'due_date'       => $dueDate->toDateString(),
                'status'         => 'belum_bayar',
                'issued_at'      => now(),
            ]);

            // ============================================================
            // Trigger event InvoiceCreated → Listener akan otomatis kirim
            // WhatsApp via WhatsAppService (Kondisi 1 dari requirement).
            // ============================================================
            event(new InvoiceCreated($invoice));

            $this->sendNotification($invoice, $customer);
            $created++;

            $this->info("  [CREATED] {$invoice->invoice_number} untuk {$customer->name}");
        }

        $this->info("Selesai. Dibuat: {$created}, Dilewati: {$skipped}");
        return Command::SUCCESS;
    }

    private function generateInvoiceNumber(Carbon $period): string
    {
        $prefix = 'INV';
        $month  = $period->format('m');
        $year   = $period->format('Y');
        $count  = Invoice::whereYear('billing_period', $year)
            ->whereMonth('billing_period', $month)
            ->count() + 1;
        return sprintf('%s/%s/%s/%04d', $prefix, $month, $year, $count);
    }

    private function sendNotification(Invoice $invoice, Customer $customer): void
    {
        $user = $customer->user;
        $portalUrl = $customer->getPortalUrl();

        // Log notifications
        foreach (['whatsapp', 'email'] as $channel) {
            $recipient = $channel === 'whatsapp'
                ? ($customer->whatsapp_number_full ?? $customer->whatsapp_number)
                : $user?->email;

            if (!$recipient) {
                continue;
            }

            NotificationLog::create([
                'invoice_id'         => $invoice->id,
                'customer_id'        => $customer->id,
                'notification_type'  => 'reminder_h3',
                'channel'           => $channel,
                'status'            => 'sent',
                'sent_at'           => now(),
                'meta'              => [
                    $channel === 'whatsapp' ? 'whatsapp_number' : 'email' => $recipient,
                    'invoice_number'  => $invoice->invoice_number,
                    'amount'         => $invoice->formattedAmount(),
                    'billing_period' => $invoice->billing_period->format('F Y'),
                    'due_date'       => $invoice->due_date->format('d M Y'),
                    'portal_url'     => $portalUrl,
                ],
            ]);
        }

        // Send actual email
        if (!$user?->email) {
            return;
        }

        $this->sendEmail($invoice, $user, $portalUrl);
    }

    private function sendEmail(Invoice $invoice, $user, string $portalUrl): void
    {
        $subject = "Tagihan {$invoice->invoice_number} — {$invoice->billing_period->format('F Y')}";

        try {
            Mail::send([], [], function ($message) use ($user, $invoice, $portalUrl, $subject) {
                $logoCid = EmailTemplates::attachLogo($message);

                $content = EmailTemplates::greeting($user->name);
                $content .= EmailTemplates::paragraph('Berikut tagihan internet Anda untuk periode <strong>' . $invoice->billing_period->format('F Y') . '</strong>. Harap lakukan pembayaran sebelum tanggal jatuh tempo.');
                $content .= EmailTemplates::invoiceTable([
                    [
                        'invoice_number' => $invoice->invoice_number,
                        'billing_period' => $invoice->billing_period->format('F Y'),
                        'amount' => 'Rp ' . $invoice->formattedAmount(),
                        'due_date' => $invoice->due_date->format('d M Y'),
                    ],
                ]);
                $content .= EmailTemplates::ctaButton($portalUrl, 'Bayar Sekarang');
                $content .= EmailTemplates::fallbackLink($portalUrl);

                $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->html(EmailTemplates::wrapper(
                        $content,
                        'Tagihan Internet',
                        'K2-Net — Sistem Manajemen Tagihan & Pelanggan',
                        EmailTemplates::PRIMARY_COLOR,
                        $logoCid
                    ));
            });
        } catch (\Throwable $e) {
            $this->warn("  [EMAIL FAILED] {$user->email} — {$e->getMessage()}");
        }
    }
}
