<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\NotificationLog;
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
                $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <div style='background: #16a34a; color: white; padding: 20px; text-align: center;'>
                                <h2 style='margin:0;'>K2-Net</h2>
                                <p style='margin:5px 0 0;'>Pembayaran Dikonfirmasi</p>
                            </div>
                            <div style='padding: 20px; background: #f9f9f9;'>
                                <p>Halo <strong>{$user->name}</strong>,</p>
                                <p>Pembayaran Anda telah <strong>dikonfirmasi</strong>. Terima kasih atas pembayarannya.</p>
                                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                                    <tr>
                                        <td style='padding:8px; border:1px solid #ddd;'>No. Tagihan</td>
                                        <td style='padding:8px; border:1px solid #ddd; font-weight:bold;'>{$invoice->invoice_number}</td>
                                    </tr>
                                    <tr style='background:#f0f0f0;'>
                                        <td style='padding:8px; border:1px solid #ddd;'>Periode</td>
                                        <td style='padding:8px; border:1px solid #ddd;'>{$invoice->billing_period->format('F Y')}</td>
                                    </tr>
                                    <tr>
                                        <td style='padding:8px; border:1px solid #ddd;'>Jumlah</td>
                                        <td style='padding:8px; border:1px solid #ddd; font-weight:bold; color:#16a34a;'>Rp {$invoice->formattedAmount()}</td>
                                    </tr>
                                    <tr style='background:#f0f0f0;'>
                                        <td style='padding:8px; border:1px solid #ddd;'>Lunas Pada</td>
                                        <td style='padding:8px; border:1px solid #ddd;'>{$invoice->paid_at?->format('d M Y') ?? now()->format('d M Y')}</td>
                                    </tr>
                                </table>
                                <p>Jika ada pertanyaan, silakan hubungi kami.</p>
                            </div>
                            <div style='padding: 15px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee;'>
                                K2-Net — Sistem Manajemen Tagihan & Pelanggan
                            </div>
                        </div>
                    ");
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
