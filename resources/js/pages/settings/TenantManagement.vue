<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenant Management',
        href: '/settings/tenant-management',
    },
];

const tenants = ref([]);
const isLoading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/settings/tenant-management/tenants-for-admin');
        console.log('Full response:', response);
        tenants.value = response.data?.tenants || [];
    } catch (error) {
        console.error('Error fetching tenants:', error);
    } finally {
        isLoading.value = false;
    }
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};
</script>
<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Tenant Management" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Tenant Management" description="Manage your tenant settings and configurations" />

                <div v-if="isLoading" class="flex justify-center items-center h-32">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                </div>

                <div v-else-if="tenants.length === 0" class="bg-white rounded-lg shadow p-6">
                    <p class="text-neutral-500">No tenants found.</p>
                </div>

                <div v-else class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Domain</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-neutral-200">
                            <tr v-for="tenant in tenants" :key="tenant.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900">{{ tenant.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">{{ tenant.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">{{ tenant.domain }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">{{ formatDate(tenant.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                    <button class="ml-4 text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
