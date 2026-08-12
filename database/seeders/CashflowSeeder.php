<?php

namespace Database\Seeders;

use App\Enums\CashflowType;
use App\Models\Cashflow;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CashflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first()
            ?? User::where('email', 'admin@keuangan.test')->first()
            ?? User::first();

        if (! $admin) {
            return;
        }

        // Ambil kategori untuk referensi
        $inflowCategories = Category::where('type', CashflowType::INFLOW)->get();
        $outflowCategories = Category::where('type', CashflowType::OUTFLOW)->get();

        $salaryCategory = $inflowCategories->where('name', 'Gaji & Pendapatan Utama')->first() ?? $inflowCategories->first();

        $inflowTitles = [
            'Gaji Bulanan Utama',
            'Bonus Kinerja Tahunan',
            'Hasil Proyek Freelance',
            'Dividen Saham & Reksa Dana',
            'Hasil Penjualan Barang Bekas',
            'Cashback & Promo Bank',
            'Honor Pembicara / Mentor',
        ];

        $outflowTitlesNormal = [
            'Belanja Harian & Sembako',
            'Makan Siang & Kopi Harian',
            'Tagihan Listrik PLN & Air PDAM',
            'Biaya Paket Data & Internet WiFi',
            'Bahan Bakar Bensin & Tol',
            'Servis & Ganti Oli Kendaraan',
            'Beli Obat & Vitamin',
            'Belanja Keperluan Rumah Tangga',
            'Langganan Netflix & Spotify',
            'Uang Saku & Hiburan Akhir Pekan',
        ];

        $outflowTitlesDeficit = [
            'Biaya Liburan & Hotel Awal Tahun',
            'Pendaftaran SPP & Peralatan Sekolah',
            'Belanja Hadiah & Parcel Hari Raya',
            'Perbaikan Besar & Renovasi Rumah',
            'Pembelian Gadget / Elektronik Baru',
            'Tiket Pesawat & Akomodasi Liburan',
        ];

        // Seed data transaksi selama 24 bulan (2 tahun terakhir)
        // Menghasilkan total ~500 data (rata-rata 20-22 transaksi/bulan)
        for ($monthOffset = 23; $monthOffset >= 0; $monthOffset--) {
            $baseDate = now()->subMonths($monthOffset);
            $monthNum = (int) $baseDate->format('m');
            $daysInMonth = $baseDate->daysInMonth;

            // Bulan Defisit: Januari (1), Juli (7), Desember (12)
            $isDeficitMonth = in_array($monthNum, [1, 7, 12], true);

            // 1. Pemasukan (Inflow) Bulan Ini
            // Gaji Bulanan Utama
            $salaryDate = $baseDate->copy()->setDay(min(25, $daysInMonth))->toDateString();
            Cashflow::create([
                'name' => 'Gaji Bulanan Utama',
                'amount' => $isDeficitMonth ? rand(9000000, 11000000) : rand(13000000, 15000000),
                'type' => CashflowType::INFLOW,
                'category_id' => $salaryCategory ? $salaryCategory->id : Category::factory()->create(['type' => CashflowType::INFLOW])->id,
                'transaction_date' => $salaryDate,
                'description' => 'Pemasukan gaji bulanan utama',
                'created_by' => $admin->id,
                'created_at' => $salaryDate,
                'updated_at' => $salaryDate,
            ]);

            // Side Income Tambahan (1-2 transaksi)
            $sideIncomesCount = $isDeficitMonth ? rand(0, 1) : rand(1, 2);
            for ($i = 0; $i < $sideIncomesCount; $i++) {
                $tDate = $baseDate->copy()->setDay(rand(1, $daysInMonth))->toDateString();
                $title = fake()->randomElement(array_filter($inflowTitles, fn ($t) => $t !== 'Gaji Bulanan Utama'));
                $cat = $inflowCategories->random();

                Cashflow::create([
                    'name' => $title,
                    'amount' => rand(500000, 2500000),
                    'type' => CashflowType::INFLOW,
                    'category_id' => $cat->id,
                    'transaction_date' => $tDate,
                    'description' => 'Pemasukan tambahan '.strtolower($title),
                    'created_by' => $admin->id,
                    'created_at' => $tDate,
                    'updated_at' => $tDate,
                ]);
            }

            // 2. Pengeluaran (Outflow) Bulan Ini (16 - 20 transaksi rutin)
            $outflowCount = rand(16, 20);
            for ($i = 0; $i < $outflowCount; $i++) {
                $tDate = $baseDate->copy()->setDay(rand(1, $daysInMonth))->toDateString();
                $title = fake()->randomElement($outflowTitlesNormal);
                $cat = $outflowCategories->random();

                Cashflow::create([
                    'name' => $title,
                    'amount' => rand(60000, 750000),
                    'type' => CashflowType::OUTFLOW,
                    'category_id' => $cat->id,
                    'transaction_date' => $tDate,
                    'description' => 'Pengeluaran '.strtolower($title),
                    'created_by' => $admin->id,
                    'created_at' => $tDate,
                    'updated_at' => $tDate,
                ]);
            }

            // 3. Pengeluaran Besar Tambahan untuk Bulan Defisit (Januari, Juli, Desember)
            if ($isDeficitMonth) {
                $bigExpenseCount = rand(2, 3);
                for ($b = 0; $b < $bigExpenseCount; $b++) {
                    $tDate = $baseDate->copy()->setDay(rand(5, $daysInMonth))->toDateString();
                    $title = fake()->randomElement($outflowTitlesDeficit);
                    $cat = $outflowCategories->random();

                    Cashflow::create([
                        'name' => $title,
                        'amount' => rand(3000000, 6500000),
                        'type' => CashflowType::OUTFLOW,
                        'category_id' => $cat->id,
                        'transaction_date' => $tDate,
                        'description' => 'Pengeluaran musiman: '.strtolower($title),
                        'created_by' => $admin->id,
                        'created_at' => $tDate,
                        'updated_at' => $tDate,
                    ]);
                }
            }
        }
    }
}
