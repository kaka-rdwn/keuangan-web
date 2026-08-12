import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowDownLeft,
    ArrowUpRight,
    Edit2,
    Plus,
    Search,
    Trash2,
    Wallet,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Can } from '@/components/can';
import { SortableHeader } from '@/components/sortable-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatPaginationLabel } from '@/lib/utils';
import {
    create as cashflowsCreate,
    destroy as cashflowsDestroy,
    edit as cashflowsEdit,
    index as cashflowsIndex,
} from '@/routes/cashflows';
import type { Cashflow, CashflowSummary, PaginatedCashflows } from '@/types/cashflow';
import type { Category } from '@/types/category';

interface Props {
    cashflows: PaginatedCashflows;
    categories: Category[];
    summary: CashflowSummary;
    filters: {
        search?: string;
        type?: string;
        category_id?: string;
        date_from?: string;
        date_to?: string;
        sort_by?: string;
        sort_dir?: string;
        sort?: string;
        direction?: string;
    };
    can?: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}

const formatRupiah = (num: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(num);
};

export default function CashflowsIndex({
    cashflows,
    categories,
    summary,
    filters,
}: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [selectedType, setSelectedType] = useState(filters.type ?? 'all');
    const [selectedCategory, setSelectedCategory] = useState(filters.category_id ?? 'all');
    const [dateFrom, setDateFrom] = useState(filters.date_from ?? '');
    const [dateTo, setDateTo] = useState(filters.date_to ?? '');
    const sortBy = filters.sort_by || filters.sort || 'transaction_date';
    const sortDir = filters.sort_dir || filters.direction || 'desc';
    const isFirstRender = useRef(true);

    const [deletingCashflow, setDeletingCashflow] = useState<Cashflow | null>(null);

    const handleSort = (column: string) => {
        const isSameColumn = sortBy === column;
        const nextDir = isSameColumn ? (sortDir === 'asc' ? 'desc' : 'asc') : 'asc';

        router.get(
            cashflowsIndex.url({
                query: {
                    search: search || undefined,
                    type: selectedType !== 'all' ? selectedType : undefined,
                    category_id: selectedCategory !== 'all' ? selectedCategory : undefined,
                    date_from: dateFrom || undefined,
                    date_to: dateTo || undefined,
                    sort_by: column,
                    sort_dir: nextDir,
                },
            }),
            {},
            { preserveState: true, replace: true }
        );
    };

    // Debounced search effect
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timer = setTimeout(() => {
            router.get(
                cashflowsIndex.url({
                    query: {
                        search: search || undefined,
                        type: selectedType !== 'all' ? selectedType : undefined,
                        category_id: selectedCategory !== 'all' ? selectedCategory : undefined,
                        date_from: dateFrom || undefined,
                        date_to: dateTo || undefined,
                        sort_by: sortBy,
                        sort_dir: sortDir,
                    },
                }),
                {},
                { preserveState: true, replace: true }
            );
        }, 350);

        return () => clearTimeout(timer);
    }, [search, selectedType, selectedCategory, dateFrom, dateTo, sortBy, sortDir]);

    const handleFilterChange = (key: string, value: string) => {
        if (key === 'type') {
            setSelectedType(value);
        }

        if (key === 'category_id') {
            setSelectedCategory(value);
        }

        if (key === 'date_from') {
            setDateFrom(value);
        }

        if (key === 'date_to') {
            setDateTo(value);
        }
    };

    const handleDeleteConfirm = () => {
        if (!deletingCashflow) {
            return;
        }

        router.delete(cashflowsDestroy.url(deletingCashflow.id), {
            onSuccess: () => setDeletingCashflow(null),
        });
    };

    return (
        <>
            <Head title="Pencatatan Arus Kas" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-emerald-200 bg-emerald-50/50 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                Total Pemasukan (Inflow)
                            </CardTitle>
                            <div className="rounded-full bg-emerald-100 p-2 dark:bg-emerald-900/50">
                                <ArrowDownLeft className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-700 dark:text-emerald-300">
                                {formatRupiah(summary.total_inflow)}
                            </div>
                            <p className="text-xs text-muted-foreground pt-1">Total uang masuk terakumulasi</p>
                        </CardContent>
                    </Card>

                    <Card className="border-rose-200 bg-rose-50/50 dark:border-rose-900/50 dark:bg-rose-950/20">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-semibold text-rose-800 dark:text-rose-300">
                                Total Pengeluaran (Outflow)
                            </CardTitle>
                            <div className="rounded-full bg-rose-100 p-2 dark:bg-rose-900/50">
                                <ArrowUpRight className="h-5 w-5 text-rose-600 dark:text-rose-400" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-rose-700 dark:text-rose-300">
                                {formatRupiah(summary.total_outflow)}
                            </div>
                            <p className="text-xs text-muted-foreground pt-1">Total uang keluar terakumulasi</p>
                        </CardContent>
                    </Card>

                    <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-semibold text-foreground">
                                Saldo Bersih (Net Balance)
                            </CardTitle>
                            <div className="rounded-full bg-muted p-2">
                                <Wallet className="h-5 w-5 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div
                                className={`text-2xl font-bold ${
                                    summary.net_balance >= 0
                                        ? 'text-foreground'
                                        : 'text-rose-600 dark:text-rose-400'
                                }`}
                            >
                                {formatRupiah(summary.net_balance)}
                            </div>
                            <p className="text-xs text-muted-foreground pt-1">Selisih pemasukan dan pengeluaran</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Table Card */}
                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-xl font-bold">Daftar Transaksi Arus Kas</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Catat dan pantau transaksi pemasukan serta pengeluaran harian Anda.
                            </p>
                        </div>
                        <Can permission="cashflow.create">
                            <Button asChild className="gap-2">
                                <Link href={cashflowsCreate.url()}>
                                    <Plus className="h-4 w-4" />
                                    Catat Transaksi
                                </Link>
                            </Button>
                        </Can>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {/* Filters */}
                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Cari transaksi..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-9"
                                />
                            </div>
                            <div>
                                <Select
                                    value={selectedType}
                                    onValueChange={(val) => handleFilterChange('type', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Tipe" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Tipe</SelectItem>
                                        <SelectItem value="inflow">Pemasukan (Inflow)</SelectItem>
                                        <SelectItem value="outflow">Pengeluaran (Outflow)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Select
                                    value={selectedCategory}
                                    onValueChange={(val) => handleFilterChange('category_id', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Kategori" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Kategori</SelectItem>
                                        {categories.map((cat) => (
                                            <SelectItem key={cat.id} value={cat.id.toString()}>
                                                {cat.name} ({cat.type === 'inflow' ? 'Pemasukan' : 'Pengeluaran'})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-center gap-2">
                                <Input
                                    type="date"
                                    value={dateFrom}
                                    onChange={(e) => handleFilterChange('date_from', e.target.value)}
                                    title="Dari Tanggal"
                                />
                                <span className="text-xs text-muted-foreground">s/d</span>
                                <Input
                                    type="date"
                                    value={dateTo}
                                    onChange={(e) => handleFilterChange('date_to', e.target.value)}
                                    title="Sampai Tanggal"
                                />
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground">
                                    <tr>
                                        <SortableHeader column="transaction_date" label="Tanggal" sortBy={sortBy} sortDir={sortDir} onSort={handleSort} className="px-4 py-3" />
                                        <SortableHeader column="name" label="Nama & Deskripsi" sortBy={sortBy} sortDir={sortDir} onSort={handleSort} className="px-4 py-3" />
                                        <SortableHeader column="category_id" label="Kategori" sortBy={sortBy} sortDir={sortDir} onSort={handleSort} className="px-4 py-3" />
                                        <SortableHeader column="type" label="Tipe" sortBy={sortBy} sortDir={sortDir} onSort={handleSort} className="px-4 py-3" />
                                        <SortableHeader column="amount" label="Nominal" sortBy={sortBy} sortDir={sortDir} onSort={handleSort} className="px-4 py-3" />
                                        <th className="px-4 py-3 text-right font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                                    {cashflows.data.length > 0 ? (
                                        cashflows.data.map((cashflow) => (
                                            <tr key={cashflow.id} className="hover:bg-muted/30 transition-colors">
                                                <td className="px-4 py-3 text-xs font-mono text-muted-foreground whitespace-nowrap">
                                                    {cashflow.transaction_date ?? '-'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="font-medium text-foreground">{cashflow.name}</div>
                                                    {cashflow.description && (
                                                        <div className="text-xs text-muted-foreground line-clamp-1">
                                                            {cashflow.description}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    <div className="flex items-center gap-1.5 flex-wrap">
                                                        <span>{cashflow.category?.name ?? '-'}</span>
                                                        {cashflow.category?.deleted_at && (
                                                            <Badge
                                                                variant="outline"
                                                                className="text-[10px] bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800"
                                                                title="Kategori ini telah dihapus"
                                                            >
                                                                Dihapus
                                                            </Badge>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {cashflow.type === 'inflow' ? (
                                                        <Badge className="bg-emerald-100 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800">
                                                            Pemasukan
                                                        </Badge>
                                                    ) : (
                                                        <Badge className="bg-rose-100 text-rose-800 hover:bg-rose-100 dark:bg-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800">
                                                            Pengeluaran
                                                        </Badge>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 font-semibold whitespace-nowrap">
                                                    <span
                                                        className={
                                                            cashflow.type === 'inflow'
                                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                                : 'text-rose-600 dark:text-rose-400'
                                                        }
                                                    >
                                                        {cashflow.type === 'inflow' ? '+' : '-'} {formatRupiah(cashflow.amount)}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-right whitespace-nowrap">
                                                    <div className="flex items-center justify-end gap-1">
                                                        <Can permission="cashflow.edit">
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                asChild
                                                                title="Ubah"
                                                            >
                                                                <Link href={cashflowsEdit.url(cashflow.id)}>
                                                                    <Edit2 className="h-4 w-4 text-muted-foreground hover:text-foreground" />
                                                                </Link>
                                                            </Button>
                                                        </Can>
                                                        <Can permission="cashflow.delete">
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                onClick={() => setDeletingCashflow(cashflow)}
                                                                title="Hapus"
                                                            >
                                                                <Trash2 className="h-4 w-4 text-destructive" />
                                                            </Button>
                                                        </Can>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                                Tidak ada data transaksi ditemukan.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination Links */}
                        {cashflows.links.length > 3 && (
                            <div className="flex flex-wrap items-center justify-between gap-2 pt-2 text-sm text-muted-foreground">
                                <div>
                                    Menampilkan {cashflows.from ?? 0} hingga {cashflows.to ?? 0} dari {cashflows.total} transaksi
                                </div>
                                <div className="flex items-center gap-1">
                                    {cashflows.links.map((link, idx) => (
                                        <Button
                                            key={idx}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                            dangerouslySetInnerHTML={{ __html: formatPaginationLabel(link.label) }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Delete Confirmation Modal */}
            <Dialog open={!!deletingCashflow} onOpenChange={(open) => !open && setDeletingCashflow(null)}>
                <DialogContent className="sm:max-w-[400px]">
                    <DialogHeader>
                        <DialogTitle>Konfirmasi Hapus Transaksi</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus transaksi{' '}
                            <span className="font-semibold text-foreground">"{deletingCashflow?.name}"</span>?
                            Tindakan ini tidak dapat dibatalkan.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="gap-2 sm:gap-0">
                        <Button type="button" variant="outline" onClick={() => setDeletingCashflow(null)}>
                            Batal
                        </Button>
                        <Button type="button" variant="destructive" onClick={handleDeleteConfirm}>
                            Hapus
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

CashflowsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Arus Kas',
            href: cashflowsIndex(),
        },
    ],
};
