<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Notifications\EmailTemplates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $portalUrl = $customer->getPortalUrl();

        $this->info("Mengirim notifikasi konfirmasi ke {$invoice->invoice_number}...");

        // WhatsApp — log only
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

        // Email — actually send
        if ($user?->email) {
            $this->sendEmail($invoice, $user, $portalUrl);
        }

        $this->info('Notifikasi konfirmasi berhasil dikirim.');
        return Command::SUCCESS;
    }

    private function sendEmail(Invoice $invoice, $user, string $portalUrl): void
    {
        $subject = "Pembayaran {$invoice->invoice_number} Dikonfirmasi — K2-Net";

        try {
            Mail::send([], [], function ($message) use ($user, $invoice, $portalUrl, $subject) {
                $logoCid = EmailTemplates::attachLogo($message);

                $content = EmailTemplates::greeting($user->name);
                $content .= EmailTemplates::paragraph('Pembayaran Anda telah <strong>dikonfirmasi</strong>. Terima kasih atas pembayarannya. Layanan internet Anda tetap aktif.');
                $content .= EmailTemplates::paymentDetailTable([
                    ['label' => 'No. Tagihan', 'value' => $invoice->invoice_number],
                    ['label' => 'Periode', 'value' => $invoice->billing_period->format('F Y')],
                    ['label' => 'Jumlah', 'value' => 'Rp ' . $invoice->formattedAmount()],
                    ['label' => 'Lunas Pada', 'value' => $invoice->paid_at?->format('d M Y') ?? now()->format('d M Y')],
                ]);
                $content .= EmailTemplates::alert('Semua tagihan lunas. Terima kasih!', 'success', '#16a34a');

                $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->html(EmailTemplates::wrapper(
                        $content,
                        'Pembayaran Dikonfirmasi',
                        'K2-Net — Sistem Manajemen Tagihan & Pelanggan',
                        '#16a34a',
                        $logoCid
                    ));
            });

            NotificationLog::create([
                'invoice_id'         => $invoice->id,
                'customer_id'        => $invoice->customer->id,
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
        } catch (\Throwable $e) {
            $this->warn("  [EMAIL FAILED] {$user->email} — {$e->getMessage()}");
            Log::error('[PaymentConfirmation] Gagal kirim email', [
                'invoice_id' => $invoice->id,
                'email'     => $user->email,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
