<?php

namespace App\Listeners;

use App\Enums\NotificationType;
use App\Events\InvoiceCreated;
use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * SendInvoiceCreatedWhatsAppNotification
 * ============================================================
 *
 * Listener untuk event InvoiceCreated.
 *
 * Tanggung jawab:
 *   - Memformat pesan WhatsApp billing untuk customer.
 *   - Memanggil WhatsAppService::send().
 *
 * Best practice:
 *   - Implements ShouldQueue → listener diproses via queue worker
 *     sehingga response HTTP ke admin tidak terhambat.
 *   - Harus ada queue worker berjalan di server:
 *       php artisan queue:work --queue=default
 *   - Pastikan QUEUE_CONNECTION=database di .env (sesuai setup ada).
 *
 * Kalau queue worker tidak tersedia, listener tetap bisa jalan
 * synchronous dengan HAPUS implements ShouldQueue.
 * Tapi untuk cron via Render / shared hosting tanpa worker,
 * sebaiknya tetap synchronous (hapus ShouldQueue) agar langsung
 * terkirim — flow generate invoice hanya 1 customer pada satu waktu,
 * jadi tidak memberatkan HTTP request.
 */
class SendInvoiceCreatedWhatsAppNotification implements ShouldQueue
{
    /**
     * Nama queue. Opsional — bisa dikelompokkan di queue terpisah.
     */
    public string $queue = 'notifications';

    /**
     * Tunda listener 5 detik supaya transaction DB di controller
     * selesai commit dulu sebelum kita query ulang customer.
     */
    public int $delay = 5;

    /**
     * Berapa kali job di-retry jika gagal.
     */
    public int $tries = 3;

    /**
     * Timeout tiap attempt (detik).
     */
    public int $timeout = 60;

    public function handle(InvoiceCreated $event): void
    {
        /** @var Invoice $invoice */
        $invoice = $event->invoice;

        // Eager load customer + relasi yang dibutuhkan.
        $invoice->loadMissing('customer');

        $customer = $invoice->customer;
        if (!$customer) {
            Log::warning('[InvoiceCreated] Customer tidak ditemukan, skip WA', [
                'invoice_id' => $invoice->id,
            ]);
            return;
        }

        // ============================================================
        // Feature Flag — skip kalau channel WhatsApp dimatikan.
        // Listener ini cuma memproses channel WhatsApp; kalau off,
        // langsung return agar tidak ada HTTP call sia-sia.
        // ============================================================
        $wa = app(WhatsAppService::class);
        if (!$wa->isEnabled()) {
            Log::info('[InvoiceCreated] WhatsApp disabled, listener no-op', [
                'invoice_id'  => $invoice->id,
                'customer_id' => $customer->id,
            ]);
            return;
        }

        // Skip kalau customer tidak punya nomor WhatsApp.
        $number = $customer->whatsapp_number_full ?: $customer->whatsapp_number;
        if (empty($number)) {
            Log::info('[InvoiceCreated] Customer tanpa nomor WA, skip', [
                'customer_id' => $customer->id,
            ]);
            return;
        }

        $message = $this->buildMessage($invoice);

        $ok = $wa->send(
            $customer,
            $message,
            NotificationType::CONFIRMATION, // atau buat tipe baru 'invoice_created'
            $invoice,
        );

        if (!$ok) {
            // Biarkan queue worker retry sesuai $tries.
            // Throwing exception akan trigger retry otomatis.
            throw new \RuntimeException(
                "Gagal kirim WhatsApp untuk invoice {$invoice->invoice_number}"
            );
        }
    }

    /**
     * Template pesan WhatsApp saat invoice baru di-generate.
     */
    protected function buildMessage(Invoice $invoice): string
    {
        $customer = $invoice->customer;
        $portalUrl = $customer->getPortalUrl();
        $period = $invoice->billing_period->format('F Y');
        $dueDate = $invoice->due_date->format('d M Y');
        $amount = $invoice->formattedAmount();

        return
            "Halo *{$customer->name}*,\n\n" .
            "Tagihan internet K2-Net Anda untuk periode *{$period}* telah terbit.\n\n" .
            "📄 No. Tagihan : {$invoice->invoice_number}\n" .
            "💰 Jumlah      : {$amount}\n" .
            "📅 Jatuh Tempo : {$dueDate}\n\n" .
            "Silakan lakukan pembayaran melalui portal berikut:\n{$portalUrl}\n\n" .
            "Terima kasih 🙏\n— Tim K2-Net";
    }

    /**
     * Dipanggil ketika semua retry gagal.
     * Catat ke log supaya bisa dicek manually.
     */
    public function failed(InvoiceCreated $event, \Throwable $exception): void
    {
        Log::error('[InvoiceCreated] Listener WhatsApp gagal total', [
            'invoice_id' => $event->invoice->id,
            'error'      => $exception->getMessage(),
        ]);
    }
}
