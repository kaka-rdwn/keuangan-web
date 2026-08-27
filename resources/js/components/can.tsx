import type { ReactNode } from 'react';
import { usePermission } from '@/hooks/use-permission';
import type { PermissionInput, RoleInput } from '@/hooks/use-permission';

export interface CanProps {
    permission?: PermissionInput;
    role?: RoleInput;
    matchAll?: boolean;
    fallback?: ReactNode;
    children: ReactNode;
}

export function Can({
    permission,
    role,
    matchAll = false,
    fallback = null,
    children,
}: CanProps) {
    const { hasPermission, hasRole } = usePermission();

    let isAuthorized = true;

    if (permission) {
        isAuthorized = isAuthorized && hasPermission(permission, matchAll);
    }

    if (role) {
        isAuthorized = isAuthorized && hasRole(role);
    }

    if (!isAuthorized) {
        return <>{fallback}</>;
    }

    return <>{children}</>;
}
