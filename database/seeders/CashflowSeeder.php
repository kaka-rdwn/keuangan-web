<?php

namespace Database\Seeders;

use App\Enums\CashflowType;
use App\Models\Cashflow;
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

        $cashflows = [
            [
                'name' => 'Gaji Bulanan',
                'amount' => 15000000,
                'type' => CashflowType::INFLOW,
                'description' => 'Pemasukan gaji bulanan utama',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Belanja Operasional',
                'amount' => 2500000,
                'type' => CashflowType::OUTFLOW,
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
