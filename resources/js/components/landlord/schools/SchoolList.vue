<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import Pagination from '@/components/Pagination.vue';

interface School {
    id: number;
    name: string;
    email: string | null;
    logo_url: string | null;
    created_at: string;
    is_active: boolean;
    subscription: {
        status: string;
        ends_at: string;
    } | null;
}

interface Props {
    schools: {
        data: School[];
        links: Array<any>;
    };
    filters?: {
        search?: string;
        status?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    schools: () => ({
        data: [],
        links: []
    }),
    filters: () => ({})
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

// Status options for filter
const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' }
];

// Debounced search
watch(search, debounce((value) => {
    router.get(route('admin.tenants.index'), 
        { search: value, status: statusFilter.value }, 
        { preserveState: true, preserveScroll: true }
    );
}, 300));

// Status filter
watch(statusFilter, (value) => {
    router.get(route('admin.tenants.index'), 
        { search: search.value, status: value }, 
        { preserveState: true, preserveScroll: true }
    );
});

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

const getInitials = (name: string): string => {
    if (!name) return 'NA';
    const words = name.trim().split(' ');
    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
    }
    return (words[0][0] + words[words.length - 1][0]).toUpperCase();
};
</script>

<template>
    <div v-if="schools?.data" class="bg-white shadow rounded-lg">
        <!-- Filters Section -->
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <div class="flex flex-col sm:flex-row justify-between gap-4">
                <!-- Search -->
                <div class="flex-1 max-w-md">
                    <label for="search" class="sr-only">Search schools</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            type="search"
                            v-model="search"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                            placeholder="Search schools..."
                        >
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="flex-shrink-0">
                    <select
                        v-model="statusFilter"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="px-4 py-5 sm:p-6">
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
                                            v-if="school.logo_url && school.logo_url !== '/images/default-school-logo.png'"
                                            :src="school.logo_url"
                                            :alt="school.name"
                                            class="h-10 w-10 rounded-full object-cover"
                                        >
                                        <div 
                                            v-else 
                                            class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center"
                                        >
                                            <span class="text-sm font-medium text-indigo-700">
                                                {{ getInitials(school.name) }}
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
        </div>

        <!-- Empty State -->
        <div v-if="schools.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No schools found</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ search ? 'Try adjusting your search or filters' : 'Get started by creating a new school' }}
            </p>
            <div class="mt-6">
                <Link
                    :href="route('admin.tenants.create')"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add New School
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="schools.links.length > 2" class="px-4 py-4 sm:px-6 border-t border-gray-200">
            <Pagination :links="schools.links" />
        </div>
    </div>
</template> 