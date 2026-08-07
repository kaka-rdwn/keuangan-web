import { Link } from '@inertiajs/react';
import { ArrowLeftRight, Folder, LayoutGrid, Users } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { usePermission } from '@/hooks/use-permission';
import { dashboard } from '@/routes';
import { index as cashflowsIndex } from '@/routes/cashflows';
import { index as categoriesIndex } from '@/routes/categories';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { can } = usePermission();

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Arus Kas',
            href: cashflowsIndex(),
            icon: ArrowLeftRight,
        },
        {
            title: 'Kategori',
            href: categoriesIndex(),
            icon: Folder,
        },
    ];

    if (can('user.manage')) {
        mainNavItems.push({
            title: 'Pengguna',
            href: usersIndex(),
            icon: Users,
        });
    }

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
