<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\SystemConfiguration;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InvoiceReminderCommand extends Command
{
    protected $signature = 'invoices:remind';

    protected $description = 'Kirim reminder tagihan H-3, H-0, dan H+3 jatuh tempo';

    public function handle(): int
    {
        $reminderDays = SystemConfiguration::getValue('notification_reminder_days', [-3, 0, 3]);
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

            foreach ($reminderDays as $day) {
                $targetDate = Carbon::parse($invoice->due_date)->addDays($day)->startOfDay();
                $isTarget = $today->equalTo($targetDate);

                if (!$isTarget) {
                    continue;
                }

                $type = $this->reminderType($day);

                $alreadySent = NotificationLog::where('invoice_id', $invoice->id)
                    ->where('notification_type', $type)
                    ->whereDate('created_at', $today->toDateString())
                    ->exists();

                if ($alreadySent) {
                    $skipped++;
                    $this->line("  [SKIP] {$invoice->invoice_number} ({$type}) — sudah dikirim");
                    continue;
                }

                $this->sendReminder($invoice, $type);
                $sent++;

                $this->info("  [SENT] {$invoice->invoice_number} ({$type}) ke {$invoice->customer->whatsapp_number}");
            }
        }

        $this->info("Selesai. Dikirim: {$sent}, Dilewati: {$skipped}");
        return Command::SUCCESS;
    }

    private function reminderType(int $day): string
    {
        return match (true) {
            $day < 0  => 'reminder_h3',
            $day === 0 => 'reminder_h0',
            $day > 0  => 'reminder_h3_late',
        };
    }

    private function sendReminder(Invoice $invoice, string $type): void
    {
        $customer = $invoice->customer;
        $user = $customer->user;
        $portalUrl = $customer->getPortalUrl();

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

        if ($user?->email) {
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
        }
    }
}
