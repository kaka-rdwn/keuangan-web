import { Head, Link, router } from '@inertiajs/react';
import { Edit2, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Can } from '@/components/can';
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
import {
    create as categoriesCreate,
    destroy as categoriesDestroy,
    edit as categoriesEdit,
    index as categoriesIndex,
} from '@/routes/categories';
import type { Category, PaginatedCategories } from '@/types/category';

interface Props {
    categories: PaginatedCategories;
    filters: {
        search?: string;
        type?: string;
        sort?: string;
        direction?: string;
    };
    can?: {
        create: boolean;
        edit: boolean;
        delete: boolean;
    };
}

export default function CategoriesIndex({ categories, filters, can }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [selectedType, setSelectedType] = useState(filters.type ?? 'all');
    const isFirstRender = useRef(true);

    const [deletingCategory, setDeletingCategory] = useState<Category | null>(null);

    // Debounced automatic search & filter effect
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timer = setTimeout(() => {
            router.get(
                categoriesIndex.url({
                    query: {
                        search: search || undefined,
                        type: selectedType !== 'all' ? selectedType : undefined,
                    },
                }),
                {},
                { preserveState: true, replace: true }
            );
        }, 350);

        return () => clearTimeout(timer);
    }, [search, selectedType]);

    const handleTypeFilterChange = (val: string) => {
        setSelectedType(val);
    };

    const handleSort = (field: string) => {
        const direction = filters.sort === field && filters.direction === 'asc' ? 'desc' : 'asc';
        router.get(
            categoriesIndex.url({
                query: {
                    search: search || undefined,
                    type: selectedType !== 'all' ? selectedType : undefined,
                    sort: field,
                    direction,
                },
            }),
            {},
            { preserveState: true, replace: true }
        );
    };

    const handleDeleteConfirm = () => {
        if (!deletingCategory) {
            return;
        }

        router.delete(categoriesDestroy.url(deletingCategory.id), {
            onSuccess: () => setDeletingCategory(null),
        });
    };

    const canCreate = can?.create ?? true;
    const canEdit = can?.edit ?? true;
    const canDelete = can?.delete ?? true;

    return (
        <>
            <Head title="Kategori Keuangan" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-xl font-bold">Kategori Keuangan</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Kelola kategori pemasukan dan pengeluaran transaksi keuangan Anda.
                            </p>
                        </div>
                        {canCreate && (
                            <Can permission="category.create">
                                <Button asChild className="gap-2">
                                    <Link href={categoriesCreate.url()}>
                                        <Plus className="h-4 w-4" />
                                        Tambah Kategori
                                    </Link>
                                </Button>
                            </Can>
                        )}
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {/* Filters & Search */}
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Cari nama atau deskripsi kategori..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-9"
                                />
                            </div>
                            <div className="w-full sm:w-48">
                                <Select value={selectedType} onValueChange={handleTypeFilterChange}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Tipe" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Tipe</SelectItem>
                                        <SelectItem value="inflow">Pemasukan</SelectItem>
                                        <SelectItem value="outflow">Pengeluaran</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* Data Table */}
                        <div className="overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground">
                                    <tr>
                                        <th
                                            className="px-4 py-3 font-semibold cursor-pointer hover:bg-muted/80 select-none"
                                            onClick={() => handleSort('name')}
                                        >
                                            <div className="flex items-center gap-1">
                                                <span>Nama Kategori</span>
                                                {filters.sort === 'name' && (
                                                    <span className="text-primary">
                                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                                    </span>
                                                )}
                                            </div>
                                        </th>
                                        <th
                                            className="px-4 py-3 font-semibold cursor-pointer hover:bg-muted/80 select-none"
                                            onClick={() => handleSort('type')}
                                        >
                                            <div className="flex items-center gap-1">
                                                <span>Tipe</span>
                                                {filters.sort === 'type' && (
                                                    <span className="text-primary">
                                                        {filters.direction === 'asc' ? '↑' : '↓'}
                                                    </span>
                                                )}
                                            </div>
                                        </th>
                                        <th className="px-4 py-3 font-semibold">Deskripsi</th>
                                        <th className="px-4 py-3 text-right font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                                    {categories.data.length > 0 ? (
                                        categories.data.map((category) => (
                                            <tr key={category.id} className="hover:bg-muted/30 transition-colors">
                                                <td className="px-4 py-3 font-medium text-foreground">
                                                    {category.name}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {category.type === 'inflow' ? (
                                                        <Badge className="bg-emerald-100 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800">
                                                            Pemasukan
                                                        </Badge>
                                                    ) : (
                                                        <Badge className="bg-rose-100 text-rose-800 hover:bg-rose-100 dark:bg-rose-950 dark:text-rose-300 border-rose-200 dark:border-rose-800">
                                                            Pengeluaran
                                                        </Badge>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {category.description || '-'}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        {canEdit && (
                                                            <Can permission="category.edit">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    asChild
                                                                    title="Ubah"
                                                                >
                                                                    <Link href={categoriesEdit.url(category.id)}>
                                                                        <Edit2 className="h-4 w-4 text-muted-foreground hover:text-foreground" />
                                                                    </Link>
                                                                </Button>
                                                            </Can>
                                                        )}
                                                        {canDelete && (
                                                            <Can permission="category.delete">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    onClick={() => setDeletingCategory(category)}
                                                                    title="Hapus"
                                                                >
                                                                    <Trash2 className="h-4 w-4 text-destructive" />
                                                                </Button>
                                                            </Can>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={4} className="px-4 py-8 text-center text-muted-foreground">
                                                Tidak ada data kategori ditemukan.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination Links */}
                        {categories.links.length > 3 && (
                            <div className="flex flex-wrap items-center justify-between gap-2 pt-2 text-sm text-muted-foreground">
                                <div>
                                    Menampilkan {categories.from ?? 0} hingga {categories.to ?? 0} dari {categories.total} kategori
                                </div>
                                <div className="flex items-center gap-1">
                                    {categories.links.map((link, idx) => (
                                        <Button
                                            key={idx}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Delete Confirmation Modal */}
            <Dialog open={!!deletingCategory} onOpenChange={(open) => !open && setDeletingCategory(null)}>
                <DialogContent className="sm:max-w-[400px]">
                    <DialogHeader>
                        <DialogTitle>Konfirmasi Hapus Kategori</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus kategori{' '}
                            <span className="font-semibold text-foreground">"{deletingCategory?.name}"</span>?
                            Tindakan ini tidak dapat dibatalkan.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="gap-2 sm:gap-0">
                        <Button type="button" variant="outline" onClick={() => setDeletingCategory(null)}>
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

CategoriesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Kategori',
            href: categoriesIndex(),
        },
    ],
};
