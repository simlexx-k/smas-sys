<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, LifeBuoy, Settings, UserPenIcon, Users, Pen, LibraryBig } from 'lucide-vue-next';
import TenantAppLogo from './TenantAppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/',
        icon: LayoutGrid,
    },
    {
        title: 'Students',
        href: '/students',
        icon: Users,
    },
    {
        title: 'Teachers',
        href: '/teachers',
        icon: UserPenIcon,
    },
    {
        title: 'Classes',
        href: '/classes',
        icon: Pen,
    },
    {
        title: 'Academics',
        href: '/academics',
        icon: LibraryBig,
    }
];

const footerNavItems: NavItem[] = [

    {
        title: 'Support',
        href: 'https://katalyst.com/support-smas',
        icon: LifeBuoy,
    },
    {
        title: 'Manage School',
        href: '/school-profile',
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <TenantAppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <SidebarMenu>
                <!-- <SidebarMenuItem :to="route('tenants.academics', { hashedId: tenant.hashed_id })" icon="academic-cap">
                  Academics
                </SidebarMenuItem> -->
            </SidebarMenu>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
