<?php

use App\Enums\CashflowType;
use App\Models\Cashflow;
use App\Models\Category;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard and see metrics', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create(['type' => CashflowType::INFLOW]);
    Cashflow::factory()->create([
        'type' => CashflowType::INFLOW,
        'amount' => 5000000,
        'category_id' => $category->id,
        'transaction_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('metrics')
            ->has('monthly_trend')
            ->has('category_distribution')
            ->has('recent_transactions')
        );
});
