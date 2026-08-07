<?php

namespace App\Http\Controllers;

use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Membuat instance DashboardController baru dengan menyuntikkan dependency analytics service.
     *
     * @param  DashboardAnalyticsService  $analyticsService  Layanan bisnis untuk kalkulasi analitik dan agregasi grafik.
     */
    public function __construct(
        protected DashboardAnalyticsService $analyticsService
    ) {}

    /**
     * Menampilkan halaman Dasbor Utama Keuangan dengan indikator ringkasan, grafik analitik, dan transaksi terbaru.
     *
     * @param  Request  $request  Objek HTTP request yang berisi opsi filter bulan dan tahun.
     * @return Response Komponen halaman Inertia untuk dashboard analitik.
     */
    public function __invoke(Request $request): Response
    {
        $currentMonth = (int) now()->format('n');
        $currentYear = (int) now()->format('Y');

        $month = (int) $request->input('month', $currentMonth);
        if ($month < 1 || $month > 12) {
            $month = $currentMonth;
        }

        $year = (int) $request->input('year', $currentYear);
        if ($year < 2000 || $year > 2100) {
            $year = $currentYear;
        }

        $metrics = $this->analyticsService->getMetrics($month, $year);
        $monthlyTrend = $this->analyticsService->getMonthlyTrend($month, $year);
        $categoryDistribution = $this->analyticsService->getCategoryDistribution($month, $year);
        $recentTransactions = $this->analyticsService->getRecentTransactions(5);

        return Inertia::render('dashboard', [
            'metrics' => $metrics,
            'monthly_trend' => $monthlyTrend,
            'category_distribution' => $categoryDistribution,
            'recent_transactions' => $recentTransactions,
            'filters' => [
                'month' => $month,
                'year' => $year,
            ],
        ]);
    }
}
