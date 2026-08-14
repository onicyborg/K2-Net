<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\PaymentProof;
use Illuminate\Console\Command;

class PaymentConfirmationCommand extends Command
{
    protected $signature = 'invoices:confirm {invoice_id}';

    protected $description = 'Kirim notifikasi konfirmasi pembayaran ke pelanggan';

    public function handle(): int
    {
        $invoice = Invoice::with('customer.user')->find($this->argument('invoice_id'));

        if (!$invoice) {
            $this->error('Invoice tidak ditemukan.');
            return Command::FAILURE;
        }

        $customer = $invoice->customer;
        $user = $customer->user;

        $this->info("Mengirim notifikasi konfirmasi ke {$invoice->invoice_number}...");

        NotificationLog::create([
            'invoice_id'         => $invoice->id,
            'customer_id'        => $customer->id,
            'notification_type'  => 'confirmation',
            'channel'           => 'whatsapp',
            'status'            => 'sent',
            'sent_at'           => now(),
            'meta'              => [
                'whatsapp_number' => $customer->whatsapp_number_full ?? $customer->whatsapp_number,
                'invoice_number'   => $invoice->invoice_number,
                'amount'           => $invoice->formattedAmount(),
                'billing_period'   => $invoice->billing_period->format('F Y'),
            ],
        ]);

        if ($user?->email) {
            NotificationLog::create([
                'invoice_id'         => $invoice->id,
                'customer_id'        => $customer->id,
                'notification_type'  => 'confirmation',
                'channel'           => 'email',
                'status'            => 'sent',
                'sent_at'           => now(),
                'meta'              => [
                    'email'         => $user->email,
                    'invoice_number'=> $invoice->invoice_number,
                    'amount'        => $invoice->formattedAmount(),
                    'billing_period'=> $invoice->billing_period->format('F Y'),
                ],
            ]);
        }

        $this->info('Notifikasi konfirmasi berhasil dikirim.');
        return Command::SUCCESS;
    }
}
