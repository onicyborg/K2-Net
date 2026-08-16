# Setup K2-Net

## Prerequisites

```bash
php -v
# PHP 8.2+
composer --version
node --version
```

## Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

## Cron Job — External via cron-job.org

Project ini menggunakan **cron-job.org** (layanan gratis) untuk menjalankan scheduled jobs pada waktu yang tepat, **tanpa** perlu cron job per-menit di server.

### Cara kerja

1. `cron-job.org` hit URL endpoint Laravel pada waktu yang dijadwalkan.
2. Endpoint memvalidasi token, lalu panggil Artisan command yang relevan.
3. Laravel jalankan command dan kirim email/WhatsApp seperti biasa.

### Setup satu kali

**1. Generate token**

```bash
openssl rand -hex 32
```

Copy hasilnya, masukkan ke `.env` di server:

```
CRON_TOKEN=<hasil-openssl>
```

Deploy / restart Laravel supaya `.env` terbaca.

**2. Daftar di cron-job.org**

Buat akun gratis di https://cron-job.org, lalu buat **4 cron job** berikut:

| # | URL | Schedule | Method |
|---|---|---|---|
| 1 | `https://yourdomain.com/cron/invoices-remind?token=XXX` | `0 8 * * *` (daily 08:00) | GET |
| 2 | `https://yourdomain.com/cron/invoices-auto-generate?token=XXX` | `5 8 28 * *` (tanggal 28 jam 08:05) | GET |
| 3 | `https://yourdomain.com/cron/billing-reminder-active?token=XXX` | `0 8 1 * *` (tanggal 1 jam 08:00) | GET |
| 4 | `https://yourdomain.com/cron/billing-reminder-due?token=XXX` | `0 8 15 * *` (tanggal 15 jam 08:00) | GET |

Ganti `XXX` dengan token dari `.env`.

**3. Testing manual**

Buka URL di browser (atau pakai curl):

```bash
curl "https://yourdomain.com/cron/invoices-remind?token=XXX"
```

Response JSON:
```json
{
  "status": "ok",
  "command": "invoices:remind",
  "exit_code": 0,
  "output": "...",
  "ran_at": "2026-08-17 08:00:00"
}
```

Token salah → `403 Forbidden`.

### Daftar endpoint

Semua endpoint ada di `routes/web.php` dengan prefix `/cron`:

| Endpoint | Command | Kapan dijalankan |
|---|---|---|
| `/cron/invoices-remind` | `invoices:remind` | Daily 08:00 — email reminder H-3 & H+3 |
| `/cron/invoices-auto-generate` | `invoices:auto-generate` | Tanggal 28 jam 08:05 — generate invoice bulan depan + email |
| `/cron/billing-reminder-active` | `billing:send-reminders --type=active` | Tanggal 1 jam 08:00 — WhatsApp ke semua customer aktif |
| `/cron/billing-reminder-due` | `billing:send-reminders --type=due` | Tanggal 15 jam 08:00 — WhatsApp reminder jatuh tempo |

### Tidak perlu lagi

- `* * * * * cd /path && php artisan schedule:run` — hapus cron OS ini
- `routes/console.php` Schedule:: sudah dihapus, tidak aktif

### Monitoring

- Login ke cron-job.org → lihat history eksekusi (success/fail, response time)
- Cek Laravel log: `storage/logs/laravel.log` untuk error command
- Cek `notification_logs` table untuk status kirim email/WhatsApp