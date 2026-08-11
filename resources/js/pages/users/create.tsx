import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, Copy, RefreshCw, Save, UserPlus } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Role } from '@/types/user';

interface Props {
    roles: Role[];
}

const generatePassword = (length = 12): string => {
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*()_+~`|}{[]:;?><,./-=';
    const all = uppercase + lowercase + numbers + symbols;

    let password = '';
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += symbols[Math.floor(Math.random() * symbols.length)];

    for (let i = 4; i < length; i++) {
        password += all[Math.floor(Math.random() * all.length)];
    }

    return password.split('').sort(() => 0.5 - Math.random()).join('');
};

export default function UserCreate({ roles }: Props) {
    const [copied, setCopied] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        role: roles.length > 0 ? roles[0].name : 'User',
        password: generatePassword(12),
    });

    const handleRegeneratePassword = () => {
        const newPassword = generatePassword(12);
        setData('password', newPassword);
        setCopied(false);
    };

    const handleCopyPassword = () => {
        if (!data.password) {
return;
}

        navigator.clipboard.writeText(data.password);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/users');
    };

    return (
        <>
            <Head title="Tambah Pengguna Baru" />

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
                            <UserPlus className="h-6 w-6 text-primary" />
                            Tambah Pengguna Baru
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Buat akun pengguna baru dan tetapkan peran (role) akses sistem.
                        </p>
                    </div>
                </div>

                {/* Form Card */}
                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader>
                        <CardTitle className="text-lg font-semibold">Formulir Pengguna Baru</CardTitle>
                        <CardDescription>
                            Kredensial dan password akan secara otomatis dikirimkan ke email pengguna.
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
                                    autoFocus
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

                            {/* Password */}
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password" className="required">
                                        Kata Sandi (Password)
                                    </Label>
                                    <div className="flex items-center gap-2">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={handleCopyPassword}
                                            className="h-7 px-2 text-xs gap-1 text-muted-foreground hover:text-foreground"
                                            title="Salin Kata Sandi"
                                        >
                                            {copied ? (
                                                <>
                                                    <Check className="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                                                    <span className="text-emerald-600 dark:text-emerald-400">Tersalin!</span>
                                                </>
                                            ) : (
                                                <>
                                                    <Copy className="h-3.5 w-3.5" />
                                                    Salin
                                                </>
                                            )}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={handleRegeneratePassword}
                                            className="h-7 px-2 text-xs gap-1 text-primary hover:text-primary/80"
                                        >
                                            <RefreshCw className="h-3.5 w-3.5" />
                                            Generate Ulang
                                        </Button>
                                    </div>
                                </div>
                                <PasswordInput
                                    id="password"
                                    placeholder="Password acak ter-generate..."
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className="font-mono text-sm"
                                />
                                <InputError message={errors.password} />
                                <p className="text-[11px] text-muted-foreground">
                                    Password otomatis dibuat secara acak. Anda dapat menyalin atau melakukan generate ulang sebelum menyimpan.
                                </p>
                            </div>

                            {/* Form Actions */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-sidebar-border/50">
                                <Button variant="outline" asChild disabled={processing}>
                                    <Link href="/users">Batal</Link>
                                </Button>
                                <Button type="submit" disabled={processing} className="gap-2">
                                    <Save className="h-4 w-4" />
                                    {processing ? 'Menyimpan...' : 'Simpan & Kirim Email'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
