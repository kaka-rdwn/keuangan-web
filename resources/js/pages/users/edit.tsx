import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Edit3, Save } from 'lucide-react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Role, UserItem } from '@/types/user';

interface Props {
    user: UserItem;
    roles: Role[];
}

export default function UserEdit({ user, roles }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        role: user.role?.name ?? (roles.length > 0 ? roles[0].name : 'User'),
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(`/users/${user.id}`);
    };

    return (
        <>
            <Head title={`Edit Pengguna - ${user.name}`} />

            <div className="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                {/* Header & Navigation */}
                <div className="flex items-center gap-3">
                    <Button variant="outline" size="icon" asChild>
                        <Link href="/users">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground">
                            <Edit3 className="h-6 w-6 text-primary" />
                            Ubah Pengguna
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Perbarui informasi akun dan peran pengguna <span className="font-semibold text-foreground">{user.name}</span>.
                        </p>
                    </div>
                </div>

                {/* Form Card */}
                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader>
                        <CardTitle className="text-lg font-semibold">Formulir Penyuntingan Pengguna</CardTitle>
                        <CardDescription>
                            Kosongkan kolom kata sandi jika Anda tidak ingin mengubahnya.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-5">
                            {/* Nama Lengkap */}
                            <div className="space-y-2">
                                <Label htmlFor="name" className="required">
                                    Nama Lengkap
                                </Label>
                                <Input
                                    id="name"
                                    type="text"
                                    placeholder="Contoh: Ahmad Rizki"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                />
                                <InputError message={errors.name} />
                            </div>

                            {/* Email */}
                            <div className="space-y-2">
                                <Label htmlFor="email" className="required">
                                    Alamat Email
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="contoh@domain.com"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                />
                                <InputError message={errors.email} />
                            </div>

                            {/* Peran / Role */}
                            <div className="space-y-2">
                                <Label htmlFor="role" className="required">
                                    Peran (Role)
                                </Label>
                                <Select value={data.role} onValueChange={(val) => setData('role', val)}>
                                    <SelectTrigger id="role">
                                        <SelectValue placeholder="Pilih Peran" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {roles.map((r) => (
                                            <SelectItem key={r.id} value={r.name}>
                                                {r.name} {r.description ? `(${r.description})` : ''}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.role} />
                            </div>

                            {/* Password Baru */}
                            <div className="space-y-2">
                                <Label htmlFor="password">
                                    Kata Sandi Baru (Opsional)
                                </Label>
                                <PasswordInput
                                    id="password"
                                    placeholder="Biarkan kosong jika tidak ingin diubah"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                />
                                <InputError message={errors.password} />
                            </div>

                            {/* Konfirmasi Password */}
                            <div className="space-y-2">
                                <Label htmlFor="password_confirmation">
                                    Konfirmasi Kata Sandi Baru
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    placeholder="Ulangi kata sandi baru"
                                    value={data.password_confirmation}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                />
                                <InputError message={errors.password_confirmation} />
                            </div>

                            {/* Form Actions */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-sidebar-border/50">
                                <Button variant="outline" asChild disabled={processing}>
                                    <Link href="/users">Batal</Link>
                                </Button>
                                <Button type="submit" disabled={processing} className="gap-2">
                                    <Save className="h-4 w-4" />
                                    {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
