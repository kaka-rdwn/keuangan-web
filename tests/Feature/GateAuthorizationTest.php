<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admin role bypasses all permission checks automatically', function () {
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    expect(Gate::forUser($admin)->allows('any.random.permission'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('cashflow.delete'))->toBeTrue();
});

test('regular user without permission is denied access', function () {
    $userRole = Role::firstOrCreate(['name' => 'User']);
    $user = User::factory()->create(['role_id' => $userRole->id]);

    expect(Gate::forUser($user)->allows('cashflow.delete'))->toBeFalse();
});

test('regular user with granted permission is allowed access', function () {
    $userRole = Role::firstOrCreate(['name' => 'User']);
    $permission = Permission::firstOrCreate(['name' => 'cashflow.view'], ['display_name' => 'View Cashflow']);
    $user = User::factory()->create(['role_id' => $userRole->id]);
    $user->permissions()->attach($permission->id);

    expect(Gate::forUser($user)->allows('cashflow.view'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('cashflow.delete'))->toBeFalse();
});
