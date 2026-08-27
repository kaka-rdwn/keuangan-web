<?php

namespace App\Services;

use App\Enums\CashflowType;
use App\Models\Cashflow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * Palet warna Tailwind/HEX untuk grafik lingkaran (Donut Chart) kategori pengeluaran.
     *
     * @var array<int, string>
     */
    protected array $chartColors = [
        '#ef4444', // Red / Rose
        '#f97316', // Orange
        '#eab308', // Yellow
        '#8b5cf6', // Violet / Purple
        '#ec4899', // Pink
        '#06b6d4', // Cyan
        '#10b981', // Emerald
        '#3b82f6', // Blue
        '#64748b', // Slate
    ];

    /**
     * Menghitung metric summary keuangan bulanan beserta pertumbuhan Month-over-Month (MoM).
     *
     * @param  int  $month  Bulan aktif (1-12).
     * @param  int  $year  Tahun aktif (misal 2026).
     * @return array{
     *     total_inflow: int,
     *     total_outflow: int,
     *     net_balance: int,
     *     inflow_growth: float,
     *     outflow_growth: float,
     *     top_expense_category: array{name: string, amount: int}|null
     * } Array data indikator keuangan bulanan.
     */
    public function getMetrics(int $month, int $year): array
    {
        $currentStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $currentEnd = (clone $currentStart)->endOfMonth();

        $prevStart = (clone $currentStart)->subMonth()->startOfMonth();
        $prevEnd = (clone $prevStart)->endOfMonth();

        // Metric Bulan Ini
        $totalInflowCents = (int) Cashflow::query()
            ->where('type', CashflowType::INFLOW->value)
            ->whereBetween('transaction_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->sum('amount');

        $totalOutflowCents = (int) Cashflow::query()
            ->where('type', CashflowType::OUTFLOW->value)
            ->whereBetween('transaction_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->sum('amount');

        $totalInflow = (int) round($totalInflowCents / 100);
        $totalOutflow = (int) round($totalOutflowCents / 100);
        $netBalance = $totalInflow - $totalOutflow;

        // Metric Bulan Lalu (Untuk Month-over-Month Growth)
        $prevInflowCents = (int) Cashflow::query()
            ->where('type', CashflowType::INFLOW->value)
            ->whereBetween('transaction_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->sum('amount');

        $prevOutflowCents = (int) Cashflow::query()
            ->where('type', CashflowType::OUTFLOW->value)
            ->whereBetween('transaction_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->sum('amount');

        $prevInflow = (int) round($prevInflowCents / 100);
        $prevOutflow = (int) round($prevOutflowCents / 100);

        $inflowGrowth = $prevInflow > 0
            ? round((($totalInflow - $prevInflow) / $prevInflow) * 100, 1)
            : 0.0;

        $outflowGrowth = $prevOutflow > 0
            ? round((($totalOutflow - $prevOutflow) / $prevOutflow) * 100, 1)
            : 0.0;

        // Top Expense Category Bulan Ini
        $outflowItems = Cashflow::query()
            ->where('type', CashflowType::OUTFLOW->value)
            ->whereBetween('transaction_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->whereNotNull('category_id')
            ->with('category')
            ->get();

        $topExpenseCategory = null;
        if ($outflowItems->isNotEmpty()) {
            $grouped = $outflowItems->groupBy('category_id')->map(function (Collection $group) {
                $category = $group->first()?->category;

                return [
                    'name' => $category ? $category->name : 'Tanpa Kategori',
                    'amount' => (int) $group->sum('amount'),
                ];
            })->sortByDesc('amount')->first();

            if ($grouped) {
                $topExpenseCategory = $grouped;
            }
        }

        return [
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'net_balance' => $netBalance,
            'inflow_growth' => $inflowGrowth,
            'outflow_growth' => $outflowGrowth,
            'top_expense_category' => $topExpenseCategory,
        ];
    }

    /**
     * Mengambil daftar tahun unik transaksi yang ada di database ditambah tahun berjalan.
     *
     * @return array<int, int> Array daftar tahun terurut secara descending.
     */
    public function getAvailableYears(): array
    {
        $driver = DB::getDriverName();
        $yearExpression = match ($driver) {
            'sqlite' => "strftime('%Y', transaction_date)",
            'pgsql' => 'EXTRACT(YEAR FROM transaction_date)',
            default => 'YEAR(transaction_date)',
        };

        $dbYears = Cashflow::query()
            ->selectRaw("{$yearExpression} as year")
            ->whereNotNull('transaction_date')
            ->distinct()
            ->pluck('year')
            ->map(fn($y) => (int) $y)
            ->filter(fn($y) => $y > 0)
            ->toArray();

        $currentYear = (int) now()->year;

        return collect(array_merge($dbYears, [$currentYear]))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /**
     * Menghitung tren perbandingan Pemasukan vs Pengeluaran (Per Bulan / Per Kuartal) pada tahun tertentu.
     *
     * @param  int  $year  Tahun aktif.
     * @param  string  $period  Tipe periode agregasi ('monthly' atau 'quarterly').
     * @return array<int, array{month_year?: string, period_key: string, label: string, inflow: int, outflow: int}> Array data tren grafik.
     */
    public function getMonthlyTrend(int $year, string $period = 'monthly'): array
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfMonth();

        $cashflows = Cashflow::query()
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        if ($period === 'quarterly') {
            $quarterDefs = [
                'Q1' => 'Kuartal 1',
                'Q2' => 'Kuartal 2',
                'Q3' => 'Kuartal 3',
                'Q4' => 'Kuartal 4',
            ];

            $trendMap = [
                'Q1' => ['inflow' => 0, 'outflow' => 0],
                'Q2' => ['inflow' => 0, 'outflow' => 0],
                'Q3' => ['inflow' => 0, 'outflow' => 0],
                'Q4' => ['inflow' => 0, 'outflow' => 0],
            ];

            foreach ($cashflows as $item) {
                $month = (int) Carbon::parse($item->transaction_date)->month;
                $qKey = match (true) {
                    $month <= 3 => 'Q1',
                    $month <= 6 => 'Q2',
                    $month <= 9 => 'Q3',
                    default => 'Q4',
                };
                $type = $item->type->value;
                $amount = (int) $item->amount;

                if ($type === CashflowType::INFLOW->value) {
                    $trendMap[$qKey]['inflow'] += $amount;
                } else {
                    $trendMap[$qKey]['outflow'] += $amount;
                }
            }

            $result = [];
            foreach ($quarterDefs as $key => $label) {
                $result[] = [
                    'period_key' => $key,
                    'label' => $label,
                    'inflow' => $trendMap[$key]['inflow'],
                    'outflow' => $trendMap[$key]['outflow'],
                ];
            }

            return $result;
        }

        $trendMap = [];
        foreach ($cashflows as $item) {
            $monthKey = Carbon::parse($item->transaction_date)->format('Y-m');
            $type = $item->type->value;
            $amount = (int) $item->amount;

            if (! isset($trendMap[$monthKey])) {
                $trendMap[$monthKey] = ['inflow' => 0, 'outflow' => 0];
            }

            if ($type === CashflowType::INFLOW->value) {
                $trendMap[$monthKey]['inflow'] += $amount;
            } else {
                $trendMap[$monthKey]['outflow'] += $amount;
            }
        }

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $cursor = Carbon::createFromDate($year, $m, 1);
            $monthKey = $cursor->format('Y-m');
            $label = $cursor->translatedFormat('M Y');

            $inflow = $trendMap[$monthKey]['inflow'] ?? 0;
            $outflow = $trendMap[$monthKey]['outflow'] ?? 0;

            $result[] = [
                'month_year' => $monthKey,
                'period_key' => $monthKey,
                'label' => $label,
                'inflow' => $inflow,
                'outflow' => $outflow,
            ];
        }

        return $result;
    }

    /**
     * Menghitung proporsi pengeluaran per kategori pada bulan dan tahun tertentu untuk Donut Chart.
     *
     * @param  int  $month  Bulan aktif.
     * @param  int  $year  Tahun aktif.
     * @return array<int, array{name: string, amount: int, percentage: float, color: string}> Array rincian distribusi pengeluaran.
     */
    public function getCategoryDistribution(int $month, int $year): array
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $cashflows = Cashflow::query()
            ->where('type', CashflowType::OUTFLOW->value)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->with('category')
            ->get();

        if ($cashflows->isEmpty()) {
            return [];
        }

        $grandTotal = (int) $cashflows->sum('amount');
        if ($grandTotal === 0) {
            return [];
        }

        $grouped = $cashflows->groupBy('category_id')->map(function (Collection $group) {
            $category = $group->first()?->category;

            return [
                'name' => $category ? $category->name : 'Tanpa Kategori',
                'amount' => (int) $group->sum('amount'),
            ];
        })->sortByDesc('amount');

        $result = [];
        $colorIndex = 0;
        $colorsCount = count($this->chartColors);

        foreach ($grouped as $item) {
            $amount = $item['amount'];
            $percentage = round(($amount / $grandTotal) * 100, 1);
            $color = $this->chartColors[$colorIndex % $colorsCount];

            $result[] = [
                'name' => $item['name'],
                'amount' => $amount,
                'percentage' => $percentage,
                'color' => $color,
            ];

            $colorIndex++;
        }

        return $result;
    }

    /**
     * Mengambil daftar transaksi arus kas terbaru.
     *
     * @param  int  $limit  Jumlah maksimal transaksi yang diambil (default: 5).
     * @return Collection<int, Cashflow> Koleksi data transaksi terbaru beserta relasi kategori.
     */
    public function getRecentTransactions(int $limit = 5): Collection
    {
        return Cashflow::query()
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->take($limit)
            ->get();
    }
}
