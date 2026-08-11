<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Cashflow
            ['name' => 'cashflow.view', 'display_name' => 'Lihat Cashflow', 'description' => 'Dapat melihat daftar dan detail transaksi'],
            ['name' => 'cashflow.create', 'display_name' => 'Tambah Cashflow', 'description' => 'Dapat mencatat transaksi baru'],
            ['name' => 'cashflow.edit', 'display_name' => 'Ubah Cashflow', 'description' => 'Dapat memperbarui data transaksi'],
            ['name' => 'cashflow.delete', 'display_name' => 'Hapus Cashflow', 'description' => 'Dapat menghapus data transaksi'],

            // Category
            ['name' => 'category.view', 'display_name' => 'Lihat Kategori', 'description' => 'Dapat melihat daftar dan detail kategori'],
            ['name' => 'category.create', 'display_name' => 'Tambah Kategori', 'description' => 'Dapat membuat kategori baru'],
            ['name' => 'category.edit', 'display_name' => 'Ubah Kategori', 'description' => 'Dapat memperbarui data kategori'],
            ['name' => 'category.delete', 'display_name' => 'Hapus Kategori', 'description' => 'Dapat menghapus kategori'],

            // User
            ['name' => 'user.manage', 'display_name' => 'Kelola Pengguna', 'description' => 'Dapat mengelola akun pengguna dan peran'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
