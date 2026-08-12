import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Edit3, Save } from 'lucide-react';
import { useMemo } from 'react';
import { AuditMetaCard } from '@/components/audit-meta-card';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { Cashflow, CashflowForm } from '@/types/cashflow';
import type { CashflowType, Category } from '@/types/category';

interface Props {
    cashflow: Cashflow;
    categories: Category[];
}

export default function CashflowEdit({ cashflow, categories }: Props) {
    const formattedDate = cashflow.transaction_date ? cashflow.transaction_date.split('T')[0] : '';

    const { data, setData, put, processing, errors } = useForm<CashflowForm>({
        name: cashflow.name,
        type: cashflow.type,
        category_id: cashflow.category_id ? String(cashflow.category_id) : '',
        amount: cashflow.amount ?? '',
        transaction_date: formattedDate,
        description: cashflow.description ?? '',
    });

    const filteredCategories = useMemo(() => {
        return categories.filter((cat) => cat.type === data.type);
    }, [categories, data.type]);

    const handleTypeChange = (newType: CashflowType) => {
        setData((prev) => ({
            ...prev,
            type: newType,
            category_id: '',
        }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/cashflows/${cashflow.id}`);
    };

    return (
        <>
            <Head title={`Edit Transaksi - ${cashflow.name}`} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                {/* Header & Navigation */}
                <div className="flex items-center gap-3">
                    <Button variant="outline" size="icon" asChild>
                        <Link href="/cashflows">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground">
                            <Edit3 className="h-6 w-6 text-primary" />
                            Ubah Transaksi Arus Kas
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Perbarui data transaksi <span className="font-semibold text-foreground">{cashflow.name}</span>.
                        </p>
                    </div>
                </div>

                {/* Form Card */}
                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader>
                        <CardTitle className="text-lg font-semibold">Formulir Penyuntingan Transaksi</CardTitle>
                        <CardDescription>
                            Ubah informasi nominal, tanggal, atau deskripsi transaksi lalu simpan perubahan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-5">
                            {/* Tipe Transaksi */}
                            <div className="space-y-2">
                                <Label htmlFor="type" className="required">
                                    Tipe Transaksi
                                </Label>
                                <Select value={data.type} onValueChange={(val: CashflowType) => handleTypeChange(val)}>
                                    <SelectTrigger id="type">
                                        <SelectValue placeholder="Pilih Tipe Transaksi" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="inflow">Pemasukan (Inflow)</SelectItem>
                                        <SelectItem value="outflow">Pengeluaran (Outflow)</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.type} />
                            </div>

                            {/* Nama Transaksi */}
                            <div className="space-y-2">
                                <Label htmlFor="name" className="required">
                                    Nama / Judul Transaksi
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    placeholder="Contoh: Pembayaran Listrik, Gaji Bulanan..."
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                />
                                <InputError message={errors.name} />
                            </div>

                            {/* Kategori Transaksi */}
                            <div className="space-y-2">
                                <Label htmlFor="category_id" className="required">
                                    Kategori
                                </Label>
                                <Select
                                    value={data.category_id ? String(data.category_id) : ''}
                                    onValueChange={(val) => setData('category_id', val)}
                                >
                                    <SelectTrigger id="category_id">
                                        <SelectValue placeholder="Pilih Kategori Transaksi" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {filteredCategories.length > 0 ? (
                                            filteredCategories.map((cat) => (
                                                <SelectItem key={cat.id} value={String(cat.id)}>
                                                    {cat.name}
                                                </SelectItem>
                                            ))
                                        ) : (
                                            <SelectItem value="none" disabled>
                                                Tidak ada kategori {data.type === 'inflow' ? 'pemasukan' : 'pengeluaran'}
                                            </SelectItem>
                                        )}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.category_id} />
                            </div>

                            {/* Nominal Transaksi (Rupiah) & Tanggal */}
                            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="amount" className="required">
                                        Nominal Transaksi (Rp)
                                    </Label>
                                    <Input
                                        id="amount"
                                        type="number"
                                        min="0"
                                        step="any"
                                        placeholder="0"
                                        value={data.amount}
                                        onChange={(e) => setData('amount', e.target.value)}
                                    />
                                    <InputError message={errors.amount} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="transaction_date" className="required">
                                        Tanggal Transaksi
                                    </Label>
                                    <Input
                                        id="transaction_date"
                                        type="date"
                                        value={data.transaction_date}
                                        onChange={(e) => setData('transaction_date', e.target.value)}
                                    />
                                    <InputError message={errors.transaction_date} />
                                </div>
                            </div>

                            {/* Deskripsi / Catatan */}
                            <div className="space-y-2">
                                <Label htmlFor="description">Catatan (Opsional)</Label>
                                <Textarea
                                    id="description"
                                    placeholder="Rincian atau catatan tambahan transaksi..."
                                    value={data.description}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setData('description', e.target.value)}
                                    rows={4}
                                />
                                <InputError message={errors.description} />
                            </div>

                            {/* Form Actions */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-sidebar-border/50">
                                <Button variant="outline" asChild disabled={processing}>
                                    <Link href="/cashflows">Batal</Link>
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
                    createdBy={cashflow.creator ?? cashflow.createdBy}
                    createdAt={cashflow.created_at}
                    updatedBy={cashflow.updater ?? cashflow.updatedBy}
                    updatedAt={cashflow.updated_at}
                />
            </div>
        </>
    );
}
