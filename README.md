# Keuangan Web

Proyek **Keuangan Web** adalah aplikasi manajemen arus kas (*cashflow tracker*) berbasis web yang dirancang untuk mencatat, mengelompokkan, dan memantau transaksi keuangan (uang masuk dan keluar) secara terstruktur.

---

## 🚀 Fitur Utama

- **Pencatatan Arus Kas (Cashflow Management):** Pencatatan transaksi keuangan yang dibedakan secara tegas menjadi dua tipe utama: `inflow` (pemasukan) dan `outflow` (pengeluaran).
- **Kategorisasi Transaksi:** Pengelompokan transaksi berdasarkan kategori kustom (misal: Gaji, Makanan, Transportasi, Hiburan) untuk kemudahan laporan.
- **Presisi Finansial (Integer/Sen):** Menggunakan tipe data `BIGINT` untuk menyimpan nominal uang dalam satuan terkecil (sen/dikali 100), menghindari *floating-point arithmetic bugs* serta *integer overflow*.
- **Manajemen Akses & Keamanan (RBAC):** Otentikasi pengguna yang dilengkapi dengan peran (*Role*) dan hak akses (*Permission*) untuk membatasi aksi/menu di dalam aplikasi.
- **Jejak Audit & Keamanan Data (Audit Trail & Soft Delete):** Merekam pengguna pembuat dan pembaru data (`created_by`, `updated_by`) serta menerapkan `deleted_at` (*soft delete*) untuk pemulihan data secara aman.

---

## 🛠️ Tech Stack

- **Backend Framework:** Laravel 13 (PHP 8.4+)
- **Authentication:** Laravel Fortify
- **Frontend Framework:** Inertia.js v3, React 19, & TypeScript
- **Styling & UI Components:** Tailwind CSS v4, Radix UI / Shadcn UI primitives, & Lucide Icons
- **Data Visualization:** Recharts
- **Database:** MySQL / PostgreSQL (Default: SQLite)
- **Build Tool:** Vite 8
- **Testing & Code Quality:** 
  - Backend: Pest PHP v4, PHPStan (Larastan), Laravel Pint
  - Frontend: ESLint v9, Prettier, Husky, & Lint-staged
- **Architecture Pattern:** Clean Architecture, Action Classes, PHP 8 Backed Enums, & Custom Eloquent Casts

---

## 📖 Dokumentasi Teknis

Detail teknis mengenai arsitektur, skema data, dan keputusan desain dapat diakses melalui dokumen terpisah berikut:

- **[Architecture & Design Specification](docs/ARCHITECTURE.md):** Penjelasan arsitektur sistem, *Architecture Decision Records* (ADR-001 s.d. ADR-004), serta *Data Flow Diagram* (DFD).
- **[Database Schema Specification](docs/DATABASE.md):** Dokumentasi skema database lengkap (DBML, ERD Mermaid, relasi antartabel, dan aturan presisi data).

---

## ⚙️ Panduan Instalasi Lokal

### Prasyarat
- **PHP** >= 8.4
- **Node.js** >= 20.x
- **Composer**

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/kaka-rdwn/keuangan-web.git
cd keuangan-web

# 2. Jalankan perintah setup otomatis
# (Otomatis menginstal paket, membuat file .env & database SQLite, generate key, menjalankan migrasi, serta build asset frontend)
composer run setup

# 3. (Opsional) Jalankan seeder untuk mengisi data awal/sample
php artisan db:seed

# 4. Jalankan lingkungan pengembangan
# (Menjalankan HTTP Server, Queue Listener, & Vite HMR secara bersamaan)
composer run dev
```

### 🔑 Akun Default (Dev/Testing)

Setelah menjalankan `php artisan db:seed`, Anda dapat masuk ke aplikasi menggunakan akun administrator bawaan:

- **Email:** `admin@keuangan.test`
- **Password:** `Admin#1234`

### 📧 Konfigurasi Mailer (Pengiriman Email)

Secara bawaan saat `composer run setup` dijalankan, file `.env` akan dibuat dari `.env.example`. Pastikan variabel lingkungan berikut sudah terkonfigurasi sesuai kebutuhan lokal Anda:

```env
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> **Catatan:**
> - Dengan `MAIL_MAILER=log`, email yang dikirimkan oleh sistem (seperti *reset password*) tidak dikirim ke alamat fisik, melainkan dicatat di file `storage/logs/laravel.log`.
> - Setelah menjalankan `composer run dev`, aplikasi dapat langsung diakses di browser melalui **`http://127.0.0.1:8000`**.

---

## 🧪 Testing & Standardisasi Kode

Untuk memastikan kualitas kode backend dan frontend tetap terjaga:

```bash
# Jalankan pengujian backend (Pest PHP)
composer run test

# Format kode PHP (Laravel Pint)
composer run lint

# Analisis statis kode PHP (PHPStan)
composer run types:check

# Menjalankan seluruh pemeriksaan CI secara lokal
composer run ci:check
```

---

## 📄 Lisensi

Proyek ini dilindungi di bawah lisensi [Apache-2.0](LICENSE).
