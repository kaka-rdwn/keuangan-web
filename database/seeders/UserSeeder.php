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

        // 1. Admin User (Hak Akses Penuh)
        $admin = User::firstOrCreate(
            ['email' => 'admin@keuangan.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin#1234'),
                'role_id' => $adminRole?->id,
                'email_verified_at' => now(),
            ]
        );

        // Sync seluruh permission ke Admin
        $allPermissionIds = Permission::pluck('id');
        $admin->permissions()->sync($allPermissionIds);

        // 2. Regular User
        $user = User::firstOrCreate(
            ['email' => 'kakaridwan@keuangan.test'],
            [
                'name' => 'Kaka Ridwan',
                'password' => Hash::make('User#1234'),
                'role_id' => $userRole?->id,
                'email_verified_at' => now(),
            ]
        );

        // Sync permission standar ke Regular User
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
