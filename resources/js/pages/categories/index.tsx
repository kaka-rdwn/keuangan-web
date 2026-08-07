import { Head, router, useForm } from '@inertiajs/react';
import { Edit2, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Can } from '@/components/can';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    destroy as categoriesDestroy,
    index as categoriesIndex,
    store as categoriesStore,
    update as categoriesUpdate,
} from '@/routes/categories';
import type { CashflowType, Category, CategoryForm, PaginatedCategories } from '@/types/category';

interface Props {
    categories: PaginatedCategories;
    filters: {
        search?: string;
        type?: string;
        sort?: string;
        direction?: string;
    };
}

export default function CategoriesIndex({ categories, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [selectedType, setSelectedType] = useState(filters.type ?? 'all');
    const isFirstRender = useRef(true);

    // Modal states
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [editingCategory, setEditingCategory] = useState<Category | null>(null);
    const [deletingCategory, setDeletingCategory] = useState<Category | null>(null);

    const form = useForm<CategoryForm>({
        name: '',
        type: 'outflow',
        description: '',
    });

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

    const openCreateModal = () => {
        setEditingCategory(null);
        form.setData({
            name: '',
            type: 'outflow',
            description: '',
        });
        form.clearErrors();
        setIsFormModalOpen(true);
    };

    const openEditModal = (category: Category) => {
        setEditingCategory(category);
        form.setData({
            name: category.name,
            type: category.type,
            description: category.description ?? '',
        });
        form.clearErrors();
        setIsFormModalOpen(true);
    };

    const handleFormSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (editingCategory) {
            form.put(categoriesUpdate.url(editingCategory.id), {
                onSuccess: () => setIsFormModalOpen(false),
            });
        } else {
            form.post(categoriesStore.url(), {
                onSuccess: () => setIsFormModalOpen(false),
            });
        }
    };

    const handleDeleteConfirm = () => {
        if (!deletingCategory) {
return;
}

        router.delete(categoriesDestroy.url(deletingCategory.id), {
            onSuccess: () => setDeletingCategory(null),
        });
    };

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
                        <Can permission="category.manage">
                            <Button onClick={openCreateModal} className="gap-2">
                                <Plus className="h-4 w-4" />
                                Tambah Kategori
                            </Button>
                        </Can>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {/* Filters & Search */}
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Cari nama atau deskripsi..."
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
                                        <SelectItem value="inflow">Pemasukan (Inflow)</SelectItem>
                                        <SelectItem value="outflow">Pengeluaran (Outflow)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-3 font-semibold">Nama</th>
                                        <th className="px-4 py-3 font-semibold">Tipe</th>
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
                                                    <Can
                                                        permission="category.manage"
                                                        fallback={<span className="text-xs font-mono text-muted-foreground">No access</span>}
                                                    >
                                                        <div className="flex items-center justify-end gap-2">
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                onClick={() => openEditModal(category)}
                                                                title="Ubah"
                                                            >
                                                                <Edit2 className="h-4 w-4 text-muted-foreground hover:text-foreground" />
                                                            </Button>
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                onClick={() => setDeletingCategory(category)}
                                                                title="Hapus"
                                                            >
                                                                <Trash2 className="h-4 w-4 text-destructive" />
                                                            </Button>
                                                        </div>
                                                    </Can>
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

            {/* Create & Edit Modal */}
            <Dialog open={isFormModalOpen} onOpenChange={setIsFormModalOpen}>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>
                            {editingCategory ? 'Edit Kategori' : 'Tambah Kategori Baru'}
                        </DialogTitle>
                        <DialogDescription>
                            Isi rincian kategori di bawah ini. Klik Simpan setelah selesai.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleFormSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Nama Kategori</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                placeholder="Contoh: Gaji, Operasional, Transportasi"
                                required
                            />
                            <InputError message={form.errors.name} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="type">Tipe Kategori</Label>
                            <Select
                                value={form.data.type}
                                onValueChange={(val: CashflowType) => form.setData('type', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Tipe" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="inflow">Pemasukan (Inflow)</SelectItem>
                                    <SelectItem value="outflow">Pengeluaran (Outflow)</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.type} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">Deskripsi (Opsional)</Label>
                            <Input
                                id="description"
                                value={form.data.description}
                                onChange={(e) => form.setData('description', e.target.value)}
                                placeholder="Keterangan singkat kategori..."
                            />
                            <InputError message={form.errors.description} />
                        </div>

                        <DialogFooter className="gap-2 sm:gap-0">
                            <DialogClose asChild>
                                <Button type="button" variant="outline">
                                    Batal
                                </Button>
                            </DialogClose>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? 'Menyimpan...' : 'Simpan'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

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
