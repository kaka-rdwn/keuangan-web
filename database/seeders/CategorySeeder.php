<?php

namespace Database\Seeders;

use App\Enums\CashflowType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first() ?? User::first();

        $categories = [
            // INFLOW Categories
            [
                'name' => 'Gaji & Pendapatan Utama',
                'type' => CashflowType::INFLOW,
                'description' => 'Pendapatan rutinitas bulanan dan bonus',
            ],
            [
                'name' => 'Bonus & Komisi Proyek',
                'type' => CashflowType::INFLOW,
                'description' => 'Bonus tahunan, komisi penjualan, dan apresiasi kerja',
            ],
            [
                'name' => 'Investasi & Passive Income',
                'type' => CashflowType::INFLOW,
                'description' => 'Dividen, bunga deposito, dan capital gain',
            ],
            [
                'name' => 'Usaha Sampingan & Freelance',
                'type' => CashflowType::INFLOW,
                'description' => 'Pendapatan dari proyek sampingan dan konsultasi',
            ],

            // OUTFLOW Categories
            [
                'name' => 'Operasional & Utilitas',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Tagihan listrik, air, internet, dan kebersihan',
            ],
            [
                'name' => 'Makanan & Minuman',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Kebutuhan konsumsi harian dan belanja dapur',
            ],
            [
                'name' => 'Transportasi & BBM',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Biaya bahan bakar, servis kendaraan, tol, dan parkir',
            ],
            [
                'name' => 'Kesehatan & Perawatan',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Biaya obat, dokter, asuransi, dan perawatan diri',
            ],
            [
                'name' => 'Hiburan & Lifestyle',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Rekreasi akhir pekan, langganan streaming, dan hobi',
            ],
            [
                'name' => 'Pendidikan & Pengembangan Diri',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Buku, kursus online, pelatihan, dan seminar',
            ],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(
                ['name' => $data['name']],
                [
                    ...$data,
                    'created_by' => $admin?->id,
                ]
            );
        }
    }
}
