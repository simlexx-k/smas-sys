<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, LayoutGrid, PlusIcon, SchoolIcon, Building, CreditCard, Settings, Users, BookOpenCheck, GraduationCap } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const isMounted = ref(false);
const page = usePage<PageProps>();

onMounted(() => {
    isMounted.value = true;
});

onUnmounted(() => {
    isMounted.value = false;
});

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    ...(page.props.auth.user?.role === 'landlord' ? [
        {
            title: 'Schools',
            href: route('admin.tenants.index'),
            icon: SchoolIcon,
        },
        {
            title: 'Subscriptions',
            href: route('admin.subscriptions.index'),
            icon: CreditCard,
        },
        {
            title: 'Plans',
            href: route('admin.plans.index'),
            icon: Settings,
        },
        {
            title: 'New School',
            href: route('admin.tenants.create'),
            icon: PlusIcon,
        }
    ] : page.props.auth.user?.role === 'tenant-admin' ? [
        {
            title: 'Students',
            href: '/students',
            icon: SchoolIcon,
        },
        {
            title: 'Teachers',
            href: '/teachers',
            icon: Users,
        },
        {
            title: 'Classes',
            href: '/classes',
            icon: Building,
        },
        {
            title: 'Attendance',
            href: '/attendance',
            icon: BookOpenCheck,
        },
        {
            title: 'Subjects',
            href: '/subjects',
            icon: BookOpen,
        },
        {
            title: 'Exams',
            href: '/exams',
            icon: BookOpen,
        },
        {
            title: 'Report Cards',
            href: '/report-cards',
            icon: BookOpen,
        }
    ] : page.props.auth.user?.role === 'teacher' ? [
        {
            title: 'Students',
            href: '/students',
            icon: SchoolIcon,
        }
    ] : []),
];

const footerNavItems: NavItem[] = [
    ...(page.props.auth.user?.role === 'landlord' ? [

    ] : page.props.auth.user?.role === 'tenant-admin' ? [
        {
            title: 'School Settings',
            href: route('settings.school'),
            icon: Building,
        }
    ] : []),
    {
        title: 'Documentation',
        href: '/docs',
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
