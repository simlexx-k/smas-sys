<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, PlusIcon, SchoolIcon } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const isMounted = ref(false);

onMounted(() => {
    isMounted.value = true;
});

onUnmounted(() => {
    isMounted.value = false;
});

const mainNavItems: NavItem[] = [
    console.log('Current user:', usePage().props.auth.user),
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    ...(usePage().props.auth.user?.role === 'tenant-admin' ? [
        {
            title: 'Students',
            href: '/students',
            icon: SchoolIcon,
        },
        {
            title: 'Teachers',
            href: '/teachers',
            icon: SchoolIcon,
        },
        {
            title: 'Classes',
            href: '/classes',
            icon: SchoolIcon,
        },
        {
            title: 'Attendance',
            href: '/attendance',
            icon: SchoolIcon,
        },
        {
            title: 'Subjects',
            href: '/subjects',
            icon: PlusIcon,
        },
        {
            title: 'Exams',
            href: '/exams',
            icon: PlusIcon,
        },
        {
            title: 'Report Cards',
            href: '/report-cards',
            icon: PlusIcon,
        }
    ] : [
        {
            title: 'Tenants',
            href: '/tenants',
            icon: SchoolIcon,
        },
        {
            title: 'Create Tenant',
            href: '/tenants/create',
            icon: PlusIcon,
        },

    ]),
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar v-if="isMounted" collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
