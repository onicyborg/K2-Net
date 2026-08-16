<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\SystemConfiguration;
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
                $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <div style='background: #0091ea; color: white; padding: 20px; text-align: center;'>
                                <h2 style='margin:0;'>K2-Net</h2>
                                <p style='margin:5px 0 0;'>Tagihan Internet</p>
                            </div>
                            <div style='padding: 20px; background: #f9f9f9;'>
                                <p>Halo <strong>{$user->name}</strong>,</p>
                                <p>Berikut tagihan Anda:</p>
                                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                                    <thead>
                                        <tr style='background:#e5e5e5;'>
                                            <th style='padding:8px;border:1px solid #ddd;text-align:left;'>No. Tagihan</th>
                                            <th style='padding:8px;border:1px solid #ddd;text-align:left;'>Periode</th>
                                            <th style='padding:8px;border:1px solid #ddd;text-align:right;'>Jumlah</th>
                                            <th style='padding:8px;border:1px solid #ddd;text-align:left;'>Jatuh Tempo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style='padding:8px;border:1px solid #ddd;'>{$invoice->invoice_number}</td>
                                            <td style='padding:8px;border:1px solid #ddd;'>{$invoice->billing_period->format('F Y')}</td>
                                            <td style='padding:8px;border:1px solid #ddd;text-align:right;'>Rp {$invoice->formattedAmount()}</td>
                                            <td style='padding:8px;border:1px solid #ddd;'>{$invoice->due_date->format('d M Y')}</td>
                                        </tr>
                                    </tbody>
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
                    ");
            });
        } catch (\Throwable $e) {
            $this->warn("  [EMAIL FAILED] {$user->email} — {$e->getMessage()}");
        }
    }
}
