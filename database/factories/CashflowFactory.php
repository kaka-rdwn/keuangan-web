<?php

namespace Database\Factories;

use App\Enums\CashflowType;
use App\Models\Cashflow;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cashflow>
 */
class CashflowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::instance(fake()->dateTimeBetween('-2 years', 'now'));
        $month = (int) $date->format('m');

        // Bulan defisit: Januari (1), Juli (7), Desember (12)
        $isDeficitMonth = in_array($month, [1, 7, 12], true);

        // Frekuensi transaksi: Outflow lebih sering (misal 65%), Inflow lebih jarang (35%)
        $type = fake()->boolean($isDeficitMonth ? 25 : 35) ? CashflowType::INFLOW : CashflowType::OUTFLOW;

        $inflowTitles = [
            'Gaji Bulanan Utama',
            'Bonus Kinerja Tahunan',
            'Hasil Proyek Freelance',
            'Dividen Saham & Reksa Dana',
            'Hasil Penjualan Barang Bekas',
            'Cashback & Promo Bank',
            'Honor Pembicara / Mentor',
            'Pengembalian Piutang Teman',
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

        if ($type === CashflowType::INFLOW) {
            $name = fake()->randomElement($inflowTitles);
            $amount = $name === 'Gaji Bulanan Utama'
                ? fake()->numberBetween(12000000, 15000000)
                : fake()->numberBetween(500000, 3500000);

            $category = Category::where('type', CashflowType::INFLOW)->inRandomOrder()->first();
            $categoryId = $category ? $category->id : Category::factory()->create(['type' => CashflowType::INFLOW])->id;
        } else {
            if ($isDeficitMonth && fake()->boolean(30)) {
                $name = fake()->randomElement($outflowTitlesDeficit);
                $amount = fake()->numberBetween(3500000, 7500000);
            } else {
                $name = fake()->randomElement($outflowTitlesNormal);
                $amount = fake()->numberBetween(50000, 850000);
            }

            $category = Category::where('type', CashflowType::OUTFLOW)->inRandomOrder()->first();
            $categoryId = $category ? $category->id : Category::factory()->create(['type' => CashflowType::OUTFLOW])->id;
        }

        $user = User::inRandomOrder()->first();

        return [
            'name' => $name,
            'amount' => $amount,
            'type' => $type,
            'category_id' => $categoryId,
            'transaction_date' => $date->format('Y-m-d'),
            'description' => fake()->boolean(60) ? fake()->sentence() : null,
            'created_by' => $user ? $user->id : User::factory(),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
