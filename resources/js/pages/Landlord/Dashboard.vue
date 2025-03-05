<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import type { PageProps } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import Pagination from '@/components/Pagination.vue';
import DeletedSchoolsWidget from '@/components/landlord/DeletedSchoolsWidget.vue';

const page = usePage<PageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/landlord/dashboard',
    }
];

// Define tabs
const tabs = [
    { id: 'overview', name: 'Overview', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { id: 'subscriptions', name: 'Subscriptions', icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z' },
    { id: 'revenue', name: 'Revenue', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { id: 'metrics', name: 'Metrics', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' }
];

const activeTab = ref('overview');

interface Tenant {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    logo_url: string | null;
    created_at: string;
}

interface Activity {
    id: number;
    type: string;
    action: string;
    description: string;
    created_at: string;
    metadata: {
        user_role: string;
        user_email: string;
        model_name?: string;
        changes?: any;
    };
    user: {
        name: string;
        email: string;
        role: string;
    };
    tenant?: {
        name: string;
        domain: string;
    };
}

const search = ref(page.props.filters?.search || '');
const status = ref(page.props.filters?.status || '');

// Fix debounce typing
watch(search, debounce((value: string) => {
    router.get('/landlord/dashboard', { 
        search: value, 
        status: status.value 
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300));

watch(status, (value: string) => {
    router.get('/landlord/dashboard', { 
        search: search.value, 
        status: value 
    }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const getStatusColor = (status: string) => {
    return status === 'healthy' ? 'text-green-600' : 'text-red-600';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString();
};

const getActivityIcon = (type: string) => {
    switch (type) {
        case 'create':
            return 'M12 4v16m8-8H4';
        case 'update':
            return 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15';
        case 'system':
            return 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z';
        default:
            return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

// Create a safe reference to stats that includes all required properties
const safeStats = computed(() => ({
    ...page.props.stats
}));

const props = defineProps<{
    stats: any;
    systemStatus: any;
    activities: any;
    filters: any;
    recentlyDeleted: {
        id: number;
        name: string;
        deleted_at: string;
    }[];
    totalDeleted: number;
}>();

console.log('Dashboard props:', props);

console.log('Landlord Dashboard page props:', page.props);
</script>

<template>
    <Head title="Landlord Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Dashboard</h2>
                </div>
                <Link
                    href="/admin/tenants/create"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all duration-200 ease-in-out"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New School
                </Link>
            </div>

            <!-- Tabs -->
            <nav class="mt-4 -mb-px flex space-x-8 border-b border-gray-200">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        activeTab === tab.id
                            ? 'border-indigo-500 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm'
                    ]"
                >
                    <svg
                        class="mr-2 h-5 w-5"
                        :class="activeTab === tab.id ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        :stroke-width="activeTab === tab.id ? 2 : 1.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" />
                    </svg>
                    {{ tab.name }}
                </button>
            </nav>
        </template>

        <!-- Content -->
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                <!-- Main content grid -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <!-- Main content (2 columns) -->
                    <div class="lg:col-span-2">
                        <!-- Error Alert -->
                        <div v-if="page.props.error" class="mb-6">
                            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">{{ page.props.error }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="space-y-6">
                            <!-- Overview Tab -->
                            <div v-show="activeTab === 'overview'" class="space-y-6">
                                <!-- Stats Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="bg-white rounded-lg shadow p-6">
                                        <h3 class="text-lg font-medium text-gray-900">Total Schools</h3>
                                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ safeStats.total_tenants }}</p>
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-green-600">{{ safeStats.active_tenants }} Active</span>
                                                <span class="text-red-600">{{ safeStats.tenants_without_subscription }} Without Subscription</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- System Status -->
                                    <div class="bg-white rounded-lg shadow p-6">
                                        <h3 class="text-lg font-medium text-gray-900">System Status</h3>
                                        <div class="mt-4 space-y-3">
                                            <div v-for="(status, service) in page.props.systemStatus" :key="service" class="flex justify-between">
                                                <span class="text-sm text-gray-600 capitalize">{{ service }}</span>
                                                <span :class="['text-sm font-medium', getStatusColor(status)]">{{ status }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recent Activity -->
                                    <div class="bg-white rounded-lg shadow p-6">
                                        <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                                        <div class="mt-4 flow-root">
                                            <ul class="-mb-8">
                                                <li v-for="activity in page.props.activities.data.slice(0, 3)" :key="activity.id" class="relative pb-8">
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.type)" />
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div>
                                                                <div class="text-sm">
                                                                    <span class="font-medium text-gray-900">{{ activity.user.name }}</span>
                                                                    <span class="text-gray-500"> {{ activity.description }}</span>
                                                                </div>
                                                                <p class="mt-0.5 text-sm text-gray-500">
                                                                    {{ formatDate(activity.created_at) }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            
                                <!-- Subscription Health -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Health</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-green-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-green-800">Active Rate</h4>
                                            <p class="mt-2 text-2xl font-semibold text-green-900">
                                                {{ Math.round(safeStats.subscription_health.active_rate) }}%
                                            </p>
                                        </div>
                                        <div class="bg-red-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-red-800">Churn Rate</h4>
                                            <p class="mt-2 text-2xl font-semibold text-red-900">
                                                {{ Math.round(safeStats.subscription_health.churn_rate) }}%
                                            </p>
                                        </div>
                                        <div class="bg-blue-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-blue-800">Renewal Rate</h4>
                                            <p class="mt-2 text-2xl font-semibold text-blue-900">
                                                {{ Math.round(safeStats.subscription_health.renewal_rate) }}%
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Schools -->
                                <div class="bg-white rounded-lg shadow">
                                    <div class="p-6 border-b border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <h2 class="text-xl font-semibold text-gray-900">Recent Schools</h2>
                                            <Link
                                                href="/admin/tenants"
                                                class="text-sm text-indigo-600 hover:text-indigo-900"
                                            >
                                                View All
                                            </Link>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        School Name
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Email
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Joined
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="tenant in safeStats.recent_tenants.data" :key="tenant.id">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-10 w-10">
                                                                <img
                                                                    v-if="tenant.logo_url"
                                                                    :src="tenant.logo_url"
                                                                    :alt="tenant.name"
                                                                    class="h-10 w-10 rounded-full"
                                                                >
                                                                <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                                    <span class="text-xl font-bold text-gray-600">
                                                                        {{ tenant.name.charAt(0) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-gray-900">
                                                                    {{ tenant.name }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ tenant.email }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">
                                                            {{ formatDate(tenant.created_at) }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        <Link
                                                            :href="`/admin/tenants/${tenant.id}`"
                                                            class="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            View Details
                                                        </Link>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Subscriptions Tab -->
                            <div v-show="activeTab === 'subscriptions'" class="space-y-6">
                                <!-- Status Overview -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Status</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-green-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-green-800">Active Subscriptions</h4>
                                            <p class="mt-2 text-2xl font-semibold text-green-900">
                                                {{ safeStats.status_breakdown.active }}
                                            </p>
                                        </div>
                                        <div class="bg-yellow-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-yellow-800">Trial Subscriptions</h4>
                                            <p class="mt-2 text-2xl font-semibold text-yellow-900">
                                                {{ safeStats.status_breakdown.trial }}
                                            </p>
                                        </div>
                                        <div class="bg-red-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-red-800">Expired Subscriptions</h4>
                                            <p class="mt-2 text-2xl font-semibold text-red-900">
                                                {{ safeStats.status_breakdown.expired }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subscription Alerts -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Alerts</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-yellow-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-yellow-800">Expiring Soon</h4>
                                            <p class="mt-2 text-2xl font-semibold text-yellow-900">
                                                {{ safeStats.status_breakdown.expiring_soon }}
                                            </p>
                                            <p class="mt-1 text-sm text-yellow-600">
                                                Subscriptions expiring in the next 30 days
                                            </p>
                                        </div>
                                        <div class="bg-orange-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-orange-800">Canceling</h4>
                                            <p class="mt-2 text-2xl font-semibold text-orange-900">
                                                {{ safeStats.status_breakdown.canceling }}
                                            </p>
                                            <p class="mt-1 text-sm text-orange-600">
                                                Active subscriptions scheduled for cancellation
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Renewal Tracking -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Renewal Tracking</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-blue-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-blue-800">Last 30 Days</h4>
                                            <p class="mt-2 text-2xl font-semibold text-blue-900">
                                                {{ safeStats.renewal_tracking.last_30_days }}
                                            </p>
                                            <p class="mt-1 text-sm text-blue-600">
                                                New and renewed subscriptions
                                            </p>
                                        </div>
                                        <div class="bg-indigo-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-indigo-800">Last 90 Days</h4>
                                            <p class="mt-2 text-2xl font-semibold text-indigo-900">
                                                {{ safeStats.renewal_tracking.last_90_days }}
                                            </p>
                                            <p class="mt-1 text-sm text-indigo-600">
                                                New and renewed subscriptions
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Revenue Tab -->
                            <div v-show="activeTab === 'revenue'" class="space-y-6">
                                <!-- Revenue Overview -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue Overview</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-green-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-green-800">Monthly Recurring Revenue</h4>
                                            <p class="mt-2 text-2xl font-semibold text-green-900">
                                                {{ formatCurrency(safeStats.revenue_metrics.monthly_recurring_revenue) }}
                                            </p>
                                        </div>
                                        <div class="bg-blue-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-blue-800">Average Revenue per Tenant</h4>
                                            <p class="mt-2 text-2xl font-semibold text-blue-900">
                                                {{ formatCurrency(safeStats.revenue_metrics.average_revenue_per_tenant) }}
                                            </p>
                                        </div>
                                        <div class="bg-indigo-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-indigo-800">Projected Annual Revenue</h4>
                                            <p class="mt-2 text-2xl font-semibold text-indigo-900">
                                                {{ formatCurrency(safeStats.revenue_metrics.projected_annual_revenue) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Metrics Tab -->
                            <div v-show="activeTab === 'metrics'" class="space-y-6">
                                <!-- Time Metrics -->
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Time-based Metrics</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-purple-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-purple-800">Average Time to Conversion</h4>
                                            <p class="mt-2 text-2xl font-semibold text-purple-900">
                                                {{ Math.round(safeStats.time_metrics.average_time_to_conversion) }} days
                                            </p>
                                        </div>
                                        <div class="bg-indigo-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-indigo-800">Average Subscription Age</h4>
                                            <p class="mt-2 text-2xl font-semibold text-indigo-900">
                                                {{ Math.round(safeStats.time_metrics.average_subscription_age) }} days
                                            </p>
                                        </div>
                                        <div class="bg-blue-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-blue-800">Average Renewal Interval</h4>
                                            <p class="mt-2 text-2xl font-semibold text-blue-900">
                                                {{ Math.round(safeStats.renewal_tracking.average_renewal_interval) }} days
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination sections -->
                            <div v-if="safeStats.recent_tenants?.links?.length" class="mt-4">
                                <Pagination :links="safeStats.recent_tenants.links" />
                            </div>

                            <div v-if="page.props.activities?.links?.length" class="mt-4">
                                <Pagination :links="page.props.activities.links" />
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar (1 column) -->
                    <div>
                        <DeletedSchoolsWidget
                            :recently-deleted="props.recentlyDeleted"
                            :total-deleted="props.totalDeleted"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>