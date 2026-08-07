<?php

use App\Enums\CashflowType;
use App\Models\Cashflow;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('category index page can be rendered for user with view permission', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'category.view'], ['display_name' => 'Lihat Kategori']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $this->actingAs($user)
        ->get(route('categories.index'))
        ->assertOk();
});

test('user with manage permission can create a category with type', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'category.manage'], ['display_name' => 'Kelola Kategori']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $response = $this->actingAs($user)
        ->post(route('categories.store'), [
            'name' => 'Investasi Saham',
            'type' => 'inflow',
            'description' => 'Pendapatan dividen dan capital gain',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('categories', [
        'name' => 'Investasi Saham',
        'type' => CashflowType::INFLOW->value,
    ]);
});

test('user with manage permission can update a category type', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'category.manage'], ['display_name' => 'Kelola Kategori']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $category = Category::factory()->create([
        'name' => 'Tagihan Listrik',
        'type' => CashflowType::OUTFLOW,
    ]);

    $response = $this->actingAs($user)
        ->put(route('categories.update', $category), [
            'name' => 'Tagihan Listrik & Air',
            'type' => 'outflow',
            'description' => 'Pembayaran PLN dan PDAM',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Tagihan Listrik & Air',
        'type' => CashflowType::OUTFLOW->value,
    ]);
});

test('category deletion is prevented if associated cashflows exist', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'category.manage'], ['display_name' => 'Kelola Kategori']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $category = Category::factory()->create([
        'name' => 'Gaji',
        'type' => CashflowType::INFLOW,
    ]);

    Cashflow::factory()->create([
        'category_id' => $category->id,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertRedirect();
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'deleted_at' => null,
    ]);
});

test('user with manage permission can delete category without cashflows', function () {
    $role = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'category.manage'], ['display_name' => 'Kelola Kategori']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    $category = Category::factory()->create([
        'name' => 'Uang Saku',
        'type' => CashflowType::INFLOW,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertRedirect();
    $this->assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);
});
