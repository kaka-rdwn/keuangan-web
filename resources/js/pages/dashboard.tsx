import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowDownLeft,
    ArrowRight,
    ArrowUpRight,
    PieChart as PieIcon,
    TrendingDown,
    TrendingUp,
    Wallet,
} from 'lucide-react';
import { useMemo } from 'react';
import {
    Bar,
    BarChart,
    Cell,
    Legend,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as cashflowsIndex } from '@/routes/cashflows';
import type { DashboardProps } from '@/types/dashboard';

const formatRupiah = (num: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(num);
};

const MONTH_NAMES = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
];

export default function Dashboard({
    metrics,
    monthly_trend,
    category_distribution,
    recent_transactions,
    filters,
}: DashboardProps) {
    const yearsList = useMemo(() => {
        const currentY = new Date().getFullYear();

        return Array.from({ length: 5 }, (_, i) => currentY - 2 + i);
    }, []);

    const handleFilterChange = (key: 'month' | 'year', val: string) => {
        const nextQuery = {
            month: key === 'month' ? val : filters.month.toString(),
            year: key === 'year' ? val : filters.year.toString(),
        };

        router.get('/dashboard', nextQuery, { preserveState: true });
    };

    return (
        <>
            <Head title="Dashboard Keuangan" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
                {/* Header & Month/Year Filter */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Dashboard Keuangan</h1>
                        <p className="text-sm text-muted-foreground">
                            Ringkasan performa finansial, tren arus kas, dan analisis pengeluaran Anda.
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Select
                            value={filters.month.toString()}
                            onValueChange={(val) => handleFilterChange('month', val)}
                        >
                            <SelectTrigger className="w-36">
                                <SelectValue placeholder="Pilih Bulan" />
                            </SelectTrigger>
                            <SelectContent>
                                {MONTH_NAMES.map((name, idx) => (
                                    <SelectItem key={idx + 1} value={(idx + 1).toString()}>
                                        {name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={filters.year.toString()}
                            onValueChange={(val) => handleFilterChange('year', val)}
                        >
                            <SelectTrigger className="w-28">
                                <SelectValue placeholder="Pilih Tahun" />
                            </SelectTrigger>
                            <SelectContent>
                                {yearsList.map((y) => (
                                    <SelectItem key={y} value={y.toString()}>
                                        {y}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {/* Section 1: Summary Cards Grid */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Card 1: Inflow */}
                    <Card className="border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                Total Pemasukan
                            </CardTitle>
                            <div className="rounded-full bg-emerald-100 p-2 dark:bg-emerald-900/50">
                                <ArrowDownLeft className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-700 dark:text-emerald-300">
                                {formatRupiah(metrics.total_inflow)}
                            </div>
                            <div className="flex items-center gap-1 text-xs pt-1 text-muted-foreground">
                                {metrics.inflow_growth >= 0 ? (
                                    <span className="flex items-center text-emerald-600 dark:text-emerald-400 font-medium">
                                        <TrendingUp className="mr-0.5 h-3 w-3" />+{metrics.inflow_growth}%
                                    </span>
                                ) : (
                                    <span className="flex items-center text-rose-600 dark:text-rose-400 font-medium">
                                        <TrendingDown className="mr-0.5 h-3 w-3" />{metrics.inflow_growth}%
                                    </span>
                                )}
                                <span>vs bulan sebelumnya</span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 2: Outflow */}
                    <Card className="border-rose-200 bg-rose-50/40 dark:border-rose-900/50 dark:bg-rose-950/20">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-semibold text-rose-800 dark:text-rose-300">
                                Total Pengeluaran
                            </CardTitle>
                            <div className="rounded-full bg-rose-100 p-2 dark:bg-rose-900/50">
                                <ArrowUpRight className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-rose-700 dark:text-rose-300">
                                {formatRupiah(metrics.total_outflow)}
                            </div>
                            <div className="flex items-center gap-1 text-xs pt-1 text-muted-foreground">
                                {metrics.outflow_growth <= 0 ? (
                                    <span className="flex items-center text-emerald-600 dark:text-emerald-400 font-medium">
                                        <TrendingDown className="mr-0.5 h-3 w-3" />{metrics.outflow_growth}%
                                    </span>
                                ) : (
                                    <span className="flex items-center text-rose-600 dark:text-rose-400 font-medium">
                                        <TrendingUp className="mr-0.5 h-3 w-3" />+{metrics.outflow_growth}%
                                    </span>
                                )}
                                <span>vs bulan sebelumnya</span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 3: Net Balance */}
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-semibold text-foreground">
                                Arus Kas Bersih
                            </CardTitle>
                            <div className="rounded-full bg-muted p-2">
                                <Wallet className="h-5 w-5 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div
                                className={`text-2xl font-bold ${
                                    metrics.net_balance >= 0
                                        ? 'text-foreground'
                                        : 'text-rose-600 dark:text-rose-400'
                                }`}
                            >
                                {formatRupiah(metrics.net_balance)}
                            </div>
                            <p className="text-xs text-muted-foreground pt-1">
                                {metrics.net_balance >= 0 ? 'Surplus bulan ini' : 'Defisit bulan ini'}
                            </p>
                        </CardContent>
                    </Card>

                    {/* Card 4: Top Expense Category */}
                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-semibold text-foreground">
                                Pengeluaran Terbesar
                            </CardTitle>
                            <div className="rounded-full bg-muted p-2">
                                <PieIcon className="h-5 w-5 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            {metrics.top_expense_category ? (
                                <>
                                    <div className="text-lg font-bold text-foreground truncate">
                                        {metrics.top_expense_category.name}
                                    </div>
                                    <p className="text-xs font-semibold text-rose-600 dark:text-rose-400 pt-1">
                                        {formatRupiah(metrics.top_expense_category.amount)}
                                    </p>
                                </>
                            ) : (
                                <>
                                    <div className="text-lg font-semibold text-muted-foreground">-</div>
                                    <p className="text-xs text-muted-foreground pt-1">Belum ada data pengeluaran</p>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Section 2: Visual Charts Grid */}
                <div className="grid gap-6 lg:grid-cols-7">
                    {/* Left: 12-Month Bar Chart */}
                    <Card className="lg:col-span-4 border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">
                                Tren Arus Kas (12 Bulan Terakhir)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[300px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={monthly_trend} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                                        <XAxis dataKey="label" stroke="#888888" fontSize={11} tickLine={false} axisLine={false} />
                                        <YAxis
                                            stroke="#888888"
                                            fontSize={10}
                                            tickLine={false}
                                            axisLine={false}
                                            tickFormatter={(v: number) => `Rp${(v / 1000000).toFixed(0)}M`}
                                        />
                                        <Tooltip
                                            formatter={(value: unknown) => [formatRupiah(Number(value || 0))]}
                                            contentStyle={{
                                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                                borderRadius: '8px',
                                                border: '1px solid #e2e8f0',
                                                fontSize: '12px',
                                            }}
                                        />
                                        <Legend wrapperStyle={{ fontSize: '12px', paddingTop: '10px' }} />
                                        <Bar dataKey="inflow" name="Pemasukan" fill="#10b981" radius={[4, 4, 0, 0]} />
                                        <Bar dataKey="outflow" name="Pengeluaran" fill="#ef4444" radius={[4, 4, 0, 0]} />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Right: Donut Chart Category Distribution */}
                    <Card className="lg:col-span-3 border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">
                                Pengeluaran Per Kategori
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col items-center justify-center">
                            {category_distribution.length > 0 ? (
                                <div className="h-[300px] w-full flex flex-col items-center">
                                    <ResponsiveContainer width="100%" height={200}>
                                        <PieChart>
                                            <Pie
                                                data={category_distribution}
                                                cx="50%"
                                                cy="50%"
                                                innerRadius={55}
                                                outerRadius={80}
                                                paddingAngle={3}
                                                dataKey="amount"
                                            >
                                                {category_distribution.map((entry, index) => (
                                                    <Cell key={`cell-${index}`} fill={entry.color} />
                                                ))}
                                            </Pie>
                                            <Tooltip formatter={(value: unknown) => [formatRupiah(Number(value || 0)), 'Pengeluaran']} />
                                        </PieChart>
                                    </ResponsiveContainer>
                                    <div className="w-full space-y-1 overflow-y-auto max-h-[100px] text-xs pt-2">
                                        {category_distribution.slice(0, 4).map((item, idx) => (
                                            <div key={idx} className="flex items-center justify-between">
                                                <div className="flex items-center gap-2 truncate">
                                                    <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: item.color }} />
                                                    <span className="truncate text-muted-foreground">{item.name}</span>
                                                </div>
                                                <span className="font-semibold text-foreground">{item.percentage}%</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ) : (
                                <div className="flex h-[300px] items-center justify-center text-sm text-muted-foreground">
                                    Belum ada pengeluaran pada periode ini.
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Section 3: Recent Transactions */}
                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle className="text-base font-semibold">Transaksi Terbaru</CardTitle>
                            <p className="text-xs text-muted-foreground">5 transaksi keuangan yang baru saja dicatat.</p>
                        </div>
                        <Button variant="ghost" size="sm" asChild className="gap-1 text-xs">
                            <Link href={cashflowsIndex()}>
                                Lihat Semua
                                <ArrowRight className="h-3.5 w-3.5" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-2.5 font-semibold">Tanggal</th>
                                        <th className="px-4 py-2.5 font-semibold">Nama / Deskripsi</th>
                                        <th className="px-4 py-2.5 font-semibold">Kategori</th>
                                        <th className="px-4 py-2.5 font-semibold">Tipe</th>
                                        <th className="px-4 py-2.5 text-right font-semibold">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                                    {recent_transactions.length > 0 ? (
                                        recent_transactions.map((item) => (
                                            <tr key={item.id} className="hover:bg-muted/30 transition-colors">
                                                <td className="px-4 py-2.5 text-xs font-mono text-muted-foreground whitespace-nowrap">
                                                    {item.transaction_date ?? '-'}
                                                </td>
                                                <td className="px-4 py-2.5">
                                                    <div className="font-medium text-foreground">{item.name}</div>
                                                </td>
                                                <td className="px-4 py-2.5 text-muted-foreground text-xs">
                                                    {item.category?.name || '-'}
                                                </td>
                                                <td className="px-4 py-2.5">
                                                    {item.type === 'inflow' ? (
                                                        <Badge className="bg-emerald-100 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800 text-[10px] py-0">
                                                            Pemasukan
                                                        </Badge>
                                                    ) : (
                                                        <Badge className="bg-rose-100 text-rose-800 hover:bg-rose-100 dark:bg-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800 text-[10px] py-0">
                                                            Pengeluaran
                                                        </Badge>
                                                    )}
                                                </td>
                                                <td className="px-4 py-2.5 text-right font-semibold whitespace-nowrap">
                                                    <span
                                                        className={
                                                            item.type === 'inflow'
                                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                                : 'text-rose-600 dark:text-rose-400'
                                                        }
                                                    >
                                                        {item.type === 'inflow' ? '+' : '-'} {formatRupiah(item.amount)}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-6 text-center text-muted-foreground text-xs">
                                                Belum ada transaksi recorded.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
    ],
};
