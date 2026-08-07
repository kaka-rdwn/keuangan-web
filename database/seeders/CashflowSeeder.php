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
        $admin = User::where('email', 'admin@keuangan.test')->first();

        if (! $admin) {
            return;
        }

        $salaryCat = Category::where('name', 'like', '%Gaji%')->first();
        $operasionalCat = Category::where('name', 'like', '%Operasional%')->first();

        $cashflows = [
            [
                'name' => 'Gaji Bulanan Utama',
                'amount' => 15000000 * 100,
                'type' => CashflowType::INFLOW,
                'category_id' => $salaryCat?->id,
                'transaction_date' => now()->startOfMonth()->toDateString(),
                'description' => 'Pemasukan gaji bulanan utama',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Belanja Operasional Kantor',
                'amount' => 2500000 * 100,
                'type' => CashflowType::OUTFLOW,
                'category_id' => $operasionalCat?->id,
                'transaction_date' => now()->toDateString(),
                'description' => 'Pengeluaran kebutuhan operasional',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($cashflows as $data) {
            Cashflow::firstOrCreate(
                ['name' => $data['name'], 'created_by' => $data['created_by']],
                $data
            );
        }
    }
}
