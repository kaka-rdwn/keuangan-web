import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    CheckSquare,
    Info,
    Key,
    Save,
    ShieldAlert,
    ShieldCheck,
    Square,
} from 'lucide-react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import type { GroupedPermission, UserPermissionsProps } from '@/types/user';

export default function UserPermissionsPage({
    user,
    userRole,
    groupedPermissions,
    userPermissionIds,
}: UserPermissionsProps) {
    const { data, setData, put, processing, errors } = useForm<{ permissions: number[] }>({
        permissions: userPermissionIds ?? [],
    });

    const isPermissionSelected = (id: number): boolean => {
        return data.permissions.includes(id);
    };

    const togglePermission = (id: number) => {
        if (isPermissionSelected(id)) {
            setData(
                'permissions',
                data.permissions.filter((item) => item !== id)
            );
        } else {
            setData('permissions', [...data.permissions, id]);
        }
    };

    const selectGroupPermissions = (group: GroupedPermission) => {
        const groupIds = group.items.map((item) => item.id);
        const newPermissions = Array.from(new Set([...data.permissions, ...groupIds]));
        setData('permissions', newPermissions);
    };

    const deselectGroupPermissions = (group: GroupedPermission) => {
        const groupIds = new Set(group.items.map((item) => item.id));
        const newPermissions = data.permissions.filter((id) => !groupIds.has(id));
        setData('permissions', newPermissions);
    };

    const isGroupAllSelected = (group: GroupedPermission): boolean => {
        return group.items.every((item) => data.permissions.includes(item.id));
    };

    const selectAllGlobal = () => {
        const allIds = groupedPermissions.flatMap((g) => g.items.map((i) => i.id));
        setData('permissions', Array.from(new Set(allIds)));
    };

    const deselectAllGlobal = () => {
        setData('permissions', []);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/users/${user.id}/permissions`);
    };

    const totalPermissionsCount = groupedPermissions.reduce(
        (acc, group) => acc + group.items.length,
        0
    );

    return (
        <>
            <Head title={`Kelola Permission - ${user.name}`} />

            <div className="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                {/* Navigation Back & Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="outline" size="icon" asChild>
                            <Link href="/users">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground">
                                <Key className="h-6 w-6 text-primary" />
                                Kelola Hak Akses (Direct Permissions)
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Atur izin khusus untuk pengguna <span className="font-semibold text-foreground">{user.name}</span> ({user.email}).
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={selectAllGlobal}
                            className="text-xs"
                        >
                            <CheckSquare className="mr-1.5 h-3.5 w-3.5" />
                            Pilih Semua
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={deselectAllGlobal}
                            className="text-xs"
                        >
                            <Square className="mr-1.5 h-3.5 w-3.5" />
                            Hapus Semua
                        </Button>
                    </div>
                </div>

                {/* User Identity Card */}
                <Card className="border-sidebar-border/70 bg-card dark:border-sidebar-border">
                    <CardContent className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-3">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary">
                                {user.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div className="flex items-center gap-2">
                                    <span className="text-base font-semibold text-foreground">{user.name}</span>
                                    {userRole === 'Admin' ? (
                                        <Badge className="bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border-purple-200 dark:border-purple-800">
                                            Admin (Superadmin)
                                        </Badge>
                                    ) : (
                                        <Badge variant="outline" className="text-muted-foreground">
                                            Peran: {userRole ?? 'User'}
                                        </Badge>
                                    )}
                                </div>
                                <span className="text-sm text-muted-foreground">{user.email}</span>
                            </div>
                        </div>

                        <div className="flex items-center gap-2 text-xs text-muted-foreground bg-muted/50 px-3 py-1.5 rounded-md border border-sidebar-border/40">
                            <ShieldCheck className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            <span>
                                Terpilih: <strong className="text-foreground">{data.permissions.length}</strong> dari {totalPermissionsCount} Permission
                            </span>
                        </div>
                    </CardContent>
                </Card>

                {userRole === 'Admin' && (
                    <div className="flex items-start gap-3 rounded-lg border border-purple-200 bg-purple-50 p-4 text-purple-900 dark:border-purple-900/50 dark:bg-purple-950/40 dark:text-purple-200 text-sm">
                        <ShieldAlert className="h-5 w-5 flex-shrink-0 text-purple-600 dark:text-purple-400" />
                        <div>
                            <strong className="font-semibold">Catatan Superadmin:</strong> Pengguna ini memiliki peran <strong className="underline">Admin</strong>. Sesuai kebijakan aplikasi, akun Admin secara otomatis melewati (*bypass*) semua pemeriksaan izin dan dapat mengakses seluruh fitur tanpa batasan.
                        </div>
                    </div>
                )}

                {errors.permissions && (
                    <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
                        <InputError message={errors.permissions} />
                    </div>
                )}

                {/* Form grouped permissions */}
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="space-y-6">
                        {groupedPermissions.map((group) => {
                            const allSelected = isGroupAllSelected(group);

                            return (
                                <Card
                                    key={group.key}
                                    className="border-sidebar-border/70 dark:border-sidebar-border"
                                >
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3 border-b border-sidebar-border/50 bg-muted/30 dark:bg-muted/10">
                                        <div>
                                            <CardTitle className="text-base font-bold text-foreground">
                                                {group.name}
                                            </CardTitle>
                                            <CardDescription className="text-xs">
                                                Modul {group.key} ({group.items.length} izin tersedia)
                                            </CardDescription>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() =>
                                                    allSelected
                                                        ? deselectGroupPermissions(group)
                                                        : selectGroupPermissions(group)
                                                }
                                                className="h-8 text-xs text-muted-foreground hover:text-foreground"
                                            >
                                                {allSelected ? 'Batal Kelompok' : 'Pilih Kelompok'}
                                            </Button>
                                        </div>
                                    </CardHeader>

                                    <CardContent className="pt-4">
                                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2">
                                            {group.items.map((permission) => {
                                                const checked = isPermissionSelected(permission.id);
                                                const checkId = `permission-${permission.id}`;

                                                return (
                                                    <div
                                                        key={permission.id}
                                                        onClick={() => togglePermission(permission.id)}
                                                        className={`flex cursor-pointer items-start space-x-3 rounded-lg border p-3.5 transition-colors ${
                                                            checked
                                                                ? 'border-primary/50 bg-primary/5 dark:bg-primary/10'
                                                                : 'border-sidebar-border/70 hover:bg-muted/40 dark:border-sidebar-border'
                                                        }`}
                                                    >
                                                        <Checkbox
                                                            id={checkId}
                                                            checked={checked}
                                                            onCheckedChange={() => togglePermission(permission.id)}
                                                            className="mt-0.5"
                                                        />
                                                        <div className="flex-1 space-y-1">
                                                            <div className="flex items-center justify-between">
                                                                <Label
                                                                    htmlFor={checkId}
                                                                    className="cursor-pointer font-semibold text-sm text-foreground"
                                                                >
                                                                    {permission.display_name}
                                                                </Label>
                                                                <code className="rounded bg-muted px-1.5 py-0.5 text-[11px] font-mono text-muted-foreground">
                                                                    {permission.name}
                                                                </code>
                                                            </div>
                                                            {permission.description && (
                                                                <p className="text-xs text-muted-foreground">
                                                                    {permission.description}
                                                                </p>
                                                            )}
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>

                    {/* Bottom Action Bar */}
                    <div className="sticky bottom-4 z-10 flex items-center justify-between rounded-lg border border-sidebar-border/80 bg-background/95 p-4 shadow-lg backdrop-blur dark:border-sidebar-border">
                        <div className="flex items-center gap-2 text-xs text-muted-foreground">
                            <Info className="h-4 w-4 text-primary" />
                            <span>Perubahan permission akan langsung aktif pada sesi pengguna berikutnya.</span>
                        </div>

                        <div className="flex items-center gap-3">
                            <Button variant="outline" asChild disabled={processing}>
                                <Link href="/users">Batal</Link>
                            </Button>

                            <Button type="submit" disabled={processing} className="gap-2">
                                <Save className="h-4 w-4" />
                                {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </>
    );
}
