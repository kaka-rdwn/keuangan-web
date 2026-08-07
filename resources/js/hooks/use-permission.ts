import { usePage } from '@inertiajs/react';
import type { Auth, User } from '@/types/auth';

export type PermissionInput = string | string[];
export type RoleInput = string | string[];

export interface UsePermissionReturn {
    user: User | null;
    isAdmin: () => boolean;
    hasRole: (role: RoleInput) => boolean;
    hasPermission: (permission: PermissionInput, matchAll?: boolean) => boolean;
    can: (permission: PermissionInput, matchAll?: boolean) => boolean;
}

export function usePermission(): UsePermissionReturn {
    const page = usePage<{ auth?: Auth }>();
    const user = page.props.auth?.user ?? null;

    /**
     * Check if user is Superadmin (role === 'Admin')
     */
    const isAdmin = (): boolean => {
        if (!user || !user.role) {
            return false;
        }

        return user.role === 'Admin';
    };

    /**
     * Check if user has given role or any of the given roles
     */
    const hasRole = (role: RoleInput): boolean => {
        if (!user || !user.role) {
            return false;
        }

        const userRole = user.role;

        if (Array.isArray(role)) {
            return role.includes(userRole);
        }

        return userRole === role;
    };

    /**
     * Check if user has permission(s) or is Admin (Superadmin Bypass)
     */
    const hasPermission = (permission: PermissionInput, matchAll = false): boolean => {
        if (!user) {
            return false;
        }

        // Superadmin bypass: Admin role has full access
        if (isAdmin()) {
            return true;
        }

        const userPermissions = user.permissions ?? [];

        if (userPermissions.length === 0) {
            return false;
        }

        if (Array.isArray(permission)) {
            if (permission.length === 0) {
                return true;
            }

            if (matchAll) {
                return permission.every((p) => userPermissions.includes(p));
            }

            return permission.some((p) => userPermissions.includes(p));
        }

        return userPermissions.includes(permission);
    };

    return {
        user,
        isAdmin,
        hasRole,
        hasPermission,
        can: hasPermission,
    };
}
