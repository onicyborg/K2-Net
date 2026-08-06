# Setup K2-Net

## Prerequisites

```bash
cd /path/to/K2-Net
```

## 1. Install Dependencies

```bash
composer install
```

## 2. Install JWT Auth

```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

## 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```
APP_NAME="K2-Net"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=k2net
DB_USERNAME=root
DB_PASSWORD=

# Atau untuk PostgreSQL (dev):
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=k2net
# DB_USERNAME=postgres
# DB_PASSWORD=secret
```

## 4. Buat Database

```sql
CREATE DATABASE k2net CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 5. Jalankan Migration & Seeder

```bash
php artisan migrate
php artisan db:seed
```

## 6. Jalankan Server

```bash
php artisan serve
```

Buka browser: http://localhost:8000

## Login Admin

- **Email:** admin@k2net.local
- **Password:** admin123

## API Endpoint

### Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@k2net.local","password":"admin123"}'
```

Response:
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {...}
  }
}
```

### Get Me
```bash
curl http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer eyJ..."
```

### Refresh Token
```bash
curl -X POST http://localhost:8000/api/v1/auth/refresh \
  -H "Authorization: Bearer eyJ..."
```

### Logout
```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer eyJ..."
```

## Struktur Menu Admin

1. **Dashboard** — Statistik ringkasan sistem
2. **Pelanggan** — CRUD data pelanggan
3. **Paket Internet** — CRUD paket langganan
4. **Tagihan** — Kelola invoice pelanggan
5. **Verifikasi Pembayaran** — Konfirmasi bukti bayar
6. **Pelaporan** — Laporan pendapatan & pelanggan
7. **Log Notifikasi** — Riwayat notifikasi WhatsApp/Email
8. **Konfigurasi** — Pengaturan sistem
