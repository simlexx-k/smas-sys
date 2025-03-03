<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';
// import TenantAdminComponent1 from '@/components/tenants/TenantAttendanceCharts.vue';
import TenantAdminComponent2 from '@/components/tenants/TenantAdminComponent2.vue';
import TenantDashboardContent from '@/components/tenants/TenantDashboardContent.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps<{
    name?: string;
    user: {
        role: string;
    };
    tenant?: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        logo_url: string | null;
        created_at: string;
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
                        <TenantDashboardContent 
                            v-if="usePage().props.auth.user?.role === 'tenant-admin' && tenant"
                            :tenant="tenant"
                        />
                        <!-- Add other role-specific dashboard content here -->
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>