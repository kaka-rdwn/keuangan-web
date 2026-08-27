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
        ->assertInertia(
            fn ($page) => $page
                ->component('dashboard')
                ->has('metrics')
                ->has('monthly_trend')
                ->has('category_distribution')
                ->has('recent_transactions')
                ->has('available_years')
                ->where('selected_year', (int) now()->year)
        );
});

test('dashboard provides available_years sorted descending excluding soft deleted cashflows', function () {
    $user = User::factory()->create();

    Cashflow::factory()->create([
        'transaction_date' => '2024-05-10',
    ]);
    Cashflow::factory()->create([
        'transaction_date' => '2025-08-15',
    ]);
    $deleted = Cashflow::factory()->create([
        'transaction_date' => '2022-01-01',
    ]);
    $deleted->delete();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $availableYears = $response->inertiaProps('available_years');

    expect($availableYears)->toBeArray()
        ->and($availableYears)->toContain(2024)
        ->and($availableYears)->toContain(2025)
        ->and($availableYears)->toContain((int) now()->year)
        ->and($availableYears)->not->toContain(2022);

    $sortedYears = $availableYears;
    rsort($sortedYears);
    expect($availableYears)->toEqual($sortedYears);
});

test('dashboard filters monthly trend by selected year parameter', function () {
    $user = User::factory()->create();

    Cashflow::factory()->create([
        'type' => CashflowType::INFLOW,
        'amount' => 2000000,
        'transaction_date' => '2024-03-15',
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard', ['year' => 2024]));

    $response->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('dashboard')
                ->where('selected_year', 2024)
                ->has('monthly_trend', 12)
                ->where('monthly_trend.2.month_year', '2024-03')
                ->where('monthly_trend.2.inflow', 2000000)
        );
});

test('dashboard renders 12 months with nominal 0 when selected year has no transactions', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard', ['year' => 2020]));

    $response->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('dashboard')
                ->where('selected_year', 2020)
                ->where('selected_period', 'monthly')
                ->has('monthly_trend', 12)
                ->where('monthly_trend.0.month_year', '2020-01')
                ->where('monthly_trend.0.inflow', 0)
                ->where('monthly_trend.0.outflow', 0)
                ->where('monthly_trend.11.month_year', '2020-12')
                ->where('monthly_trend.11.inflow', 0)
                ->where('monthly_trend.11.outflow', 0)
        );
});

test('dashboard aggregates trend by quarterly period when period parameter is quarterly', function () {
    $user = User::factory()->create();

    // Q1 transaction (Feb)
    Cashflow::factory()->create([
        'type' => CashflowType::INFLOW,
        'amount' => 1000000,
        'transaction_date' => '2024-02-10',
    ]);
    // Q1 transaction (Mar)
    Cashflow::factory()->create([
        'type' => CashflowType::INFLOW,
        'amount' => 1500000,
        'transaction_date' => '2024-03-20',
    ]);
    // Q3 transaction (Aug)
    Cashflow::factory()->create([
        'type' => CashflowType::OUTFLOW,
        'amount' => 800000,
        'transaction_date' => '2024-08-05',
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard', ['year' => 2024, 'period' => 'quarterly']));

    $response->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('dashboard')
                ->where('selected_year', 2024)
                ->where('selected_period', 'quarterly')
                ->has('chart_data', 4)
                ->where('chart_data.0.period_key', 'Q1')
                ->where('chart_data.0.label', 'Kuartal 1')
                ->where('chart_data.0.inflow', 2500000)
                ->where('chart_data.0.outflow', 0)
                ->where('chart_data.1.period_key', 'Q2')
                ->where('chart_data.1.label', 'Kuartal 2')
                ->where('chart_data.1.inflow', 0)
                ->where('chart_data.2.period_key', 'Q3')
                ->where('chart_data.2.label', 'Kuartal 3')
                ->where('chart_data.2.outflow', 800000)
                ->where('chart_data.3.period_key', 'Q4')
                ->where('chart_data.3.label', 'Kuartal 4')
        );
});
