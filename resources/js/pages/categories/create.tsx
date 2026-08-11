import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, FolderPlus, Save } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { CashflowType, CategoryForm } from '@/types/category';

export default function CategoryCreate() {
    const { data, setData, post, processing, errors } = useForm<CategoryForm>({
        name: '',
        type: 'outflow',
        description: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/categories');
    };

    return (
        <>
            <Head title="Tambah Kategori Keuangan" />

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
                            <FolderPlus className="h-6 w-6 text-primary" />
                            Tambah Kategori Keuangan
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Buat kategori baru untuk mengelompokkan arus kas pemasukan atau pengeluaran.
                        </p>
                    </div>
                </div>

                {/* Form Card */}
                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader>
                        <CardTitle className="text-lg font-semibold">Formulir Kategori Baru</CardTitle>
                        <CardDescription>
                            Isi detail informasi kategori di bawah ini dengan benar.
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
                                    {processing ? 'Menyimpan...' : 'Simpan Kategori'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
