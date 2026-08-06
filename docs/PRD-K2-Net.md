# Product Requirements Document (PRD)
# K2-Net — Sistem Manajemen Tagihan & Pelanggan Mini ISP (RT/RW Net)

| Metadata | Keterangan |
|---|---|
| Nama Produk | K2-Net Billing & Customer Management System |
| Versi Dokumen | v1.0 |
| Status | Draft — Siap Review Tim Pengembang |
| Pemilik Produk | Product Manager K2-Net |
| Target Rilis | MVP (Minimum Viable Product) |

---

## 1. Executive Summary & Business Objectives

### 1.1 Latar Belakang
K2-Net adalah bisnis penyedia layanan internet mandiri (Mini ISP / RT/RW Net) yang menjual kembali (reselling) bandwidth dari satu langganan utama kepada pelanggan-pelanggan di sekitar area layanan. Saat ini, proses penagihan, pencatatan pembayaran, dan pengingat jatuh tempo diperkirakan masih dilakukan secara manual (melalui pesan pribadi, catatan buku/spreadsheet), yang menimbulkan risiko:

- Keterlambatan pembayaran pelanggan karena tidak ada pengingat otomatis yang konsisten.
- Kesalahan pencatatan manual (human error) dalam rekap pendapatan dan piutang.
- Waktu operasional admin yang banyak terpakai untuk mengirim tagihan dan verifikasi bukti transfer satu per satu.
- Sulitnya pelanggan memantau riwayat pembayaran dan status tagihan mereka sendiri.

### 1.2 Tujuan Bisnis (Business Objectives)
1. **Mengurangi tingkat keterlambatan pembayaran (late payment rate)** melalui notifikasi otomatis berjenjang (H-3, Hari-H, H+3).
2. **Mengotomatisasi proses penagihan bulanan** sehingga admin tidak perlu membuat invoice secara manual setiap bulan.
3. **Mempercepat proses verifikasi pembayaran** dengan portal upload bukti transfer dan dashboard verifikasi terpusat.
4. **Meningkatkan transparansi** bagi pelanggan melalui portal self-service untuk mengecek status tagihan dan riwayat pembayaran.
5. **Menyediakan data pengambilan keputusan** bagi pemilik bisnis melalui dashboard pelaporan (rekap pendapatan, piutang, jumlah pelanggan aktif).

### 1.3 Target Pengguna
- **Admin K2-Net** (pemilik/pengelola bisnis) — mengelola data pelanggan, paket, tagihan, dan verifikasi pembayaran.
- **Pelanggan K2-Net** — pengguna akhir yang berlangganan internet, melakukan pembayaran, dan memantau status tagihan.

### 1.4 Metrik Keberhasilan (Success Metrics)
| Metrik | Baseline (Manual) | Target Setelah Implementasi |
|---|---|---|
| Rata-rata keterlambatan pembayaran | Tidak terukur | Turun minimal 30% dalam 3 bulan pertama |
| Waktu admin membuat tagihan bulanan | ± 1-2 hari manual | < 5 menit (otomatis via cron) |
| Waktu verifikasi 1 bukti pembayaran | ± 5-10 menit manual | < 1 menit via dashboard |
| Kesalahan pencatatan piutang | Rawan human error | Mendekati 0% (tercatat sistem) |

---

## 2. User Personas

### 2.1 Persona 1 — Admin K2-Net
| Atribut | Detail |
|---|---|
| Nama Persona | Kang Dedi — Pemilik & Pengelola K2-Net |
| Peran | Pemilik bisnis merangkap admin operasional harian |
| Usia | 35 tahun |
| Latar Belakang | Memiliki 1-2 orang staf/teknisi, mengelola sendiri urusan administrasi dan keuangan |
| Perangkat | Laptop untuk kerja administratif, HP Android untuk cek cepat saat mobile |
| Tujuan | Ingin proses tagihan berjalan otomatis, tahu siapa saja yang menunggak, dan bisa memverifikasi pembayaran dengan cepat tanpa harus cek WhatsApp satu per satu |
| Frustrasi (Pain Points) | Lupa mengirim tagihan ke pelanggan, kesulitan rekap siapa yang sudah/belum bayar, banyak waktu terbuang mengecek bukti transfer manual satu per satu di HP |
| Kebutuhan Sistem | Dashboard ringkas, notifikasi otomatis, laporan yang bisa diunduh untuk pembukuan |

### 2.2 Persona 2 — Pelanggan K2-Net
| Atribut | Detail |
|---|---|
| Nama Persona | Bu Rina — Pelanggan Rumahan |
| Peran | Pengguna layanan internet rumahan dari K2-Net |
| Usia | 40 tahun |
| Latar Belakang | Awam teknologi, terbiasa menggunakan WhatsApp dan aplikasi mobile banking |
| Perangkat | HP Android |
| Tujuan | Ingin tahu kapan harus bayar, berapa jumlahnya, dan ingin ada bukti bahwa pembayarannya sudah diterima dan diverifikasi |
| Frustrasi (Pain Points) | Kadang lupa tanggal jatuh tempo, tidak yakin apakah bukti transfer yang dikirim sudah diterima admin atau belum, tidak ada riwayat pembayaran yang bisa dicek sendiri |
| Kebutuhan Sistem | Pengingat otomatis via WhatsApp/Email, cara mudah upload bukti bayar, kepastian status (lunas/belum) yang jelas |

---

## 3. User Stories

### 3.1 Modul Master Data (Admin)
- **US-01:** Sebagai Admin, saya ingin menambahkan data pelanggan baru (nama, kontak WA/Email, alamat pemasangan, paket, status) agar pelanggan baru dapat langsung masuk ke sistem penagihan.
- **US-02:** Sebagai Admin, saya ingin mengubah data pelanggan (misalnya ganti nomor WA atau paket) agar data selalu akurat.
- **US-03:** Sebagai Admin, saya ingin menonaktifkan/mengisolir pelanggan agar pelanggan yang berhenti berlangganan atau menunggak tidak lagi menerima tagihan aktif/dapat ditandai statusnya.
- **US-04:** Sebagai Admin, saya ingin mengelola daftar paket internet beserta harga bulanannya agar dapat menyesuaikan penawaran layanan.
- **US-05:** Sebagai Admin, saya ingin mencari dan memfilter data pelanggan berdasarkan status (aktif/isolir) agar mudah memantau kondisi pelanggan.

### 3.2 Modul Tagihan & Pembayaran
- **US-06:** Sebagai Admin, saya ingin sistem secara otomatis membuat invoice bulanan untuk semua pelanggan aktif sebelum tanggal jatuh tempo, agar saya tidak perlu membuatnya manual satu per satu.
- **US-07:** Sebagai Pelanggan, saya ingin mengunggah bukti transfer (PDF/gambar) melalui portal saya agar admin dapat memverifikasi pembayaran saya.
- **US-08:** Sebagai Admin, saya ingin melihat daftar bukti pembayaran yang masuk dan menyetujui (approve) atau menolak (reject) dengan alasan, agar validasi pembayaran tercatat rapi.
- **US-09:** Sebagai Pelanggan, saya ingin mengetahui alasan jika bukti pembayaran saya ditolak, agar saya bisa mengunggah ulang bukti yang benar.
- **US-10:** Sebagai Admin, saya ingin sistem otomatis mengubah status tagihan menjadi "Lunas" setelah bukti pembayaran disetujui.

### 3.3 Modul Portal Pelanggan
- **US-11:** Sebagai Pelanggan, saya ingin login ke portal saya untuk melihat status tagihan bulan berjalan (jumlah, jatuh tempo, status).
- **US-12:** Sebagai Pelanggan, saya ingin melihat riwayat pembayaran bulan-bulan sebelumnya agar saya punya catatan pribadi.
- **US-13:** Sebagai Pelanggan, saya ingin mengunduh/melihat detail invoice setiap bulan sebagai bukti tagihan resmi.

### 3.4 Modul Notifikasi
- **US-14:** Sebagai Pelanggan, saya ingin menerima pengingat otomatis via WhatsApp/Email 3 hari sebelum jatuh tempo agar saya tidak lupa membayar.
- **US-15:** Sebagai Pelanggan, saya ingin menerima pengingat pada hari-H jatuh tempo jika belum membayar.
- **US-16:** Sebagai Pelanggan, saya ingin menerima notifikasi susulan (H+3) jika saya belum juga membayar setelah jatuh tempo.
- **US-17:** Sebagai Pelanggan, saya ingin menerima notifikasi konfirmasi ketika pembayaran saya berhasil diverifikasi.
- **US-18:** Sebagai Admin, saya ingin sistem notifikasi berjalan otomatis di latar belakang tanpa perlu saya trigger manual setiap hari.

### 3.5 Modul Pelaporan
- **US-19:** Sebagai Admin, saya ingin melihat dashboard ringkasan (total pendapatan bulan ini, total pelanggan aktif, total piutang/tunggakan) agar saya bisa memantau kesehatan bisnis secara cepat.
- **US-20:** Sebagai Admin, saya ingin mengekspor data laporan (tagihan, pembayaran, pelanggan) ke format Excel/CSV agar bisa diarsipkan atau diolah lebih lanjut untuk pembukuan.

---

## 4. Functional & Non-Functional Requirements

### 4.1 Functional Requirements (FR)

#### A. Modul Master Data
| ID | Requirement |
|---|---|
| FR-01 | Sistem harus menyediakan CRUD (Create, Read, Update, Delete/Deactivate) untuk data pelanggan: nama lengkap, nomor WhatsApp, email, alamat pemasangan, paket berlangganan, dan status (Aktif/Isolir/Nonaktif). |
| FR-02 | Sistem harus mencegah penghapusan permanen (hard delete) data pelanggan yang memiliki riwayat transaksi; penghapusan hanya berupa soft delete/nonaktifkan. |
| FR-03 | Sistem harus menyediakan CRUD untuk master data paket internet: nama paket, kecepatan (opsional), dan harga bulanan. |
| FR-04 | Sistem harus memvalidasi format nomor WhatsApp dan email saat input/edit data pelanggan. |
| FR-05 | Sistem harus menyediakan fitur pencarian dan filter pelanggan berdasarkan nama, status, dan paket. |

#### B. Modul Tagihan & Pembayaran
| ID | Requirement |
|---|---|
| FR-06 | Sistem harus secara otomatis (via scheduler/cron job) meng-generate invoice bulanan untuk setiap pelanggan berstatus "Aktif", dijalankan pada tanggal yang dapat dikonfigurasi (misal setiap tanggal 25 untuk periode bulan berikutnya). |
| FR-07 | Setiap invoice yang di-generate harus memiliki nomor invoice unik, periode tagihan, nominal, tanggal terbit, dan tanggal jatuh tempo. |
| FR-08 | Sistem tidak boleh membuat duplikasi invoice untuk pelanggan dan periode yang sama. |
| FR-09 | Sistem harus menyediakan form upload bukti transfer (format PDF, JPG, PNG; maksimum ukuran file dapat dikonfigurasi, disarankan maks. 5MB) yang terhubung ke invoice terkait. |
| FR-10 | Sistem harus menampilkan status tagihan dengan minimal 4 kondisi: **Belum Bayar**, **Menunggu Verifikasi**, **Lunas**, **Ditolak**. |
| FR-11 | Sistem harus menyediakan dashboard admin berisi daftar bukti pembayaran yang masuk dengan aksi **Approve** dan **Reject** (disertai kolom catatan/alasan penolakan). |
| FR-12 | Saat admin melakukan Approve, status invoice otomatis berubah menjadi "Lunas" dan tanggal pelunasan tercatat. |
| FR-13 | Saat admin melakukan Reject, status invoice kembali menjadi "Belum Bayar" dan pelanggan dapat mengunggah ulang bukti baru. |
| FR-14 | Sistem harus menyimpan log riwayat setiap perubahan status tagihan (audit trail) minimal: siapa yang mengubah, waktu, dan status sebelum/sesudah. |

#### C. Modul Portal Pelanggan
| ID | Requirement |
|---|---|
| FR-15 | Sistem harus menyediakan mekanisme login khusus pelanggan (autentikasi terpisah dari admin). |
| FR-16 | Dashboard pelanggan harus menampilkan status tagihan bulan berjalan secara jelas (nominal, jatuh tempo, status). |
| FR-17 | Dashboard pelanggan harus menampilkan riwayat tagihan dan pembayaran bulan-bulan sebelumnya, terurut dari yang terbaru. |
| FR-18 | Pelanggan hanya dapat melihat dan mengelola data tagihannya sendiri (tidak dapat mengakses data pelanggan lain). |

#### D. Modul Notifikasi
| ID | Requirement |
|---|---|
| FR-19 | Sistem harus menjalankan scheduler/cron job otomatis harian untuk mengecek tagihan yang perlu diingatkan. |
| FR-20 | Sistem harus mengirimkan notifikasi pengingat pada: H-3 sebelum jatuh tempo, Hari-H jatuh tempo (jika belum lunas), dan H+3 setelah jatuh tempo (jika masih belum lunas). |
| FR-21 | Sistem harus mengirimkan notifikasi otomatis kepada pelanggan saat pembayarannya berhasil diverifikasi (Approve). |
| FR-22 | Sistem harus mendukung minimal 2 kanal notifikasi: WhatsApp dan Email. |
| FR-23 | Sistem harus mencatat log status pengiriman notifikasi (terkirim/gagal) untuk keperluan audit dan troubleshooting. |
| FR-24 | Sistem tidak boleh mengirim notifikasi duplikat untuk kombinasi invoice dan jenis notifikasi yang sama pada hari yang sama. |

#### E. Modul Pelaporan
| ID | Requirement |
|---|---|
| FR-25 | Sistem harus menyediakan dashboard ringkasan admin berisi minimal: total pendapatan bulan berjalan, total pelanggan aktif, dan total piutang (tagihan belum lunas). |
| FR-26 | Sistem harus menyediakan fitur export data (daftar tagihan, pembayaran, dan pelanggan) ke format Excel (.xlsx) dan/atau CSV. |
| FR-27 | Sistem harus memungkinkan filter laporan berdasarkan rentang tanggal/periode sebelum diekspor. |

### 4.2 Non-Functional Requirements (NFR)
| ID | Kategori | Requirement |
|---|---|---|
| NFR-01 | Keamanan | Data sensitif (kredensial login, kontak pelanggan) harus dienkripsi saat disimpan (at rest) dan dalam transit (HTTPS/TLS). |
| NFR-02 | Keamanan | Sistem harus menerapkan role-based access control (RBAC) — memisahkan hak akses Admin dan Pelanggan secara tegas. |
| NFR-03 | Keamanan | File bukti pembayaran yang diunggah harus divalidasi tipe dan ukurannya untuk mencegah upload file berbahaya. |
| NFR-04 | Reliabilitas | Cron job invoice generation dan notifikasi harus memiliki mekanisme retry/logging jika gagal dijalankan, serta notifikasi kegagalan ke admin. |
| NFR-05 | Performa | Dashboard admin dan portal pelanggan harus dapat memuat data dalam waktu < 3 detik untuk jumlah pelanggan hingga 1000 data. |
| NFR-06 | Ketersediaan (Availability) | Target uptime sistem minimal 99% (di luar jadwal maintenance terjadwal). |
| NFR-07 | Skalabilitas | Arsitektur sistem harus mampu menangani pertumbuhan jumlah pelanggan tanpa perubahan besar pada desain basis data. |
| NFR-08 | Usability | Portal pelanggan harus responsif (mobile-friendly) mengingat mayoritas pelanggan mengakses via HP. |
| NFR-09 | Auditabilitas | Semua aksi kritikal (approve/reject pembayaran, perubahan status pelanggan) harus tercatat dengan log waktu dan pelaku aksi. |
| NFR-10 | Backup & Recovery | Basis data harus memiliki backup otomatis harian dengan retensi minimal 30 hari. |
| NFR-11 | Konfigurabilitas | Parameter H-3/H-0/H+3 dan tanggal generate invoice harus dapat dikonfigurasi oleh admin tanpa perlu perubahan kode. |
| NFR-12 | Kompatibilitas | Portal dapat diakses melalui browser modern (Chrome, Safari, Firefox versi 2 tahun terakhir) baik di desktop maupun mobile. |

---

## 5. User Flows

### 5.1 Alur Utama: Dari Generate Tagihan Otomatis hingga Pembayaran Lunas

```
[Awal Periode - Tanggal Konfigurasi, misal tgl 25]
        │
        ▼
(1) Cron Job "Generate Invoice" berjalan otomatis
        │
        ▼
(2) Sistem mengambil seluruh pelanggan berstatus "Aktif"
        │
        ▼
(3) Sistem membuat invoice baru per pelanggan
    - Nomor invoice unik
    - Nominal sesuai paket
    - Jatuh tempo (misal tgl 5 bulan berikutnya)
    - Status awal: "Belum Bayar"
        │
        ▼
(4) Cron Job "Notifikasi H-3" berjalan otomatis
    → Kirim WA/Email pengingat ke pelanggan yang belum lunas
        │
        ▼
(5) Pelanggan login ke Portal Pelanggan
        │
        ▼
(6) Pelanggan melihat status tagihan bulan berjalan
        │
        ▼
(7) Pelanggan melakukan transfer manual ke rekening K2-Net
        │
        ▼
(8) Pelanggan mengunggah bukti transfer (PDF/Gambar) ke sistem
        │
        ▼
(9) Status invoice berubah menjadi "Menunggu Verifikasi"
        │
        ▼
(10) Admin membuka Dashboard Verifikasi Pembayaran
        │
        ├──► (11a) Admin memeriksa bukti transfer
        │           │
        │           ├──[Sesuai]──► (12a) Admin klik "Approve"
        │           │                     │
        │           │                     ▼
        │           │              Status invoice → "Lunas"
        │           │              Tanggal pelunasan tercatat
        │           │                     │
        │           │                     ▼
        │           │              Notifikasi sukses dikirim ke pelanggan
        │           │                     │
        │           │                     ▼
        │           │              [SELESAI - Tagihan Lunas]
        │           │
        │           └──[Tidak Sesuai]──► (12b) Admin klik "Reject" + isi alasan
        │                                       │
        │                                       ▼
        │                                Status invoice kembali → "Belum Bayar"
        │                                       │
        │                                       ▼
        │                                Notifikasi penolakan + alasan dikirim ke pelanggan
        │                                       │
        │                                       ▼
        │                                Kembali ke langkah (7) - Pelanggan upload ulang
        │
        ▼
(13) [Jika hingga Hari-H belum ada pembayaran]
        → Cron Job "Notifikasi Hari-H" mengirim reminder jatuh tempo
        │
        ▼
(14) [Jika hingga H+3 masih belum lunas]
        → Cron Job "Notifikasi H+3" mengirim reminder keterlambatan
        │
        ▼
(15) Data tagihan menunggak tercatat pada Dashboard Pelaporan Admin (Piutang)
```

### 5.2 Alur Sekunder: Admin Mengelola Data Pelanggan Baru
1. Admin login ke sistem.
2. Admin membuka menu **Master Data → Pelanggan**.
3. Admin klik **Tambah Pelanggan Baru**.
4. Admin mengisi form (nama, kontak WA/Email, alamat, pilih paket, status default "Aktif").
5. Sistem memvalidasi input (format kontak wajib benar).
6. Sistem menyimpan data pelanggan baru.
7. Pelanggan baru otomatis masuk ke daftar penerima invoice pada siklus generate berikutnya.

### 5.3 Alur Sekunder: Admin Export Laporan
1. Admin membuka menu **Pelaporan**.
2. Admin memilih rentang tanggal/periode laporan.
3. Admin memilih jenis laporan (Tagihan/Pembayaran/Pelanggan).
4. Admin klik **Export ke Excel/CSV**.
5. Sistem menghasilkan file dan menyediakan link unduhan.

---

## 6. Acceptance Criteria (Format BDD — Given, When, Then)

> Catatan untuk QA: Setiap skenario di bawah ini harus diuji secara independen dan mencakup kondisi *happy path* maupun *edge case/negative case*.

### 6.1 Modul Master Data

**Skenario 6.1.1 — Berhasil menambahkan pelanggan baru dengan data valid**
```
Given   Admin sudah login ke sistem dan berada di halaman "Tambah Pelanggan"
When    Admin mengisi nama "Budi Santoso", nomor WA "081234567890",
        email "budi@email.com", alamat "Jl. Melati No. 5", memilih paket "10 Mbps - Rp150.000",
        dan status "Aktif", kemudian klik "Simpan"
Then    Sistem menyimpan data pelanggan baru ke database
And     Sistem menampilkan pesan sukses "Data pelanggan berhasil ditambahkan"
And     Data pelanggan baru muncul pada daftar pelanggan dengan status "Aktif"
```

**Skenario 6.1.2 — Gagal menambahkan pelanggan dengan nomor WA tidak valid**
```
Given   Admin berada di halaman "Tambah Pelanggan"
When    Admin mengisi nomor WA dengan format tidak valid (misal "abc123")
        dan klik "Simpan"
Then    Sistem menolak penyimpanan data
And     Sistem menampilkan pesan error "Format nomor WhatsApp tidak valid"
And     Data pelanggan tidak tersimpan ke database
```

**Skenario 6.1.3 — Admin mengubah status pelanggan menjadi "Isolir"**
```
Given   Admin melihat detail pelanggan "Budi Santoso" dengan status "Aktif"
When    Admin mengubah status menjadi "Isolir" dan menyimpan perubahan
Then    Status pelanggan "Budi Santoso" berubah menjadi "Isolir"
And     Pelanggan tersebut tidak lagi disertakan dalam proses generate invoice bulan berikutnya
```

**Skenario 6.1.4 — Admin mencoba menghapus pelanggan yang memiliki riwayat transaksi**
```
Given   Pelanggan "Budi Santoso" memiliki minimal 1 riwayat invoice
When    Admin mencoba melakukan hapus permanen (hard delete) pada data pelanggan tersebut
Then    Sistem menolak proses hapus permanen
And     Sistem menampilkan opsi "Nonaktifkan Pelanggan" sebagai alternatif
And     Data pelanggan dan riwayat transaksinya tetap tersimpan di database
```

### 6.2 Modul Tagihan Otomatis (Invoice Generation)

**Skenario 6.2.1 — Sistem berhasil generate invoice otomatis untuk seluruh pelanggan aktif**
```
Given   Terdapat 50 pelanggan berstatus "Aktif" dalam sistem
And     Tanggal sistem saat ini adalah tanggal konfigurasi generate invoice (misal tanggal 25)
When    Cron job "Generate Invoice Bulanan" dijalankan
Then    Sistem membuat 50 invoice baru, masing-masing 1 invoice per pelanggan aktif
And     Setiap invoice memiliki nomor unik, nominal sesuai paket pelanggan, dan status "Belum Bayar"
And     Tanggal jatuh tempo invoice terisi sesuai konfigurasi (misal tanggal 5 bulan berikutnya)
```

**Skenario 6.2.2 — Sistem tidak membuat invoice untuk pelanggan berstatus "Isolir"**
```
Given   Terdapat 50 pelanggan aktif dan 5 pelanggan berstatus "Isolir"
When    Cron job "Generate Invoice Bulanan" dijalankan
Then    Sistem hanya membuat 50 invoice baru untuk pelanggan aktif
And     Tidak ada invoice yang dibuat untuk 5 pelanggan berstatus "Isolir"
```

**Skenario 6.2.3 — Sistem mencegah duplikasi invoice pada periode yang sama**
```
Given   Pelanggan "Budi Santoso" sudah memiliki invoice untuk periode "Agustus 2026"
When    Cron job "Generate Invoice Bulanan" dijalankan ulang secara tidak sengaja pada periode yang sama
Then    Sistem tidak membuat invoice baru duplikat untuk pelanggan "Budi Santoso" pada periode "Agustus 2026"
And     Sistem mencatat log bahwa proses generate untuk pelanggan tersebut dilewati (skipped) karena sudah ada
```

### 6.3 Modul Upload & Verifikasi Bukti Pembayaran

**Skenario 6.3.1 — Pelanggan berhasil mengunggah bukti transfer dengan format valid**
```
Given   Pelanggan "Budi Santoso" login ke portal dan memiliki invoice berstatus "Belum Bayar"
When    Pelanggan mengunggah file "bukti_transfer.jpg" berukuran 2MB pada invoice tersebut
Then    Sistem menerima dan menyimpan file bukti transfer
And     Status invoice berubah dari "Belum Bayar" menjadi "Menunggu Verifikasi"
And     Bukti pembayaran muncul pada dashboard verifikasi Admin
```

**Skenario 6.3.2 — Pelanggan gagal mengunggah file dengan format tidak didukung**
```
Given   Pelanggan berada di halaman upload bukti pembayaran
When    Pelanggan mengunggah file dengan ekstensi ".exe"
Then    Sistem menolak file tersebut
And     Sistem menampilkan pesan error "Format file tidak didukung. Gunakan PDF, JPG, atau PNG"
And     Status invoice tetap "Belum Bayar"
```

**Skenario 6.3.3 — Pelanggan gagal mengunggah file yang melebihi batas ukuran**
```
Given   Batas maksimum ukuran file yang dikonfigurasi adalah 5MB
When    Pelanggan mengunggah file berukuran 8MB
Then    Sistem menolak proses upload
And     Sistem menampilkan pesan error "Ukuran file melebihi batas maksimum 5MB"
```

**Skenario 6.3.4 — Admin berhasil menyetujui (approve) bukti pembayaran**
```
Given   Invoice pelanggan "Budi Santoso" berstatus "Menunggu Verifikasi"
And     Admin berada di dashboard verifikasi pembayaran
When    Admin memeriksa bukti transfer dan klik tombol "Approve"
Then    Status invoice berubah menjadi "Lunas"
And     Sistem mencatat tanggal dan waktu pelunasan
And     Sistem mencatat log audit (admin mana yang melakukan approve, kapan)
And     Notifikasi konfirmasi pembayaran sukses terkirim ke pelanggan via WA/Email
```

**Skenario 6.3.5 — Admin menolak (reject) bukti pembayaran yang tidak sesuai**
```
Given   Invoice pelanggan "Budi Santoso" berstatus "Menunggu Verifikasi"
When    Admin klik tombol "Reject" dan mengisi alasan "Nominal transfer tidak sesuai tagihan"
Then    Status invoice berubah kembali menjadi "Belum Bayar"
And     Alasan penolakan tersimpan dan dapat dilihat oleh pelanggan
And     Notifikasi penolakan beserta alasannya terkirim ke pelanggan via WA/Email
And     Pelanggan dapat mengunggah ulang bukti pembayaran baru pada invoice yang sama
```

**Skenario 6.3.6 — Admin mencoba approve invoice yang statusnya bukan "Menunggu Verifikasi"**
```
Given   Invoice pelanggan "Budi Santoso" berstatus "Lunas"
When    Admin mencoba mengakses aksi "Approve" pada invoice tersebut
Then    Sistem menonaktifkan/menyembunyikan tombol "Approve" dan "Reject"
And     Sistem tidak mengizinkan perubahan status ganda pada invoice yang sudah lunas
```

### 6.4 Modul Portal Pelanggan

**Skenario 6.4.1 — Pelanggan berhasil melihat status tagihan bulan berjalan**
```
Given   Pelanggan "Budi Santoso" login ke portal
And     Memiliki invoice aktif periode "Agustus 2026" berstatus "Belum Bayar"
When    Pelanggan membuka halaman "Dashboard"
Then    Sistem menampilkan nominal tagihan, tanggal jatuh tempo, dan status "Belum Bayar"
```

**Skenario 6.4.2 — Pelanggan melihat riwayat pembayaran bulan-bulan sebelumnya**
```
Given   Pelanggan "Budi Santoso" memiliki 6 riwayat invoice pada bulan-bulan sebelumnya
When    Pelanggan membuka menu "Riwayat Pembayaran"
Then    Sistem menampilkan daftar 6 invoice terurut dari yang terbaru ke terlama
And     Setiap baris riwayat menampilkan periode, nominal, status, dan tanggal pelunasan (jika lunas)
```

**Skenario 6.4.3 — Pelanggan tidak dapat mengakses data tagihan pelanggan lain**
```
Given   Pelanggan "Budi Santoso" sudah login dengan sesi aktif
When    Pelanggan mencoba mengakses URL/endpoint detail invoice milik pelanggan lain secara langsung
Then    Sistem menolak akses dan menampilkan pesan "Akses ditolak" atau error 403
```

### 6.5 Modul Notifikasi Otomatis

**Skenario 6.5.1 — Sistem mengirim notifikasi H-3 sebelum jatuh tempo**
```
Given   Invoice pelanggan "Budi Santoso" memiliki jatuh tempo tanggal 5 Agustus 2026
And     Invoice tersebut masih berstatus "Belum Bayar"
And     Tanggal sistem saat ini adalah 2 Agustus 2026 (H-3)
When    Cron job notifikasi harian dijalankan
Then    Sistem mengirimkan pesan pengingat via WhatsApp dan/atau Email ke pelanggan
And     Sistem mencatat log pengiriman notifikasi dengan jenis "H-3" dan status "Terkirim"
```

**Skenario 6.5.2 — Sistem tidak mengirim notifikasi jika invoice sudah lunas**
```
Given   Invoice pelanggan "Budi Santoso" berstatus "Lunas"
And     Tanggal sistem saat ini bertepatan dengan H-3 dari tanggal jatuh tempo invoice tersebut
When    Cron job notifikasi harian dijalankan
Then    Sistem tidak mengirimkan notifikasi pengingat untuk invoice tersebut
```

**Skenario 6.5.3 — Sistem mengirim notifikasi H+3 untuk tagihan yang masih menunggak**
```
Given   Invoice pelanggan memiliki jatuh tempo tanggal 5 Agustus 2026 dan masih berstatus "Belum Bayar"
And     Tanggal sistem saat ini adalah 8 Agustus 2026 (H+3)
When    Cron job notifikasi harian dijalankan
Then    Sistem mengirimkan notifikasi keterlambatan pembayaran ke pelanggan
And     Log notifikasi jenis "H+3" tercatat dengan status "Terkirim"
```

**Skenario 6.5.4 — Sistem tidak mengirim notifikasi duplikat pada hari yang sama**
```
Given   Notifikasi jenis "H-3" untuk invoice tertentu sudah terkirim hari ini
When    Cron job notifikasi harian dijalankan kembali pada hari yang sama (misal karena restart server)
Then    Sistem tidak mengirim ulang notifikasi "H-3" yang sama untuk invoice tersebut
```

**Skenario 6.5.5 — Sistem mencatat kegagalan pengiriman notifikasi**
```
Given   Nomor WhatsApp pelanggan tidak valid atau API pengirim pesan mengalami gangguan
When    Cron job mencoba mengirimkan notifikasi ke pelanggan tersebut
Then    Sistem mencatat log dengan status "Gagal" beserta keterangan error
And     Sistem tidak menghentikan proses pengiriman notifikasi ke pelanggan lain dalam antrian yang sama
```

### 6.6 Modul Pelaporan

**Skenario 6.6.1 — Dashboard admin menampilkan ringkasan data yang akurat**
```
Given   Terdapat 45 pelanggan aktif, total pendapatan bulan ini Rp13.500.000,
        dan total piutang (belum lunas) Rp1.200.000
When    Admin membuka halaman "Dashboard Ringkasan"
Then    Sistem menampilkan "Total Pelanggan Aktif: 45"
And     Sistem menampilkan "Total Pendapatan Bulan Ini: Rp13.500.000"
And     Sistem menampilkan "Total Piutang: Rp1.200.000"
```

**Skenario 6.6.2 — Admin berhasil mengekspor laporan ke Excel**
```
Given   Admin berada di halaman "Pelaporan" dan memilih rentang tanggal "1 Juli 2026 - 31 Juli 2026"
When    Admin klik tombol "Export ke Excel"
Then    Sistem menghasilkan file berformat .xlsx yang berisi seluruh data transaksi
        sesuai rentang tanggal yang dipilih
And     File dapat diunduh oleh Admin
And     Data pada file sesuai dengan data yang ditampilkan pada dashboard periode tersebut
```

**Skenario 6.6.3 — Admin mengekspor laporan untuk rentang tanggal tanpa data**
```
Given   Tidak ada transaksi pada rentang tanggal yang dipilih Admin
When    Admin klik tombol "Export ke Excel/CSV"
Then    Sistem tetap menghasilkan file dengan header kolom lengkap namun tanpa baris data
And     Sistem menampilkan notifikasi informatif "Tidak ada data pada periode ini"
```

---

## 7. Out of Scope

Fitur-fitur berikut **tidak termasuk** dalam cakupan pengembangan versi ini (MVP) dan diusulkan sebagai roadmap pengembangan di fase berikutnya:

1. **Integrasi Payment Gateway Otomatis (Midtrans/Xendit)**
   Pada versi ini, verifikasi pembayaran masih bersifat manual (semi-otomatis) melalui upload bukti transfer dan approval admin. Integrasi payment gateway untuk pembayaran otomatis real-time (VA, QRIS, e-wallet) akan dipertimbangkan pada fase berikutnya.

2. **Integrasi API Mikrotik/RADIUS untuk Auto-Isolir**
   Pemutusan/pembatasan akses internet pelanggan yang menunggak saat ini masih dilakukan manual oleh teknisi. Integrasi otomatis dengan perangkat Mikrotik/RADIUS untuk auto-isolir berdasarkan status tagihan akan menjadi fitur lanjutan.

3. **Sistem Ticketing Komplain Pelanggan**
   Modul untuk pelanggan melaporkan gangguan/komplain layanan (misalnya internet lambat/mati) beserta fitur tracking tiket dan SLA penanganan tidak termasuk dalam versi ini.

4. **Aplikasi Mobile Native (Android/iOS)**
   Versi ini berfokus pada portal web yang responsif (mobile-friendly), bukan aplikasi native yang diunduh dari Play Store/App Store.

5. **Multi-Cabang/Multi-Tenant**
   Sistem versi ini dirancang untuk satu entitas bisnis K2-Net. Dukungan untuk mengelola beberapa cabang/reseller dalam satu sistem (multi-tenant) belum termasuk cakupan.

6. **Manajemen Inventaris Perangkat (Router/ONT/Kabel)**
   Pencatatan aset perangkat keras yang dipinjamkan/dipasang ke pelanggan tidak termasuk dalam versi ini.

7. **Fitur Diskon/Promo Otomatis dan Referral Pelanggan**
   Mekanisme kode promo, diskon otomatis, atau program referral pelanggan baru diusulkan sebagai pengembangan lanjutan.

---

## Lampiran: Catatan untuk Tim Pengembang

- Seluruh cron job (generate invoice, notifikasi) sebaiknya dirancang **idempotent** — dapat dijalankan berulang tanpa menimbulkan duplikasi data, mengacu pada skenario BDD 6.2.3 dan 6.5.4.
- Disarankan menyimpan **log terpisah** untuk aktivitas cron (generate invoice & notifikasi) guna mempermudah proses debugging dan monitoring operasional harian.
- Parameter seperti tanggal generate invoice, hari pengingat (H-3/H-0/H+3), dan batas ukuran file upload sebaiknya disimpan sebagai **konfigurasi (config/settings)**, bukan hard-coded, sesuai NFR-11.
- Struktur status invoice (Belum Bayar → Menunggu Verifikasi → Lunas/Ditolak) sebaiknya diimplementasikan sebagai **state machine** yang jelas untuk mencegah transisi status yang tidak valid (lihat skenario 6.3.6).

---

*Dokumen ini bersifat living document dan dapat diperbarui seiring dengan feedback dari tim pengembang maupun perubahan kebutuhan bisnis K2-Net.*
