<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';
// import TenantAdminComponent1 from '@/components/tenants/TenantAttendanceCharts.vue';
import TenantDashboardContent from '@/components/tenants/TenantDashboardContent.vue';
import LandlordDashboardContent from '@/components/landlord/LandlordDashboardContent.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

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

interface PageProps {
    auth: {
        user: User;
    };
    stats?: {
        total_tenants: number;
        active_tenants: number;
        recent_tenants: {
            data: Array<any>;
            links: Array<any>;
        };
    };
    systemStatus?: Record<string, string>;
    activities?: {
        data: Array<any>;
        links: Array<any>;
    };
    filters?: {
        search?: string;
        status?: string;
    };
    error?: string;
}

const page = usePage<PageProps>();

defineProps<{
    name?: string;
    user: User;
    tenant?: Tenant;
    stats?: {
        total_tenants: number;
        active_tenants: number;
        recent_tenants: Array<Tenant>;
    };
}>();
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