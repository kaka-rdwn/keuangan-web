<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $userRole = Role::where('name', 'User')->first();
        $allPermissionIds = Permission::pluck('id');

        // 1. Admin User Utama (admin@gmail.com)
        $adminGmail = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('password'),
                'role_id' => $adminRole?->id,
                'email_verified_at' => now(),
            ]
        );
        $adminGmail->permissions()->sync($allPermissionIds);

        // 2. Admin User Sekunder (admin@keuangan.test)
        $adminTest = User::firstOrCreate(
            ['email' => 'admin@keuangan.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin#1234'),
                'role_id' => $adminRole?->id,
                'email_verified_at' => now(),
            ]
        );
        $adminTest->permissions()->sync($allPermissionIds);

        // 3. Regular User
        $user = User::firstOrCreate(
            ['email' => 'user@keuangan.test'],
            [
                'name' => 'Pengguna',
                'password' => Hash::make('User#1234'),
                'role_id' => $userRole?->id,
                'email_verified_at' => now(),
            ]
        );

        $userPermissionIds = Permission::whereIn('name', [
            'cashflow.view',
            'cashflow.create',
            'cashflow.edit',
            'cashflow.delete',
            'category.view',
        ])->pluck('id');

        $user->permissions()->sync($userPermissionIds);
    }
}
