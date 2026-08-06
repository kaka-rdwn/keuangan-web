<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrator dengan akses penuh ke seluruh sistem']
        );

        Role::firstOrCreate(
            ['name' => 'User'],
            ['description' => 'Pengguna standar aplikasi manajemen keuangan']
        );
    }
}
