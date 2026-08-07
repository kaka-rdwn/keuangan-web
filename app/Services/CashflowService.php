<?php

namespace App\Services;

use App\Enums\CashflowType;
use App\Models\Cashflow;
use Illuminate\Database\Eloquent\Builder;

class CashflowService
{
    /**
     * Menghitung ringkasan agregat transaksi arus kas (total inflow, total outflow, dan saldo bersih)
     * berdasarkan query transaksi yang telah difilter.
     *
     * @param  Builder<Cashflow>  $query  Query model Cashflow dengan kriteria filter yang sudah diterapkan.
     * @return array{total_inflow: int, total_outflow: int, net_balance: int} Array berisi total pemasukan, total pengeluaran, dan saldo bersih.
     */
    public function calculateSummary(Builder $query): array
    {
        $summaryQuery = clone $query;
        $totalInflow = (int) (clone $summaryQuery)->where('type', CashflowType::INFLOW->value)->sum('amount');
        $totalOutflow = (int) (clone $summaryQuery)->where('type', CashflowType::OUTFLOW->value)->sum('amount');
        $netBalance = $totalInflow - $totalOutflow;

        return [
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'net_balance' => $netBalance,
        ];
    }
}
