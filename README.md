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

```bash
# 1. Clone repository
git clone https://github.com/kaka-rdwn/keuangan-web.git
cd keuangan-web

# 2. Jalankan perintah setup otomatis (Install dependencies, .env, key, & migration)
composer run setup

# 3. (Opsional) Jalankan seeder untuk data awal/sample
php artisan db:seed

# 4. Jalankan aplikasi (Server, Queue Listener, & Vite berjalan bersamaan)
composer run dev
```

