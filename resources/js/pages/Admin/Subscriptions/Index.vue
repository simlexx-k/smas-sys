<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { BreadcrumbItem } from '@/types';

interface Plan {
    id: number;
    name: string;
    price: number;
}

interface Subscription {
    id: number;
    tenant: {
        id: number;
        name: string;
    };
    plan: Plan | null;
    plan_id: number | null;
    status: string;
    starts_at: string;
    ends_at: string | null;
    trial_ends_at: string | null;
    price: number;
}

interface Props {
    subscriptions: {
        data: Subscription[];
        links: any[];
    };
}

const { subscriptions } = defineProps<Props>();

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'text-green-800 bg-green-100';
        case 'trial':
            return 'text-blue-800 bg-blue-100';
        case 'cancelled':
            return 'text-yellow-800 bg-yellow-100';
        case 'expired':
            return 'text-red-800 bg-red-100';
        default:
            return 'text-gray-800 bg-gray-100';
    }
};

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString();
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard'
    },
    {
        title: 'Subscriptions',
        href: route('admin.subscriptions.index')
    }
];
</script>

<template>
    <AppLayout title="Subscriptions" :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Subscriptions</h2>
                <div class="flex space-x-3">
                    <Link
                        :href="route('admin.subscriptions.create')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Subscription
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div v-if="!subscriptions.data.length" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No subscriptions</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new subscription.</p>
                            <div class="mt-6">
                                <Link
                                    :href="route('admin.subscriptions.create')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Subscription
                                </Link>
                            </div>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            School
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Plan
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Start Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            End Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="subscription in subscriptions.data" :key="subscription.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link
                                                :href="`/admin/tenants/${subscription.tenant.id}`"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ subscription.tenant.name }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="capitalize">{{ subscription.plan?.name }}</span>
                                            <span v-if="!subscription.plan" class="text-xs text-red-500">
                                                (Plan not found - ID: {{ subscription.plan_id }})
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[getStatusColor(subscription.status), 'px-2 py-1 rounded-full text-xs']"
                                            >
                                                {{ subscription.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatPrice(subscription.price) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatDate(subscription.starts_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ formatDate(subscription.ends_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <Link
                                                :href="`/admin/subscriptions/${subscription.id}`"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div v-if="subscriptions.links?.length > 2" class="mt-6">
                                <Pagination :links="subscriptions.links" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 