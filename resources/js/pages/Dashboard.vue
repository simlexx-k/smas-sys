<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';
// import TenantAdminComponent1 from '@/components/tenants/TenantAttendanceCharts.vue';
import TenantDashboardContent from '@/components/tenants/TenantDashboardContent.vue';
import LandlordDashboardContent from '@/components/landlord/LandlordDashboardContent.vue';
import { computed } from 'vue';

const page = usePage<PageProps>();

interface User {
    role: string;
}

interface Tenant {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    logo_url: string | null;
    created_at: string;
}

const props = defineProps<{
    name?: string;
    user: User;
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

    if (!props.user) return baseBreadcrumbs;

    switch (props.user.role) {
        case 'landlord':
            return baseBreadcrumbs;
        case 'tenant-admin':
            return [...baseBreadcrumbs, {
                title: props.tenant?.name || 'School',
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
                            v-if="page.props.auth.user.role === 'landlord'"
                            :stats="page.props.stats"
                            :system-status="page.props.systemStatus"
                            :activities="page.props.activities"
                            :filters="page.props.filters"
                            :error="page.props.error"
                        />
                        <TenantDashboardContent 
                            v-else-if="page.props.auth.user.role === 'tenant-admin' && tenant"
                            :tenant="tenant"
                        />
                        <div v-else>
                            Welcome to your dashboard!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>