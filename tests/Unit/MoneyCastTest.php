<?php

use App\Casts\MoneyCast;
use App\Enums\CashflowType;
use App\Models\Cashflow;

test('money cast sanitizes formatted currency string inputs correctly and converts between cents and rupiah', function () {
    $cast = new MoneyCast;
    $cashflow = new Cashflow;

    expect($cast->set($cashflow, 'amount', '150.000', []))->toBe(15000000)
        ->and($cast->set($cashflow, 'amount', 'Rp 250.000', []))->toBe(25000000)
        ->and($cast->set($cashflow, 'amount', 500000, []))->toBe(50000000)
        ->and($cast->set($cashflow, 'amount', 75000.50, []))->toBe(7500050);

    expect($cast->get($cashflow, 'amount', 15000000, []))->toBe(150000)
        ->and($cast->get($cashflow, 'amount', 25000000, []))->toBe(250000)
        ->and($cast->get($cashflow, 'amount', 7500050, []))->toBe(75000.5);
});

test('cashflow type enum returns correct labels and colors', function () {
    expect(CashflowType::INFLOW->label())->toBe('Pemasukan')
        ->and(CashflowType::INFLOW->color())->toBe('success')
        ->and(CashflowType::OUTFLOW->label())->toBe('Pengeluaran')
        ->and(CashflowType::OUTFLOW->color())->toBe('danger');
});
