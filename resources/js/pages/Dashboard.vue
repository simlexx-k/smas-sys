<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';
// import TenantAdminComponent1 from '@/components/tenants/TenantAttendanceCharts.vue';
import TenantAdminComponent2 from '@/components/tenants/TenantAdminComponent2.vue';

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
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">

            </div>
            <div v-if="usePage().props.auth.user?.role === 'tenant-admin'">
                <!-- <TenantAdminComponent1 /> -->
                <TenantAdminComponent2 />
            </div>
        </div>
    </AppLayout>
</template>