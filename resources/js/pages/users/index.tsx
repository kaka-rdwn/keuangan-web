import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Edit2, Key, Plus, RefreshCw, Search, Trash2, UserCheck, ShieldAlert } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
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
    destroy as usersDestroy,
    index as usersIndex,
    store as usersStore,
    update as usersUpdate,
} from '@/routes/users';
import type { PageProps } from '@/types';
import type { UserForm, UserItem, UserListProps } from '@/types/user';

const generateRandomPassword = (): string => {
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*';
    const all = uppercase + lowercase + numbers + symbols;

    let password = '';
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += symbols[Math.floor(Math.random() * symbols.length)];

    for (let i = 4; i < 12; i++) {
        password += all[Math.floor(Math.random() * all.length)];
    }

    return password.split('').sort(() => 0.5 - Math.random()).join('');
};

export default function UsersIndex({ users, roles, filters }: UserListProps) {
    const pageProps = usePage<PageProps>().props;
    const authUser = pageProps.auth.user;
    const flash = (pageProps as Record<string, unknown>).flash as { success?: string; error?: string } | undefined;

    const [search, setSearch] = useState(filters.search ?? '');
    const [selectedRole, setSelectedRole] = useState(filters.role ?? 'all');
    const isFirstRender = useRef(true);

    // Modal states
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<UserItem | null>(null);
    const [deletingUser, setDeletingUser] = useState<UserItem | null>(null);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);

    const form = useForm<UserForm>({
        name: '',
        email: '',
        role: roles.find((r) => r.name === 'User')?.name ?? roles[0]?.name ?? 'User',
        password: '',
    });

    // Debounced automatic search & filter effect
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;

            return;
        }

        const timer = setTimeout(() => {
            router.get(
                usersIndex.url({
                    query: {
                        search: search || undefined,
                        role: selectedRole !== 'all' ? selectedRole : undefined,
                    },
                }),
                {},
                { preserveState: true, replace: true }
            );
        }, 350);

        return () => clearTimeout(timer);
    }, [search, selectedRole]);

    const handleRoleFilterChange = (val: string) => {
        setSelectedRole(val);
    };

    const openCreateModal = () => {
        setEditingUser(null);
        const defaultRole = roles.find((r) => r.name === 'User')?.name ?? roles[0]?.name ?? 'User';
        const initialPassword = generateRandomPassword();

        form.setData({
            name: '',
            email: '',
            role: defaultRole,
            password: initialPassword,
        });
        form.clearErrors();
        setIsFormModalOpen(true);
    };

    const openEditModal = (user: UserItem) => {
        setEditingUser(user);
        form.setData({
            name: user.name,
            email: user.email,
            role: user.role?.name ?? 'User',
            password: '',
        });
        form.clearErrors();
        setIsFormModalOpen(true);
    };

    const handleFormSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setErrorMessage(null);

        if (editingUser) {
            form.put(usersUpdate.url(editingUser.id), {
                onSuccess: () => setIsFormModalOpen(false),
            });
        } else {
            form.post(usersStore.url(), {
                onSuccess: () => setIsFormModalOpen(false),
            });
        }
    };

    const handleDeleteConfirm = () => {
        if (!deletingUser) {
return;
}

        if (deletingUser.id === authUser?.id) {
            setErrorMessage('Anda tidak dapat menghapus akun Anda sendiri.');
            setDeletingUser(null);

            return;
        }

        router.delete(usersDestroy.url(deletingUser.id), {
            onSuccess: () => setDeletingUser(null),
            onError: (errors) => {
                if (typeof errors === 'string') {
                    setErrorMessage(errors);
                }

                setDeletingUser(null);
            },
        });
    };

    const handleGeneratePassword = () => {
        const pass = generateRandomPassword();
        form.setData('password', pass);
    };

    return (
        <>
            <Head title="Manajemen Pengguna" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
                {/* Flash Messages */}
                {flash?.success && (
                    <div className="flex items-center gap-2 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <UserCheck className="h-4 w-4 shrink-0" />
                        <span>{flash.success}</span>
                    </div>
                )}
                {(flash?.error || errorMessage) && (
                    <div className="flex items-center gap-2 rounded-lg bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                        <ShieldAlert className="h-4 w-4 shrink-0" />
                        <span>{flash?.error || errorMessage}</span>
                    </div>
                )}

                <Card className="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-xl font-bold">Manajemen Pengguna</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Kelola data pengguna, penetapan peran (role), dan pembuatan akun baru.
                            </p>
                        </div>
                        <Button onClick={openCreateModal} className="gap-2">
                            <Plus className="h-4 w-4" />
                            Tambah User Baru
                        </Button>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {/* Filters & Search */}
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder="Cari nama atau email..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-9"
                                />
                            </div>
                            <div className="w-full sm:w-48">
                                <Select value={selectedRole} onValueChange={handleRoleFilterChange}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Peran" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Peran</SelectItem>
                                        {roles.map((r) => (
                                            <SelectItem key={r.id} value={r.name}>
                                                {r.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* Data Table */}
                        <div className="overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-muted/50 text-xs uppercase tracking-wider text-muted-foreground">
                                    <tr>
                                        <th className="px-4 py-3 font-semibold">Pengguna</th>
                                        <th className="px-4 py-3 font-semibold">Email</th>
                                        <th className="px-4 py-3 font-semibold">Peran (Role)</th>
                                        <th className="px-4 py-3 font-semibold">Status</th>
                                        <th className="px-4 py-3 text-right font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                                    {users.data.length > 0 ? (
                                        users.data.map((u) => (
                                            <tr key={u.id} className="hover:bg-muted/30 transition-colors">
                                                <td className="px-4 py-3 font-medium text-foreground">
                                                    <div className="flex items-center gap-2">
                                                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 font-bold text-primary text-xs">
                                                            {u.name.charAt(0).toUpperCase()}
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold">{u.name}</div>
                                                            {u.id === authUser?.id && (
                                                                <span className="text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                                                                    (Akun Anda)
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">{u.email}</td>
                                                <td className="px-4 py-3">
                                                    {u.role?.name === 'Admin' ? (
                                                        <Badge className="bg-purple-100 text-purple-800 hover:bg-purple-100 dark:bg-purple-950 dark:text-purple-300 border-purple-200 dark:border-purple-800">
                                                            Admin
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="outline" className="text-muted-foreground">
                                                            {u.role?.name ?? 'User'}
                                                        </Badge>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {u.email_verified_at ? (
                                                        <Badge className="bg-emerald-100 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800">
                                                            Aktif
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="outline" className="bg-amber-50 text-amber-700 hover:bg-amber-50 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-800">
                                                            Belum Aktif
                                                        </Badge>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        {u.role?.name !== 'Admin' && (
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                asChild
                                                                title="Kelola Permission"
                                                            >
                                                                <Link href={`/users/${u.id}/permissions`}>
                                                                    <Key className="h-4 w-4 text-amber-600 dark:text-amber-400 hover:text-amber-700" />
                                                                </Link>
                                                            </Button>
                                                        )}
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            onClick={() => openEditModal(u)}
                                                            title="Ubah"
                                                        >
                                                            <Edit2 className="h-4 w-4 text-muted-foreground hover:text-foreground" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            disabled={u.id === authUser?.id}
                                                            onClick={() => setDeletingUser(u)}
                                                            title={u.id === authUser?.id ? 'Tidak dapat menghapus akun sendiri' : 'Hapus'}
                                                        >
                                                            <Trash2 className="h-4 w-4 text-destructive disabled:opacity-30" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">
                                                Tidak ada data pengguna ditemukan.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination Links */}
                        {users.links.length > 3 && (
                            <div className="flex flex-wrap items-center justify-between gap-2 pt-2 text-sm text-muted-foreground">
                                <div>
                                    Menampilkan {users.from ?? 0} hingga {users.to ?? 0} dari {users.total} pengguna
                                </div>
                                <div className="flex items-center gap-1">
                                    {users.links.map((link, idx) => (
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
                <DialogContent className="sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle>
                            {editingUser ? 'Edit Data Pengguna' : 'Tambah User Baru'}
                        </DialogTitle>
                        <DialogDescription>
                            {editingUser
                                ? 'Perbarui data pengguna. Kosongkan password jika tidak ingin mengubahnya.'
                                : 'Buat akun pengguna baru. Email kredensial akan dikirim secara otomatis ke email yang terdaftar.'}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleFormSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">Nama Lengkap</Label>
                            <Input
                                id="name"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                placeholder="Contoh: Ahmad Subagja"
                                required
                            />
                            <InputError message={form.errors.name} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email">Alamat Email</Label>
                            <Input
                                id="email"
                                type="email"
                                value={form.data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                                placeholder="ahmad@domain.com"
                                required
                            />
                            <InputError message={form.errors.email} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="role">Peran (Role)</Label>
                            <Select
                                value={form.data.role}
                                onValueChange={(val) => form.setData('role', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Peran" />
                                </SelectTrigger>
                                <SelectContent>
                                    {roles.map((r) => (
                                        <SelectItem key={r.id} value={r.name}>
                                            {r.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.role} />
                        </div>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <Label htmlFor="password">
                                    {editingUser ? 'Password (Opsional)' : 'Password Kredensial'}
                                </Label>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={handleGeneratePassword}
                                    className="h-7 px-2 text-xs gap-1 text-primary hover:text-primary/80"
                                >
                                    <RefreshCw className="h-3 w-3" />
                                    Generate
                                </Button>
                            </div>
                            <div className="relative">
                                <Input
                                    id="password"
                                    type="text"
                                    value={form.data.password || ''}
                                    onChange={(e) => form.setData('password', e.target.value)}
                                    placeholder={
                                        editingUser
                                            ? 'Biarkan kosong jika tidak diubah'
                                            : 'Password acak ter-generate...'
                                    }
                                    required={!editingUser}
                                    className="font-mono text-sm pr-9"
                                />
                                <Key className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            </div>
                            <InputError message={form.errors.password} />
                            {!editingUser && (
                                <p className="text-[11px] text-muted-foreground">
                                    Password minimal 8 karakter (kombinasi huruf besar, kecil, angka, dan simbol).
                                </p>
                            )}
                        </div>

                        <DialogFooter className="gap-2 sm:gap-0 pt-2">
                            <DialogClose asChild>
                                <Button type="button" variant="outline">
                                    Batal
                                </Button>
                            </DialogClose>
                            <Button type="submit" disabled={form.processing}>
                                {form.processing
                                    ? 'Menyimpan...'
                                    : editingUser
                                      ? 'Simpan Perubahan'
                                      : 'Tambah & Kirim Email'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Modal */}
            <Dialog open={!!deletingUser} onOpenChange={(open) => !open && setDeletingUser(null)}>
                <DialogContent className="sm:max-w-[400px]">
                    <DialogHeader>
                        <DialogTitle>Konfirmasi Hapus Pengguna</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus akun pengguna{' '}
                            <span className="font-semibold text-foreground">"{deletingUser?.name}"</span> ({deletingUser?.email})?
                            Tindakan ini tidak dapat dibatalkan.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="gap-2 sm:gap-0">
                        <Button type="button" variant="outline" onClick={() => setDeletingUser(null)}>
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

UsersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Pengguna',
            href: usersIndex(),
        },
    ],
};
