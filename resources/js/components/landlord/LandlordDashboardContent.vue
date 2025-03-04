<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import type { DebounceSettings } from 'lodash';
import debounce from 'lodash/debounce';
import Pagination from '@/components/Pagination.vue';

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

interface SubscriptionStats {
    active: number;
    expiring_soon: number;
    expired: number;
    trial: number;
    monthly_revenue: number;
    total: number;
}

interface Props {
    stats: {
        total_tenants: number;
        active_tenants: number;
        recent_tenants: {
            data: Array<Tenant>;
            links: Array<any>;
        };
        subscription_stats: SubscriptionStats;
    };
    systemStatus?: Record<string, string>;
    activities?: {
        data: Array<Activity>;
        links: Array<any>;
    };
    filters?: {
        search?: string;
        status?: string;
    };
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    systemStatus: () => ({
        database: 'healthy',
        storage: 'healthy',
        cache: 'healthy',
        queue: 'healthy'
    }),
    activities: () => ({
        data: [],
        links: []
    }),
    filters: () => ({ search: '', status: '' })
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

// Fix debounce typing
watch(search, debounce((value: string) => {
    router.get('/dashboard', { 
        search: value, 
        status: status.value 
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300));

watch(status, (value: string) => {
    router.get('/dashboard', { 
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

const quickActions = [
    {
        title: 'Add New School',
        description: 'Register a new school in the system',
        href: '/admin/tenants/create',
        icon: 'M12 4v16m8-8H4',
        iconClass: 'text-green-600 bg-green-100'
    },
    {
        title: 'Manage Subscriptions',
        description: 'View and manage school subscriptions',
        href: '/admin/subscriptions',
        icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        iconClass: 'text-blue-600 bg-blue-100'
    },
    {
        title: 'Subscription Reports',
        description: 'View subscription analytics and reports',
        href: '/admin/subscriptions/reports',
        icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        iconClass: 'text-purple-600 bg-purple-100'
    }
];

// Update subscription stats to use actual data
const subscriptionStats = computed(() => ({
    total: props.stats.subscription_stats.total ?? 0,
    active: props.stats.subscription_stats.active ?? 0,
    expiringSoon: props.stats.subscription_stats.expiring_soon ?? 0,
    trial: props.stats.subscription_stats.trial ?? 0,
    expired: props.stats.subscription_stats.expired ?? 0,
    monthlyRevenue: props.stats.subscription_stats.monthly_revenue ?? 0
}));

const formatCurrency = (amount: number | null | undefined) => {
    const value = amount ?? 0;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Error Alert -->
        <div v-if="error" class="bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ error }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
            <Link
                href="/admin/tenants/create"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
            >
                Add New School
            </Link>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900">Total Schools</h3>
                <p class="text-3xl font-bold text-indigo-600">{{ stats.total_tenants }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900">Active Schools</h3>
                <p class="text-3xl font-bold text-green-600">{{ stats.active_tenants }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900">Utilization</h3>
                <p class="text-3xl font-bold text-blue-600">
                    {{ Math.round((stats.active_tenants / stats.total_tenants) * 100) }}%
                </p>
            </div>
        </div>

        <!-- Subscription Overview -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-green-800">Active Subscriptions</h4>
                    <p class="mt-2 text-2xl font-semibold text-green-900">
                        {{ subscriptionStats.active }}
                    </p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-yellow-800">Expiring Soon</h4>
                    <p class="mt-2 text-2xl font-semibold text-yellow-900">
                        {{ subscriptionStats.expiringSoon }}
                    </p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-800">Trial Subscriptions</h4>
                    <p class="mt-2 text-2xl font-semibold text-blue-900">
                        {{ subscriptionStats.trial }}
                    </p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-purple-800">Monthly Revenue</h4>
                    <p class="mt-2 text-2xl font-semibold text-purple-900">
                        {{ formatCurrency(subscriptionStats.monthlyRevenue) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <Link
                v-for="action in quickActions"
                :key="action.title"
                :href="action.href"
                class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow duration-200"
            >
                <div class="flex items-center">
                    <div :class="action.iconClass" class="p-3 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ action.title }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ action.description }}</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Search</label>
                    <input
                        type="text"
                        v-model="search"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Search schools..."
                    >
                </div>
                <div class="w-48">
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select
                        v-model="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- System Status and Activity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- System Status -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">System Status</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div v-for="(status, service) in systemStatus" :key="service" class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-600 capitalize">{{ service }}</dt>
                            <dd :class="['text-sm font-medium', getStatusColor(status)]" class="capitalize">
                                {{ status }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Recent Activity</h2>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li v-for="(activity, index) in activities.data" :key="activity.id">
                                <div class="relative pb-8">
                                    <span v-if="index !== activities.data.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.type)" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ activity.action }}
                                                    <span class="text-gray-500">by</span>
                                                    <span class="text-indigo-600">{{ activity.user.name }}</span>
                                                    <span v-if="activity.tenant" class="text-gray-500">
                                                        at {{ activity.tenant.name }}
                                                    </span>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    {{ activity.description }}
                                                </p>
                                                <div v-if="activity.metadata?.changes" class="mt-2 text-xs text-gray-400">
                                                    Changed: {{ Object.keys(activity.metadata.changes.after || {}).join(', ') }}
                                                </div>
                                            </div>
                                            <div class="mt-2 text-sm text-gray-500 flex items-center space-x-2">
                                                <span>{{ formatDate(activity.created_at) }}</span>
                                                <span class="text-gray-400">•</span>
                                                <span class="text-gray-400">{{ activity.user.role }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Schools -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
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
                        <tr v-for="tenant in stats.recent_tenants.data" :key="tenant.id">
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
                                    {{ new Date(tenant.created_at).toLocaleDateString() }}
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

        <!-- Pagination sections with null checks -->
        <div v-if="stats.recent_tenants?.links?.length" class="mt-4">
            <Pagination :links="stats.recent_tenants.links" />
        </div>

        <div v-if="activities?.links?.length" class="mt-4">
            <Pagination :links="activities.links" />
        </div>
    </div>
</template> 