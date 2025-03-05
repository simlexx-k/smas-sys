<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { PageProps, User, UserRole } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';
// import TenantAdminComponent1 from '@/components/tenants/TenantAttendanceCharts.vue';
import TenantDashboardContent from '@/components/tenants/TenantDashboardContent.vue';
import LandlordDashboardContent from '@/components/landlord/LandlordDashboardContent.vue';
import TeacherDashboardContent from '@/components/teachers/TeacherDashboardContent.vue';
import { computed } from 'vue';

const page = usePage<PageProps>();

interface Tenant {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    logo_url: string | null;
    created_at: string;
}

const user = computed(() => page.props.auth.user);

// Add console logs
console.log('Current user:', user.value);
console.log('User role:', user.value.role);
console.log('Page props:', page.props);
console.log('Is teacher view condition:', user.value.role === 'teacher' && page.props.auth.user.tenant);

const props = defineProps<{
    name?: string;
    tenant?: Tenant;
    stats?: {
        total_tenants: number;
        active_tenants: number;
        recent_tenants: Array<Tenant>;
    };
}>();

// Define breadcrumbs based on role after props
const breadcrumbs = computed(() => {
    const baseBreadcrumbs = [{
        title: 'Dashboard',
        href: '/dashboard',
    }];

    if (!user.value) return baseBreadcrumbs;

    switch (user.value.role) {
        case 'landlord':
            return baseBreadcrumbs;
        case 'tenant-admin':
            return [...baseBreadcrumbs, {
                title: props.tenant?.name || 'School',
                href: '#'
            }];
        case 'teacher':
            return [...baseBreadcrumbs, {
                title: props.tenant?.name || 'School',
                href: '#'
            }, {
                title: 'Teacher Dashboard',
                href: '#'
            }];
        default:
            return baseBreadcrumbs;
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <LandlordDashboardContent 
                            v-if="user.role === 'landlord'"
                            :stats="page.props.stats"
                            :system-status="page.props.systemStatus"
                            :activities="page.props.activities"
                            :filters="page.props.filters"
                            :error="page.props.error"
                        />
                        <TenantDashboardContent 
                            v-else-if="user.role === 'tenant-admin' && page.props.auth.user.tenant"
                            :tenant="page.props.auth.user.tenant"
                        />
                        <TeacherDashboardContent 
                            v-else-if="user.role === 'teacher' && page.props.auth.user.tenant"
                            :tenant="page.props.auth.user.tenant"
                            :teacher="user"
                            :classes="page.props.classes || []"
                            :upcoming_lessons="page.props.upcoming_lessons || []"
                            :recent_activities="page.props.recent_activities || []"
                        />
                        <div v-else>
                            Welcome to your dashboard!
                            <pre>{{ JSON.stringify({ role: user.role, hasTenant: !!page.props.auth.user.tenant }, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>