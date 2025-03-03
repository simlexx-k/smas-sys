<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';

interface Subscription {
    id: number;
    tenant: {
        id: number;
        name: string;
    };
    plan: string;
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

defineProps<Props>();

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
</script>

<template>
    <AppLayout title="Subscriptions">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Subscriptions</h2>
                <Link
                    href="/admin/subscriptions/create"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                >
                    New Subscription
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
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
                                            <span class="capitalize">{{ subscription.plan }}</span>
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
                        </div>

                        <div class="mt-6">
                            <Pagination :links="subscriptions.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 