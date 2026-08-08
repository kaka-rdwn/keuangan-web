<?php

use App\Mail\UserCreatedMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

test('users index page can be rendered for admin user', function () {
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $user = User::factory()->create(['role_id' => $adminRole->id]);

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk();
});

test('regular user without user.manage permission cannot access users index', function () {
    $userRole = Role::firstOrCreate(['name' => 'User']);
    $user = User::factory()->create(['role_id' => $userRole->id]);

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('admin can store new user with null email_verified_at and trigger credential email', function () {
    Mail::fake();

    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    Role::firstOrCreate(['name' => 'User']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $response = $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Staf Baru',
            'email' => 'stafbaru@keuangan.test',
            'role' => 'User',
            'password' => 'Secret#1234',
        ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'name' => 'Staf Baru',
        'email' => 'stafbaru@keuangan.test',
        'email_verified_at' => null,
    ]);

    Mail::assertSent(UserCreatedMail::class, function ($mail) {
        return $mail->hasTo('stafbaru@keuangan.test');
    });
});

test('user with null email_verified_at gets verified automatically upon first login', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
        'password' => Hash::make('Password#123'),
    ]);

    expect($user->email_verified_at)->toBeNull();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'Password#123',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});

test('admin can update user details and role', function () {
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $userRole = Role::firstOrCreate(['name' => 'User']);

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $targetUser = User::factory()->create([
        'name' => 'Nama Lama',
        'email' => 'oldemail@keuangan.test',
        'role_id' => $userRole->id,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('users.update', $targetUser), [
            'name' => 'Nama Baru',
            'email' => 'newemail@keuangan.test',
            'role' => 'User',
        ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'Nama Baru',
        'email' => 'newemail@keuangan.test',
    ]);
});

test('user cannot delete their own account via user management', function () {
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $response = $this->actingAs($admin)
        ->delete(route('users.destroy', $admin));

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('admin can delete other user account', function () {
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $userRole = Role::firstOrCreate(['name' => 'User']);

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $targetUser = User::factory()->create(['role_id' => $userRole->id]);

    $response = $this->actingAs($admin)
        ->delete(route('users.destroy', $targetUser));

    $response->assertRedirect(route('users.index'));
    $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
});
