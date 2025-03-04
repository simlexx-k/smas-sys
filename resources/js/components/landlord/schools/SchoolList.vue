<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Pagination from '@/components/Pagination.vue';

interface School {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    logo_url: string | null;
    created_at: string;
    is_active: boolean;
    subscription?: {
        status: string;
        ends_at: string | null;
    };
}

interface Props {
    schools: {
        data: School[];
        links: any[];
    };
}

defineProps<Props>();

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active': return 'text-green-800 bg-green-100';
        case 'trial': return 'text-blue-800 bg-blue-100';
        case 'expired': return 'text-red-800 bg-red-100';
        default: return 'text-gray-800 bg-gray-100';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <!-- Schools List -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                School
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subscription
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="school in schools.data" :key="school.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img
                                            v-if="school.logo_url"
                                            :src="school.logo_url"
                                            :alt="school.name"
                                            class="h-10 w-10 rounded-full"
                                        >
                                        <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-xl font-bold text-gray-600">
                                                {{ school.name.charAt(0) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ school.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Added {{ formatDate(school.created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ school.email }}</div>
                                <div class="text-sm text-gray-500">{{ school.phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    school.is_active ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100',
                                    'px-2 py-1 text-xs rounded-full'
                                ]">
                                    {{ school.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="school.subscription" :class="[
                                    getStatusColor(school.subscription.status),
                                    'px-2 py-1 text-xs rounded-full'
                                ]">
                                    {{ school.subscription.status }}
                                </span>
                                <span v-else class="text-gray-500 text-sm">
                                    No subscription
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link
                                    :href="route('admin.tenants.show', school.id)"
                                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                                >
                                    View
                                </Link>
                                <Link
                                    :href="route('admin.tenants.edit', school.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="schools.links.length > 2" class="mt-6">
                <Pagination :links="schools.links" />
            </div>
        </div>
    </div>
</template> 