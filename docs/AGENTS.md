# AGENTS.md — K2-Net Project Reference

> **Versi:** 0.1 &nbsp;|&nbsp; **Terakhir diubah:** 6 Agustus 2026 &nbsp;|&nbsp; **Source of truth:** `PRD-K2-Net.md`

| Versi | Tanggal | Perubahan |
|--------|---------|-----------|
| 0.1 | 6 Agt 2026 | Inisial — struktur direktori, Laravel best practices, API-first architecture, UUID v7, Metronic CDN pinning, eager loading N+1, RBAC dual-role |
| 0.2 | 6 Agt 2026 | JWT authentication via tymon/jwt-auth, refresh token, blacklist, role-based middleware |

> Dokumen ini adalah living document acuan development. Sebelum mulai fitur baru, baca ulang dokumen ini.
> Detail requirement lengkap ada di `PRD-K2-Net.md`. Dokumen ini merangkum keputusan yang sudah fix untuk
> memastikan konsistensi development — bukan duplikasi PRD.

---

## 1. Ringkasan Project

**K2-Net** adalah sistem manajemen tagihan dan pelanggan Mini ISP (RT/RW Net) yang mengotomatisasi proses penagihan bulanan, verifikasi pembayaran, notifikasi jatuh tempo, dan pelaporan keuangan. Ada 2 role pengguna: **Admin** (K2-Net owner/operator) dan **Pelanggan** (pengguna layanan internet).

**Tech stack:**
- Backend: **Laravel 12** (API-only, JSON response)
- Frontend: **Blade Template + Metronic v8.2.9** (CDN asset, di-pin ke URL/version spesifik)
- Database: **MySQL** (production via cPanel) / **PostgreSQL** (local/dev via Neon)
- Primary ID: **UUID v7** (sortable, time-ordered, K2 prefix per tabel)
- Authentication: **JWT** via `tymon/jwt-auth` (untuk API), session-based (untuk Blade)
- Deployment target: cPanel shared/VPS hosting dengan PHP 8.2

**Arsitektur:** Backend murni **API-only** agar bisa diintegrasikan dengan klien non-web (mobile app, integrasi pihak ketiga). Frontend Blade berfungsi sebagai "default client" yang dikonsumsi admin dan pelanggan. Route API prefix: `/api/v1/`.

---

## 2. Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/               # API controllers (JSON response only)
│   │   │   ├── AuthController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── PackageController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── NotificationLogController.php
│   │   │   ├── ReportController.php
│   │   │   └── ConfigurationController.php
│   │   ├── Web/                  # Blade page controllers (one folder, flat)
│   │   │   ├── DashboardController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── PackageController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── PaymentVerificationController.php
│   │   │   ├── ReportController.php
│   │   │   ├── NotificationLogController.php
│   │   │   ├── ConfigurationController.php
│   │   │   └── AuthController.php
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php       # RBAC: admin vs pelanggan (API + Web)
│   │       └── JwtMiddleware.php       # JWT auth guard verification (API)
│   ├── Requests/             # Form Request untuk validasi input (Blade)
│   │   ├── StoreCustomerRequest.php
│   │   ├── UpdateCustomerRequest.php
│   │   ├── StorePackageRequest.php
│   │   ├── UpdatePackageRequest.php
│   │   ├── UploadPaymentProofRequest.php
│   │   └── ApproveRejectPaymentRequest.php
│   └── Resources/            # API Resource (transform JSON response)
│       ├── CustomerResource.php
│       ├── InvoiceResource.php
│       ├── PackageResource.php
│       └── PaymentProofResource.php
├── Models/
│   ├── User.php              # Multi-role: admin + pelanggan, implements JWTSubject
│   ├── PersonalAccessToken.php # Sanctum tokens (mobile app)
│   ├── Customer.php          # Data pelanggan internet
│   ├── Package.php           # Master paket internet
│   ├── Invoice.php           # Invoice bulanan
│   ├── PaymentProof.php      # Bukti transfer
│   ├── NotificationLog.php   # Log pengiriman notifikasi
│   ├── AuditLog.php          # Audit trail (approve/reject, status change)
│   ├── SystemConfiguration.php # Konfigurasi (H-3/H+3, tanggal generate, dll.)
│   └── JwtBlacklist.php      # JWT blacklist (untuk logout/invalidate)
│   └── ...
├── Services/                  # Business logic kompleks di sini, BUKAN di Controller
│   ├── InvoiceGenerationService.php   # Cron: generate invoice bulanan
│   ├── NotificationService.php        # Kirim WA/Email notifikasi
│   ├── PaymentVerificationService.php # Approve/reject + state machine
│   ├── ExportService.php             # Export Excel/CSV
│   ├── UuidV7Service.php             # Generate UUID v7 dengan prefix
│   └── ...
├── Enums/
│   ├── InvoiceStatus.php      # BELUM_BAYAR, MENUNGGU_VERIFIKASI, LUNAS, DITOLAK
│   ├── CustomerStatus.php     # AKTIF, ISOLIR, NONAKTIF
│   ├── NotificationType.php   # REMINDER_H3, REMINDER_H0, REMINDER_H3_LATE, CONFIRMATION, REJECTION
│   ├── NotificationChannel.php # WHATSAPP, EMAIL
│   ├── NotificationStatus.php # PENDING, SENT, FAILED
│   └── UserRole.php           # ADMIN, PELANGGAN
├── Observers/                 # Eloquent Observer (state machine, audit trail)
│   ├── InvoiceObserver.php
│   └── PaymentProofObserver.php
└── Console/
    ├── Commands/
    │   ├── GenerateMonthlyInvoices.php  # php artisan invoices:generate
    │   ├── SendNotifications.php         # php artisan notifications:send
    │   └── CleanupOldLogs.php            # php artisan logs:cleanup
    └── Kernel.php                        # Scheduler (cron)

database/
├── migrations/
├── seeders/
│   ├── PackageSeeder.php      # Seed paket internet default
│   ├── AdminUserSeeder.php    # Seed admin default
│   └── SystemConfigurationSeeder.php
└── factories/

routes/
├── api.php                    # API routes v1 (prefix: /api/v1)
├── web.php                    # Blade routes
├── auth.php                   # Auth routes
└── console.php                # Artisan scheduler

resources/views/
├── layouts/
│   ├── app.blade.php          # Layout utama (sidebar + header)
│   ├── auth.blade.php         # Layout login admin
│   ├── customer.blade.php     # Layout portal pelanggan (tanpa sidebar kompleks)
│   └── partials/
│       ├── _header.blade.php
│       ├── _sidebar.blade.php  # Sidebar menu Admin
│       ├── _customer_header.blade.php
│       └── _footer.blade.php
├── components/
│   ├── alert.blade.php
│   ├── datatable.blade.php
│   ├── invoice-status-badge.blade.php
│   ├── customer-status-badge.blade.php
│   └── notification-badge.blade.php
└── pages/                     # Halaman Blade per fitur (flat, tidak nested)
    ├── dashboard/
    │   └── index.blade.php    # Dashboard Admin ringkasan
    ├── customers/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    ├── packages/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   └── edit.blade.php
    ├── invoices/
    │   ├── index.blade.php
    │   └── detail.blade.php
    ├── verifications/
    │   └── index.blade.php    # Dashboard verifikasi pembayaran
    ├── reports/
    │   └── index.blade.php    # Export laporan
    ├── notifications/
    │   └── index.blade.php    # Log notifikasi
    ├── configurations/
    │   └── index.blade.php    # Konfigurasi sistem
    └── customer-portal/        # Portal Pelanggan (self-service)
        ├── login.blade.php
        ├── dashboard.blade.php # Tagihan bulan berjalan
        ├── payment-upload.blade.php
        ├── history.blade.php  # Riwayat pembayaran
        └── invoice-detail.blade.php
```

### Aturan Penempatan Logic

| Jenis Logic | Letakkan di |
|---|---|
| HTTP handling, request routing, JSON response | API Controller |
| Blade page rendering, view logic | Web Controller |
| Validasi input form | Form Request class |
| Validasi input API | API Request / Form Request (reuse) |
| Business logic (generate invoice, notification, approve/reject) | Service class |
| State machine (invoice status transition) | InvoiceStatus enum + Observer |
| ID generation | `UuidV7Service` |
| Eloquent relationship / scope | Model (scopes, accessors, mutators) |
| Audit trail | Observer atau Middleware |
| Konfigurasi dinamis | `SystemConfiguration` model |

**Controller HARUS tipis.** Jika method controller > ~10 baris yang bukan routing/request-handling, pindahkan ke Service class.

---

## 3. Laravel 12 Best Practices

### 3.1 API-First Architecture

- Semua business logic berjalan di **Service class**
- API Controllers hanya: parse request → panggil Service → return JSON
- Web Controllers: parse request → panggil Service yang sama → render Blade view
- **TIDAK ada duplicate logic** antara API dan Web controller — keduanya pakai Service yang sama
- API Response format:

```php
// Success
return response()->json([
    'success' => true,
    'message' => 'Data retrieved successfully',
    'data' => $data,
], 200);

// Error
return response()->json([
    'success' => false,
    'message' => 'Validation failed',
    'errors' => $errors,
], 422);

// Pagination
return response()->json([
    'success' => true,
    'data' => $data,
    'meta' => [
        'current_page' => $paginator->currentPage(),
        'last_page' => $paginator->lastPage(),
        'per_page' => $paginator->perPage(),
        'total' => $paginator->total(),
    ],
], 200);
```

### 3.2 Eloquent

- **Relationships:** Gunakan Eloquent relationships (`belongsTo`, `hasMany`, `belongsToMany`)
- **Scopes:** Gunakan Eloquent scopes untuk query yang sering berulang
- **Accessors/Mutators:** Gunakan untuk format data (format mata uang, tanggal, dll.)
- **UUID v7:** Gunakan `UuidV7Service` untuk generate semua primary key. Format: `K2-{prefix}-{uuid7}`
  - Contoh: `K2-CUS-01HQ...`, `K2-INV-01HQ...`, `K2-PAY-01HQ...`
- **No raw query untuk CRUD biasa**

### 3.3 Form Request

- **Semua validasi input HARUS lewat Form Request class**, bukan inline di controller
- Naming convention: `StoreModelRequest`, `UpdateModelRequest`

### 3.4 Service / Action Class

- Logic bisnis yang kompleks (generate invoice, notification, payment verification) HARUS di Service class
- Inject via constructor atau method langsung
- Bind interface → implementasi di `AppServiceProvider` jika perlu mock di test

### 3.5 Migration & Seeding

- Migration naming: `YYYY_MM_DD_HHMMSS_create_tablename_table.php`
- Kolom `created_at`, `updated_at` selalu ada
- **Database-agnostic:** Migration HARUS pakai Schema Builder Laravel (`$table->string()`, `$table->foreignId()`, dst)
- **Enum di level aplikasi:** Status (invoice, customer, notification) disimpan sebagai `string` di database, dipetakan ke PHP enum
- Soft delete: pakai trait `SoftDeletes`, Migration pakai `$table->softDeletes()`
- Seeder untuk data master HARUS idempotent

### 3.5b UUID v7

- **Format:** `K2-{prefix}-{uuid7}`
- Prefix per tabel: `CUS` (Customer), `PKG` (Package), `INV` (Invoice), `PAY` (PaymentProof), `LOG` (NotificationLog), `AUD` (AuditLog), `USR` (User), `CFG` (SystemConfiguration)
- Service: `UuidV7Service` menyediakan method `generate(string $prefix): string`
- UUID v7 dipilih karena: sortable (timestamp-ordered), tidak exposed sequential ID, compatible dengan UUID standard

### 3.6 JWT Authentication (tymon/jwt-auth)

Gunakan package `tymon/jwt-auth` untuk autentikasi API. Model `User` implements `JWTSubject`.

**Install & Setup:**
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

**Konfigurasi `config/auth.php`:**
```php
'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

**Model User — implements JWTSubject:**
```php
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'name' => $this->name,
        ];
    }
}
```

**Token TTL & Refresh:**
- Access token TTL: **60 menit** (configurable via `config/jwt.php`)
- Refresh token TTL: **2 minggu**
- Saat login, return both `access_token` dan `refresh_token`
- Logout: blacklist token via `JWTAuth::invalidate(JWTAuth::getToken())`

**Middleware JWT:**
```php
// routes/api.php
Route::middleware('auth:api')->group(function () {
    // Semua route yang butuh auth
});
```

### 3.7 RBAC — Dual Role

Dua role: **ADMIN** dan **PELANGGAN**. Dipisah lewat kolom `role` di tabel `users`.

| Aspek | ADMIN | PELANGGAN |
|-------|-------|-----------|
| Akses | Dashboard, Master Data, Invoice, Verifikasi, Laporan, Konfigurasi | Portal Sendiri (tagihan & histori) |
| Auth (API) | JWT guard `auth:api` + role check | JWT guard `auth:api` + role check |
| Auth (Web) | Session-based (`auth:web`) | Session-based (`auth:web`) |
| Middleware | `role:admin` | `role:pelanggan` |
| API prefix | `/api/v1/admin/*` | `/api/v1/customer/*` |
| Web route prefix | `/admin/*` | `/portal/*` |

- Pelanggan **TIDAK bisa** mengakses data pelanggan lain — semua query di-scoped ke `customer_id` user yang login
- Admin **TIDAK bisa** login ke portal pelanggan

### 3.8 Blade Component

- Gunakan Blade component (`<x-alert>`, `<x-invoice-status-badge>`) untuk elemen UI yang dipakai >1 tempat
- Component class di `app/View/Components/`
- Komponen Metronic yang kompleks (DataTable, modal) dibungkus jadi component class

### 3.9 Error Handling & Logging

- Gunakan `Log::info()`, `Log::warning()`, `Log::error()` secara konsisten
- Audit log: catat ke tabel `audit_logs` untuk setiap perubahan kritikal (approve/reject, status change, customer update)
- Format: `{actor} {action} {entity} {id}: {from} → {to} at {timestamp}`
- Exception yang tidak terduga: lempar `throw new \RuntimeException(...)`, biarkan Laravel yang tangani

### 3.10 Route & AJAX Convention

- Route naming: `route('admin.customers.index')`, `route('portal.dashboard')`
- Untuk interaksi AJAX dari Blade page, gunakan route reguler yang mengembalikan JSON
- API route naming: `route('api.v1.customers.index')` (kebab-case, prefix dengan `api.v1.`)
- Selalu kirim CSRF token di header AJAX

---

## 4. Konvensi Database

### 4.0 Database Portability — ATURAN KRUSIAL

> Sama seperti BrewShift: production = MySQL, local/dev = PostgreSQL.
> Kode aplikasi HARUS berjalan identik di kedua engine tanpa perubahan.

| # | Aturan | Detail |
|---|---|---|
| 1 | **Schema Builder only** | Semua migration pakai `$table->string()`, `$table->foreignId()`, dst — **TIDAK BOLEH** raw SQL DDL |
| 2 | **Enum di level aplikasi** | Status disimpan sebagai `string` di database, dipetakan ke PHP enum |
| 3 | **Query Builder / Eloquent only** | **TIDAK BOLEH** raw SQL spesifik satu engine |
| 4 | **Config dual connection** | `config/database.php` harus punya koneksi `pgsql` (local) dan `mysql` (production) |

### 4.1 Naming

| Jenis | Konvensi | Contoh |
|---|---|---|
| Tabel | snake_case, jamak | `customers`, `invoices`, `payment_proofs` |
| Kolom | snake_case | `customer_id`, `invoice_number` |
| Foreign key | `{tabel_singular}_id` | `package_id`, `user_id` |
| Primary Key | UUID v7 string | `K2-CUS-01HQ...` |
| Enum constraint | snake_case_uppercase | `invoice_statuses`, `customer_statuses` |

### 4.2 Invoice Status — State Machine

```
BELUM_BAYAR ────────────────────► MENUNGGU_VERIFIKASI
    ▲                                   │
    │                                   ▼
    │                              LUNAS (approve)
    │                                   │
    └───────────────── DITOLAK ◄────────┘ (reject)
```

- **Transisi valid:**
  - `BELUM_BAYAR` → `MENUNGGU_VERIFIKASI` (saat pelanggan upload bukti)
  - `MENUNGGU_VERIFIKASI` → `LUNAS` (saat admin approve)
  - `MENUNGGU_VERIFIKASI` → `BELUM_BAYAR` (saat admin reject)
- **Implementasi:** `InvoiceStatus` enum dengan method `canTransitionTo()`, dipanggil di `InvoiceObserver` atau `PaymentVerificationService`
- Aksi approve/reject TIDAK boleh dilakukan pada invoice yang statusnya bukan `MENUNGGU_VERIFIKASI`

### 4.3 Soft Delete

- `customers` — soft delete (tidak boleh hard delete jika ada riwayat invoice)
- `packages` — soft delete (tidak boleh hard delete jika ada pelanggan aktif)
- `invoices` — TIDAK soft delete (audit trail finansial)
- `payment_proofs` — TIDAK soft delete (bukti transaksi)
- Audit log: `audit_logs` dengan `created_at` saja (immutable)

---

## 5. Aturan Spesifik Bisnis

> Detail lengkap ada di `PRD-K2-Net.md`. Bagian ini adalah ringkasan reference cepat.

### 5.1 Invoice Generation (Cron Job)

- Dijalankan otomatis via `GenerateMonthlyInvoices` command, dijadwalkan via Laravel Scheduler
- Tanggal eksekusi configurable via `SystemConfiguration` (default: tanggal 25 setiap bulan)
- Mengambil semua pelanggan berstatus `AKTIF`
- Membuat 1 invoice per pelanggan per periode
- Invoice number format: `INV-{YYYYMM}-{customer_code}-{sequence}` (contoh: `INV-202608-CUS001-001`)
- Tidak membuat duplikat: cek `WHERE customer_id = ? AND billing_period = ?` sebelum insert
- Idempotent: aman dijalankan berulang tanpa duplikasi

### 5.2 Notification Scheduler

- `SendNotifications` command dijalankan harian via Scheduler (misal: setiap jam 08:00)
- Cek semua invoice dengan status `BELUM_BAYAR`, hitung selisih hari dari `due_date`
- Tipe notifikasi: `REMINDER_H3` (H-3), `REMINDER_H0` (hari-H), `REMINDER_H3_LATE` (H+3)
- Tidak mengirim notifikasi duplikat: cek `notification_logs` untuk kombinasi `invoice_id + notification_type + DATE(sent_at)`
- TIDAK mengirim notifikasi untuk invoice berstatus `LUNAS` atau `MENUNGGU_VERIFIKASI`

### 5.3 Payment Verification

- Pelanggan upload bukti → status berubah ke `MENUNGGU_VERIFIKASI`
- Admin approve → status `LUNAS`, tanggal pelunasan tercatat, kirim notifikasi konfirmasi
- Admin reject → status kembali `BELUM_BAYAR`, alasan tersimpan, pelanggan bisa upload ulang
- File bukti: simpan di `storage/app/payment-proofs/`, nama file pakai UUID v7

### 5.4 Audit Trail

Setiap perubahan kritikal HARUS tercatat:
- Approve/reject pembayaran: `{actor} {approve/reject} payment_proof {id} for invoice {invoice_id} at {timestamp}`
- Status invoice change: `{actor} changed invoice {id} status from {old} to {new} at {timestamp}`
- Customer create/update/deactivate: `{actor} {created/updated/deactivated} customer {id} at {timestamp}`

### 5.5 System Configuration

Konfigurasi yang harus bisa diedit admin (tanpa ubah kode):

| Key | Default | Deskripsi |
|-----|---------|-----------|
| `invoice_generate_day` | 25 | Tanggal generate invoice bulanan |
| `invoice_due_day` | 5 | Tanggal jatuh tempo (bulan berikutnya) |
| `notification_reminder_days` | `[-3, 0, 3]` | Hari pengingat (H-3, H0, H+3) |
| `upload_max_size_kb` | 5120 | Maks ukuran upload bukti (5MB) |
| `upload_allowed_types` | `['pdf','jpg','jpeg','png']` | Tipe file yang diizinkan |
| `whatsapp_api_url` | `''` | URL API WhatsApp gateway |
| `email_from_address` | `''` | Alamat email pengirim |
| `email_from_name` | `'K2-Net'` | Nama pengirim email |

---

## 6. Kebijakan Deviasi / Pengecualian Aturan

Dokumen ini adalah living document. Jika suatu fitur perlu melanggar aturan yang tertulis di atas,
**WAJIB** didokumentasikan di bawah ini — jangan diam-diam menyimpang.

### Catatan Pengecualian

<!-- Mulai tulis pengecualian di sini. Format: -->
<!-- | # | Aturan yang Dilanggar | Alasan | File/Fitur | Tanggal | -->

| # | Aturan yang Dilanggar | Alasan | File/Fitur | Tanggal |
|---|---|---|---|---|
| — | — | — | — | — |

---

## 7. Testing

### 7.1 Pendekatan

- Framework: **Pest** (Laravel default)
- Lokasi: `tests/Unit/` dan `tests/Feature/`

### 7.2 Unit Test — WAJIB Ada

**`InvoiceGenerationService`:**
- `test_generate_creates_invoice_for_all_active_customers()`
- `test_generate_skips_existing_invoice_for_same_period()`
- `test_generate_skips_inactive_customers()`
- `test_generate_idempotent_when_run_multiple_times()`

**`PaymentVerificationService`:**
- `test_approve_changes_status_to_lunas_and_records_paid_date()`
- `test_reject_returns_status_to_belum_bayar_with_reason()`
- `test_cannot_approve_already_lunas_invoice()`
- `test_cannot_reject_already_lunas_invoice()`

**`NotificationService`:**
- `test_send_h3_reminder_for_due_date_minus_3_days()`
- `test_do_not_send_reminder_for_paid_invoice()`
- `test_do_not_send_duplicate_notification_same_day()`
- `test_send_confirmation_on_approve()`
- `test_send_rejection_with_reason_on_reject()`

**`UuidV7Service`:**
- `test_uuidv7_format_matches_pattern()`
- `test_uuidv7_is_sortable_by_timestamp()`

### 7.3 Feature Test

Minimal:
- CRUD customer (admin)
- CRUD package (admin)
- Invoice generation
- Payment upload (pelanggan) → status change
- Admin approve/reject
- Customer portal access (data isolation)
- Export laporan

### 7.4 Mock Policy

- **Jangan mock database** di feature test — gunakan `SqliteInMemoryDatabase` trait
- Unit test boleh mock Service dependency jika scope test jelas
- API test pakai HTTP test helpers (`getJson`, `postJson`, etc.)

---

## 8. Code Style & Static Analysis

### 8.1 Laravel Pint (Formatting Otomatis)

- Jalankan `vendor/bin/pint` sebelum setiap commit
- Konfigurasi `pint.json` di root project

### 8.2 PHPStan / Larastan (Static Analysis)

- Jalankan `vendor/bin/phpstan analyse` minimal **level 5**
- Target sebelum merge: **0 errors**

### 8.3 Eager Loading — N+1 Prevention

- **WAJIB** eager-load relasi dengan `with()` di semua query yang hasilnya di-render di view
- Contoh: `Invoice::with(['customer', 'customer.package'])->where(...)`
- **DILARANG lazy load** di dalam loop/view

### 8.4 Metronic CDN Asset — Pinned URL

- Semua path asset Metronic HARUS menggunakan URL CDN yang **di-pin ke versi spesifik**
- Lihat `docs/DESIGN.md` Section 1 untuk path asset lengkap
- Selalu refer ke `design.md` untuk path asset yang sudah di-pin

---

## 9. API Specification (v1)

### 9.1 Base URL

```
Production: https://k2net.example.com/api/v1
Local:      http://k2net.test/api/v1
```

### 9.2 Authentication

Semua endpoint API (kecuali auth) membutuhkan **JWT Bearer token**.

**Header:**
```
Authorization: Bearer {jwt_token}
```

**Response format error 401 (token invalid/expired):**
```json
{
  "success": false,
  "message": "Token is invalid or expired",
  "error_code": "TOKEN_INVALID"
}
```

**Response format error 403 (role not allowed):**
```json
{
  "success": false,
  "message": "You do not have permission to access this resource",
  "error_code": "FORBIDDEN"
}
```

### 9.3 Auth Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/auth/login` | Login admin/pelanggan → return access & refresh token |
| POST | `/auth/refresh` | Refresh access token |
| POST | `/auth/logout` | Logout (blacklist token) |
| GET | `/auth/me` | Current authenticated user info |

**Login Request:**
```json
POST /api/v1/auth/login
{
  "email": "admin@k2net.local",
  "password": "secret"
}
```

**Login Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ...",
    "token_type": "bearer",
    "expires_in": 3600,
    "refresh_token": "def50200...",
    "user": {
      "id": "K2-USR-01HQ...",
      "name": "Kang Dedi",
      "email": "admin@k2net.local",
      "role": "admin"
    }
  }
}
```

**Refresh Request:**
```json
POST /api/v1/auth/refresh
Headers: Authorization: Bearer {refresh_token}
```

**Logout Request:**
```json
POST /api/v1/auth/logout
Headers: Authorization: Bearer {access_token}
```

### 9.4 Admin Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/admin/customers` | Daftar pelanggan (paginated) |
| POST | `/admin/customers` | Tambah pelanggan |
| GET | `/admin/customers/{id}` | Detail pelanggan |
| PUT | `/admin/customers/{id}` | Edit pelanggan |
| PATCH | `/admin/customers/{id}/deactivate` | Deactivate (soft delete) |
| GET | `/admin/packages` | Daftar paket |
| POST | `/admin/packages` | Tambah paket |
| PUT | `/admin/packages/{id}` | Edit paket |
| GET | `/admin/invoices` | Daftar invoice (paginated, filterable) |
| GET | `/admin/invoices/{id}` | Detail invoice |
| POST | `/admin/invoices/generate` | Trigger generate invoice |
| GET | `/admin/verifications` | Daftar bukti pembayaran pending |
| POST | `/admin/verifications/{id}/approve` | Approve pembayaran |
| POST | `/admin/verifications/{id}/reject` | Reject pembayaran |
| GET | `/admin/reports/summary` | Dashboard ringkasan |
| GET | `/admin/reports/export` | Export laporan (Excel/CSV) |
| GET | `/admin/notification-logs` | Log notifikasi |
| GET | `/admin/configurations` | Ambil konfigurasi |
| PUT | `/admin/configurations/{key}` | Update konfigurasi |

### 9.5 Pelanggan Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/customer/auth/login` | Login pelanggan |
| GET | `/customer/me` | Data profil pelanggan |
| GET | `/customer/invoices/current` | Invoice bulan berjalan |
| GET | `/customer/invoices` | Riwayat invoice (paginated) |
| GET | `/customer/invoices/{id}` | Detail invoice |
| POST | `/customer/invoices/{id}/payment-proof` | Upload bukti transfer |

### 9.5 Auth Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/auth/login` | Login admin |
| POST | `/auth/logout` | Logout |
| GET | `/auth/me` | Current user info |

---

*AGENTS.md — Terakhir diubah: 6 Agustus 2026 (v0.2) — perubahan: API-first, UUID v7, JWT auth (tymon/jwt-auth), refresh token, dual-role RBAC, state machine invoice, Metronic CDN pinning, eager loading, Pint/PHPStan*
