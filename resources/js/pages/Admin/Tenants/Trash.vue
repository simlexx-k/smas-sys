<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDistanceToNow } from 'date-fns';

interface Tenant {
    id: number;
    name: string;
    email: string;
    deleted_at: string;
    admin?: {
        name: string;
        email: string;
    };
    subscription?: {
        status: string;
    };
}

interface TrashStats {
    total: number;
    expiringSoon: number;
    storageUsed: string;
    byStatus: {
        [key: string]: number;
    };
}

interface Props {
    tenants: {
        data: Tenant[];
        links: any[];
    };
    stats: TrashStats;
}

const props = defineProps<Props>();
const toast = useToast();

const restore = (tenant: Tenant) => {
    if (confirm(`Are you sure you want to restore ${tenant.name}?`)) {
        router.post(route('admin.tenants.restore', tenant.id), {}, {
            onSuccess: () => toast.success('School restored successfully'),
            onError: () => toast.error('Failed to restore school')
        });
    }
};

const forceDelete = (tenant: Tenant) => {
    if (confirm(`Are you sure you want to permanently delete ${tenant.name}? This action cannot be undone.`)) {
        router.delete(route('admin.tenants.force-delete', tenant.id), {
            onSuccess: () => toast.success('School permanently deleted'),
            onError: () => toast.error('Failed to delete school')
        });
    }
};

const breadcrumbs = [
    { title: 'Schools', href: route('admin.tenants.index') },
    { title: 'Trash', href: route('admin.tenants.trash') }
];
</script>

<template>
    <Head title="Deleted Schools" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Deleted Schools</h2>
                <Link
                    v-if="stats.total > 0"
                    :href="route('admin.tenants.export-trash')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export to Excel
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6">
                            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-4">
                                <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Deleted Schools
                                    </dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                        {{ stats.total }}
                                    </dd>
                                </div>
                                <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Expiring Soon
                                    </dt>
                                    <dd class="mt-1 text-3xl font-semibold text-red-600">
                                        {{ stats.expiringSoon }}
                                    </dd>
                                </div>
                                <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Storage Used
                                    </dt>
                                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                        {{ stats.storageUsed }}
                                    </dd>
                                </div>
                                <div class="px-4 py-5 bg-white shadow rounded-lg overflow-hidden sm:p-6">
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        By Status
                                    </dt>
                                    <dd class="mt-1">
                                        <ul class="space-y-1">
                                            <li v-for="(count, status) in stats.byStatus" :key="status" 
                                                class="text-sm flex justify-between"
                                            >
                                                <span class="capitalize">{{ status || 'No status' }}</span>
                                                <span>{{ count }}</span>
                                            </li>
                                            <li v-if="Object.keys(stats.byStatus).length === 0" 
                                                class="text-sm text-gray-500"
                                            >
                                                No data available
                                            </li>
                                        </ul>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="tenant in tenants.data" :key="tenant.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ tenant.name }}</div>
                                            <div class="text-sm text-gray-500">{{ tenant.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="tenant.admin" class="text-sm text-gray-900">{{ tenant.admin.name }}</div>
                                            <div v-if="tenant.admin" class="text-sm text-gray-500">{{ tenant.admin.email }}</div>
                                            <div v-else class="text-sm text-gray-500">No admin</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDistanceToNow(new Date(tenant.deleted_at), { addSuffix: true }) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800': tenant.subscription?.status === 'trial',
                                                    'bg-green-100 text-green-800': tenant.subscription?.status === 'active',
                                                    'bg-red-100 text-red-800': tenant.subscription?.status === 'canceled'
                                                }">
                                                {{ tenant.subscription?.status || 'No subscription' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                @click="restore(tenant)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                Restore
                                            </button>
                                            <button
                                                @click="forceDelete(tenant)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete Permanently
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 