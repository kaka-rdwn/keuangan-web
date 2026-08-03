# Keuangan Web

Proyek Keuangan Web adalah aplikasi manajemen arus kas (cashflow tracker) berbasis web yang dirancang untuk mencatat, mengelompokkan, dan memantau transaksi keuangan (uang masuk dan keluar) secara terstruktur.

## Tujuan & Fungsi Utama Sistem

- **Pencatatan Arus Kas (Cashflow Management):** Menjadi tempat mencatat seluruh transaksi keuangan melalui tabel cashflows. Sistem ini membedakan transaksi menjadi dua tipe utama: inflow (pemasukan) dan outflow (pengeluaran).

- **Kategorisasi Transaksi:** Setiap transaksi dikelompokkan berdasarkan entitas categories (misal: Gaji, Makanan, Transportasi, Hiburan) untuk mempermudah analisis laporan keuangan.

- **Presisi Finansial (Integer/Sen):** Menggunakan tipe data bigint pada atribut amount untuk menyimpan nilai uang dalam satuan terkecil (sen), menghindari floating-point bug serta masalah integer overflow.

- **Manajemen Akses & Keamanan (RBAC):** Memiliki sistem otentikasi users yang dilengkapi dengan roles dan permissions untuk membatasi hak akses pengakses web (misal: mana menu untuk Admin, mana untuk User biasa).

- **Jejak Audit & Keamanan Data (Audit Trail & Soft Delete):** Merekam siapa yang membuat dan memperbarui data (created_by, updated_by) serta menggunakan deleted_at (soft delete) agar data yang dihapus tidak langsung hilang dari database.

## Database

Dokumentasi lengkap mengenai rancangan dan skema database (ERD, relasi antartabel, atribut, serta aturan presisi finansial) dapat dilihat pada [docs/DATABASE.md](docs/DATABASE.md).

