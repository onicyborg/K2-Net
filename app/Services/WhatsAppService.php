<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationLog;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * ============================================================
 * WhatsAppService — Wrapper HTTP ke Microservice WA Gateway
 * ============================================================
 *
 * Laravel TIDAK mengirim WhatsApp langsung. Tugas class ini:
 *   1. Terima nomor & pesan dari kode aplikasi (controller / command).
 *   2. Tembak POST /api/send-message ke microservice Node.js.
 *   3. Microservice yang menerjemahkan pesan ke Baileys.
 *   4. Catat hasilnya ke tabel notification_logs.
 *
 * Kenapa tidak langsung pakai Baileys dari Laravel?
 *   - Baileys adalah library Node.js, tidak bisa dijalankan di PHP.
 *   - Microservice terpisah = sesi WhatsApp persist walau Laravel
 *     restart, dan tidak membebani worker cPanel shared hosting.
 *
 * Setiap method publik otomatis menulis ke notification_logs
 * dengan status 'sent' / 'failed' sehingga bisa di-audit dari
 * halaman Notification Log yang sudah ada.
 */
class WhatsAppService
{
    /**
     * Base URL microservice (tanpa trailing slash).
     */
    protected string $baseUrl;

    /**
     * Shared secret untuk header X-Gateway-Token.
     */
    protected string $token;

    /**
     * Timeout HTTP call (detik).
     */
    protected int $timeout;

    /**
     * @param  string|null  $baseUrl  override URL (untuk testing)
     */
    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl  = rtrim($baseUrl ?? config('whatsapp.url'), '/');
        $this->token    = config('whatsapp.token', '');
        $this->timeout  = (int) config('whatsapp.timeout', 30);
    }

    /**
     * ============================================================
     * Feature Flag Check — apakah channel WhatsApp diaktifkan?
     * ============================================================
     *
     * Dipakai oleh semua method yang mengirim WA.
     * Override dari class test bisa dilakukan dengan constructor
     * $baseUrl=null + config()->set('whatsapp.enabled', true).
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) config('whatsapp.enabled', false);
    }

    /**
     * ============================================================
     * HTTP Client — pre-configured dengan token auth.
     * ============================================================
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'X-Gateway-Token' => $this->token,
                'Accept'          => 'application/json',
            ])
            ->acceptJson();
    }

    /**
     * ============================================================
     * Kirim pesan WhatsApp ke satu nomor.
     * ============================================================
     *
     * Method ini adalah "single send" — dipakai saat:
     *   - Invoice baru di-generate (realtime trigger).
     *   - Pembayaran dikonfirmasi / ditolak.
     *
     * @param  Customer        $customer
     * @param  string          $message        body pesan (plain text)
     * @param  NotificationType $type          jenis notifikasi (untuk log)
     * @param  Invoice|null    $invoice       invoice terkait (opsional, untuk log)
     * @return bool            true kalau berhasil terkirim
     */
    public function send(
        Customer $customer,
        string $message,
        NotificationType $type,
        ?Invoice $invoice = null,
    ): bool {
        // ============================================================
        // Feature Flag Check — skip kalau notifikasi WhatsApp dimatikan
        // ============================================================
        // Ini memungkinkan kita menonaktifkan seluruh channel WA
        // dengan satu env var tanpa harus hapus/komentar kode pemanggil.
        if (!$this->isEnabled()) {
            Log::info('[WhatsAppService] Notifikasi WhatsApp dinonaktifkan (WA_NOTIFICATIONS_ENABLED=false). Skip.', [
                'customer_id' => $customer->id,
                'invoice_id'  => $invoice?->id,
                'type'        => $type->value,
            ]);
            return false; // dianggap "tidak terkirim" tapi bukan error
        }

        $number = $customer->whatsapp_number_full
            ?: $customer->whatsapp_number;

        if (empty($number)) {
            $this->logFailure($customer, $invoice, $type, 'Nomor WhatsApp customer kosong.');
            return false;
        }

        if (empty($this->token) || empty($this->baseUrl)) {
            $this->logFailure(
                $customer,
                $invoice,
                $type,
                'WA_GATEWAY_URL / WA_GATEWAY_TOKEN belum di-set di .env',
            );
            return false;
        }

        try {
            $response = $this->client()->post(
                config('whatsapp.endpoints.send_message'),
                [
                    'number'  => $number,
                    'message' => $message,
                ],
            );

            if ($response->successful()) {
                $this->logSuccess($customer, $invoice, $type, $number, $message, $response->json());
                return true;
            }

            // Response 4xx/5xx dari gateway — anggap gagal.
            $error = $response->json('error') ?? $response->body();
            $this->logFailure($customer, $invoice, $type, "HTTP {$response->status()}: {$error}", $number);
            return false;
        } catch (ConnectionException $e) {
            // Microservice tidak bisa dihubungi (DNS, down, timeout).
            $this->logFailure($customer, $invoice, $type, 'Tidak dapat terhubung ke gateway: ' . $e->getMessage(), $number);
            return false;
        } catch (RequestException $e) {
            // Error lain dari HTTP client.
            $this->logFailure($customer, $invoice, $type, 'RequestException: ' . $e->getMessage(), $number);
            return false;
        } catch (\Throwable $e) {
            // Fallback — jangan sampai trigger WhatsApp error bikin
            // flow utama (generate invoice / kirim email) ikut gagal.
            $this->logFailure($customer, $invoice, $type, 'Unexpected: ' . $e->getMessage(), $number);
            Log::error('[WhatsAppService] Unexpected error', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * ============================================================
     * Health check — ping GET /health ke microservice.
     * ============================================================
     *
     * Bisa dipanggil dari controller diagnostik atau command
     * untuk memastikan microservice hidup & WhatsApp terhubung.
     *
     * @return array{alive: bool, status: ?string, error: ?string}
     */
    public function health(): array
    {
        try {
            $response = $this->client()->get(config('whatsapp.endpoints.health'));
            if ($response->successful()) {
                return [
                    'alive'  => true,
                    'status' => $response->json('wa_connection'),
                    'error'  => null,
                ];
            }
            return [
                'alive'  => false,
                'status' => null,
                'error'  => 'HTTP ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'alive'  => false,
                'status' => null,
                'error'  => $e->getMessage(),
            ];
        }
    }

    // ---------------------------------------------------------------
    // Helper: pencatatan ke notification_logs
    // ---------------------------------------------------------------
    protected function logSuccess(
        Customer $customer,
        ?Invoice $invoice,
        NotificationType $type,
        string $number,
        string $message,
        array $responseBody,
    ): void {
        NotificationLog::create([
            'invoice_id'        => $invoice?->id,
            'customer_id'       => $customer->id,
            'notification_type' => $type->value,
            'channel'           => NotificationChannel::WHATSAPP->value,
            'status'            => NotificationStatus::SENT->value,
            'sent_at'           => now(),
            'meta'              => [
                'whatsapp_number' => $number,
                'message_length'  => strlen($message),
                'gateway_response' => $responseBody,
            ],
        ]);
    }

    protected function logFailure(
        Customer $customer,
        ?Invoice $invoice,
        NotificationType $type,
        string $errorMessage,
        ?string $number = null,
    ): void {
        NotificationLog::create([
            'invoice_id'        => $invoice?->id,
            'customer_id'       => $customer->id,
            'notification_type' => $type->value,
            'channel'           => NotificationChannel::WHATSAPP->value,
            'status'            => NotificationStatus::FAILED->value,
            'failed_at'         => now(),
            'error_message'     => $errorMessage,
            'meta'              => [
                'whatsapp_number' => $number,
            ],
        ]);
    }
}
