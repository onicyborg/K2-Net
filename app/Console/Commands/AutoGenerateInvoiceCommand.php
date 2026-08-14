<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoGenerateInvoiceCommand extends Command
{
    protected $signature = 'invoices:auto-generate';

    protected $description = 'Generate invoice otomatis untuk semua pelanggan aktif H-3 sebelum tanggal 1';

    public function handle(): int
    {
        $nextMonth = Carbon::today()->addMonthNoOverflow()->startOfMonth();
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

            $dueDate = $nextMonth->copy()->addDays(14);

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
                    'invoice_number' => $invoice->invoice_number,
                    'amount'        => $invoice->formattedAmount(),
                    'billing_period'=> $invoice->billing_period->format('F Y'),
                    'due_date'      => $invoice->due_date->format('d M Y'),
                    'portal_url'    => $portalUrl,
                ],
            ]);
        }
    }
}
