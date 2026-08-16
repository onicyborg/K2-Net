<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway (Microservice Node.js)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi microservice WhatsApp Gateway yang berjalan terpisah di
    | Node.js (Baileys) dan di-deploy ke Render.com.
    |
    | URL lengkap microservice, contoh:
    |   https://k2-net-wa-gateway.onrender.com
    |
    | GATEWAY_TOKEN harus SAMA PERSIS dengan GATEWAY_TOKEN di .env
    | microservice (Node.js). Tanpa token cocok, request ditolak 401.
    |
    */

    'url' => env('WA_GATEWAY_URL', 'http://localhost:3000'),

    'token' => env('WA_GATEWAY_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Feature Flag — Enable / Disable WhatsApp Notifikasi
    |--------------------------------------------------------------------------
    |
    | Set `WA_NOTIFICATIONS_ENABLED=false` di .env untuk sementara mematikan
    | SEMUA pengiriman WhatsApp tanpa menghapus kode.
    |
    | Apa yang terjadi ketika false:
    |   • WhatsAppService::send() langsung return false (no-op)
    |   • Command billing:send-reminders skip channel WhatsApp
    |   • Listener InvoiceCreated tidak kirim WhatsApp
    |   • notification_logs TIDAK dibuat untuk channel WhatsApp
    |
    | Berguna untuk:
    |   • Development lokal tanpa microservice
    |   • Microservice belum siap / sedang maintenance
    |   • Testing sebelum Go Live
    |
    | Default: false (aman). Ubah ke true setelah microservice ready.
    */
    'enabled' => filter_var(env('WA_NOTIFICATIONS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    /*
    | Timeout HTTP call ke gateway (detik).
    | Gateway butuh waktu untuk handshake + kirim pesan, jadi jangan
    | terlalu kecil. 30 detik cukup aman.
    */
    'timeout' => (int) env('WA_GATEWAY_TIMEOUT', 30),

    /*
    | Delay antar pesan saat broadcast (millidetik).
    | Disarankan 5000-10000 ms untuk menghindari banned nomor.
    | 7000 ms = 7 detik.
    */
    'broadcast_delay_ms' => (int) env('WA_BROADCAST_DELAY_MS', 7000),

    /*
    | Path endpoint internal (relatif terhadap URL).
    | Dipakai oleh WhatsAppService saat menyusun request.
    */
    'endpoints' => [
        'send_message' => '/api/send-message',
        'health'       => '/health',
    ],
];
