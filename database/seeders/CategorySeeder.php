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
        $admin = User::where('email', 'admin@keuangan.test')->first();

        $categories = [
            [
                'name' => 'Gaji & Pendapatan Utama',
                'type' => CashflowType::INFLOW,
                'description' => 'Pendapatan rutinitas bulanan dan bonus',
            ],
            [
                'name' => 'Investasi & Passive Income',
                'type' => CashflowType::INFLOW,
                'description' => 'Dividen, bunga deposito, dan capital gain',
            ],
            [
                'name' => 'Operasional & Listrik',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Tagihan listrik, air, internet, dan kebersihan',
            ],
            [
                'name' => 'Makanan & Minuman',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Kebutuhan konsumsi harian dan konsumsi kantor',
            ],
            [
                'name' => 'Transportasi & BBM',
                'type' => CashflowType::OUTFLOW,
                'description' => 'Biaya bahan bakar, servis kendaraan, dan perjalanan',
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
