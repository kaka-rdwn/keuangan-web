<?php

use App\Enums\CashflowType;
use App\Models\Cashflow;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('cashflow index page can be rendered for user with view permission', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'cashflow.view'], ['display_name' => 'Lihat Cashflow']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $this->actingAs($user)
        ->get(route('cashflows.index'))
        ->assertOk();
});

test('user with create permission can store cashflow transaction', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'cashflow.create'], ['display_name' => 'Tambah Cashflow']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $category = Category::factory()->create([
        'type' => CashflowType::INFLOW,
    ]);

    $response = $this->actingAs($user)
        ->post(route('cashflows.store'), [
            'name' => 'Bonus Projek',
            'type' => 'inflow',
            'category_id' => $category->id,
            'amount' => '5.000.000',
            'transaction_date' => '2026-08-07',
            'description' => 'Bonus penyelesaian sistem keuangan',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cashflows', [
        'name' => 'Bonus Projek',
        'amount' => 500000000,
        'type' => CashflowType::INFLOW->value,
        'category_id' => $category->id,
    ]);
});

test('storing cashflow fails if category type does not match cashflow type', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'cashflow.create'], ['display_name' => 'Tambah Cashflow']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $outflowCategory = Category::factory()->create([
        'type' => CashflowType::OUTFLOW,
    ]);

    $response = $this->actingAs($user)
        ->post(route('cashflows.store'), [
            'name' => 'Salah Tipe Transaksi',
            'type' => 'inflow', // mismatch with category type (outflow)
            'category_id' => $outflowCategory->id,
            'amount' => 100000,
            'transaction_date' => '2026-08-07',
        ]);

    $response->assertSessionHasErrors(['category_id']);
});

test('user with edit permission can update cashflow transaction', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'cashflow.edit'], ['display_name' => 'Ubah Cashflow']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $category = Category::factory()->create(['type' => CashflowType::OUTFLOW]);

    $cashflow = Cashflow::factory()->create([
        'name' => 'Beli ATK',
        'type' => CashflowType::OUTFLOW,
        'category_id' => $category->id,
        'amount' => 150000,
    ]);

    $response = $this->actingAs($user)
        ->put(route('cashflows.update', $cashflow), [
            'name' => 'Beli Kertas & Tinta',
            'type' => 'outflow',
            'category_id' => $category->id,
            'amount' => 250000,
            'transaction_date' => '2026-08-07',
            'description' => 'Pembelian kertas HVS A4 dan tinta printer',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cashflows', [
        'id' => $cashflow->id,
        'name' => 'Beli Kertas & Tinta',
        'amount' => 25000000,
    ]);
});

test('user with delete permission can soft delete cashflow transaction', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'cashflow.delete'], ['display_name' => 'Hapus Cashflow']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $cashflow = Cashflow::factory()->create([
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('cashflows.destroy', $cashflow));

    $response->assertRedirect();
    $this->assertSoftDeleted('cashflows', [
        'id' => $cashflow->id,
    ]);
});
