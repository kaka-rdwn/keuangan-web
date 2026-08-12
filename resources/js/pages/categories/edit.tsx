import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Edit3, Save } from 'lucide-react';
import { AuditMetaCard } from '@/components/audit-meta-card';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { CashflowType, Category, CategoryForm } from '@/types/category';

interface Props {
    category: Category;
}

export default function CategoryEdit({ category }: Props) {
    const { data, setData, put, processing, errors } = useForm<CategoryForm>({
        name: category.name,
        type: category.type,
        description: category.description ?? '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/categories/${category.id}`);
    };

    return (
        <>
            <Head title={`Edit Kategori - ${category.name}`} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                {/* Header & Navigation */}
                <div className="flex items-center gap-3">
                    <Button variant="outline" size="icon" asChild>
                        <Link href="/categories">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground">
                            <Edit3 className="h-6 w-6 text-primary" />
                            Ubah Kategori Keuangan
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Perbarui informasi data kategori <span className="font-semibold text-foreground">{category.name}</span>.
                        </p>
                    </div>
                </div>

                {/* Form Card */}
                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader>
                        <CardTitle className="text-lg font-semibold">Formulir Penyuntingan Kategori</CardTitle>
                        <CardDescription>
                            Ubah kolom yang diperlukan lalu klik simpan perubahan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-5">
                            {/* Nama Kategori */}
                            <div className="space-y-2">
                                <Label htmlFor="name" className="required">
                                    Nama Kategori
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    placeholder="Contoh: Gaji, Belanja Bulanan, Transportasi..."
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                />
                                <InputError message={errors.name} />
                            </div>

                            {/* Tipe Kategori */}
                            <div className="space-y-2">
                                <Label htmlFor="type" className="required">
                                    Tipe Transaksi
                                </Label>
                                <Select
                                    value={data.type}
                                    onValueChange={(val: CashflowType) => setData('type', val)}
                                >
                                    <SelectTrigger id="type">
                                        <SelectValue placeholder="Pilih Tipe" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="inflow">Pemasukan (Inflow)</SelectItem>
                                        <SelectItem value="outflow">Pengeluaran (Outflow)</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.type} />
                            </div>

                            {/* Deskripsi */}
                            <div className="space-y-2">
                                <Label htmlFor="description">Deskripsi (Opsional)</Label>
                                <Textarea
                                    id="description"
                                    placeholder="Catatan tambahan mengenai kategori ini..."
                                    value={data.description}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('description', e.target.value)}
                                    rows={4}
                                />
                                <InputError message={errors.description} />
                            </div>

                            {/* Form Actions */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-sidebar-border/50">
                                <Button variant="outline" asChild disabled={processing}>
                                    <Link href="/categories">Batal</Link>
                                </Button>
                                <Button type="submit" disabled={processing} className="gap-2">
                                    <Save className="h-4 w-4" />
                                    {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Audit Metadata Info */}
                <AuditMetaCard
                    createdBy={category.creator ?? category.createdBy}
                    createdAt={category.created_at}
                    updatedBy={category.updater ?? category.updatedBy}
                    updatedAt={category.updated_at}
                />
            </div>
        </>
    );
}
