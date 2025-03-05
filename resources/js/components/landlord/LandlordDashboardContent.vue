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
    total_tenants: number;
    active_tenants: number;
    tenants_without_subscription: number;
    active: number;
    expiring_soon: number;
    expired: number;
    trial: number;
    monthly_revenue: number;
    total_subscriptions: number;
    health_metrics: {
        active_rate: number;
        churn_rate: number;
        renewal_rate: number;
    };
    status_breakdown: Record<string, number>;
    renewal_tracking: {
        last_30_days: number;
        last_90_days: number;
        average_renewal_interval: number | null;
    };
}

interface Props {
    stats?: {
        total_tenants: number;
        active_tenants: number;
        tenants_without_subscription: number;
        expired_subscription_tenants: number;
        active_subscriptions: number;
        recent_tenants: {
            data: Array<Tenant>;
            links: Array<any>;
        };
        subscription_health: {
            active_rate: number;
            expired_rate: number;
            no_subscription_rate: number;
            average_subscription_length: number;
            renewal_rate: number;
            churn_rate: number;
        };
        status_breakdown: {
            active: number;
            trial: number;
            expiring_soon: number;
            expired: number;
            canceled: number;
            canceling: number;
        };
        renewal_tracking: {
            last_30_days: number;
            last_90_days: number;
            average_renewal_interval: number;
        };
        revenue_metrics: {
            monthly_recurring_revenue: number;
            average_revenue_per_tenant: number;
            projected_annual_revenue: number;
        };
        time_metrics: {
            average_time_to_conversion: number;
            average_subscription_age: number;
        };
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
    stats: () => ({
        total_tenants: 0,
        active_tenants: 0,
        tenants_without_subscription: 0,
        expired_subscription_tenants: 0,
        active_subscriptions: 0,
        recent_tenants: {
            data: [],
            links: []
        },
        subscription_health: {
            active_rate: 0,
            expired_rate: 0,
            no_subscription_rate: 0,
            average_subscription_length: 0,
            renewal_rate: 0,
            churn_rate: 0
        },
        status_breakdown: {
            active: 0,
            trial: 0,
            expiring_soon: 0,
            expired: 0,
            canceled: 0,
            canceling: 0
        },
        renewal_tracking: {
            last_30_days: 0,
            last_90_days: 0,
            average_renewal_interval: 0
        },
        revenue_metrics: {
            monthly_recurring_revenue: 0,
            average_revenue_per_tenant: 0,
            projected_annual_revenue: 0
        },
        time_metrics: {
            average_time_to_conversion: 0,
            average_subscription_age: 0
        }
    }),
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
    filters: () => ({
        search: '',
        status: ''
    })
});
console.log('LandlordDashboardContent props:', props);

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
        icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 00-2-2V7a2 2 0 00-2 2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 002 2z',
        iconClass: 'text-purple-600 bg-purple-100'
    }
];

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
};

// Create a safe reference to stats that includes all required properties
const safeStats = computed(() => ({
    ...props.stats
}));

// Add this after the safeStats computed property
watch(() => safeStats.value, (newStats) => {
    console.log('Frontend Subscription Statistics:', {
        total_tenants: newStats.total_tenants,
        active_tenants: newStats.active_tenants,
        subscription_health: {
            active_rate: newStats.subscription_health.active_rate,
            no_subscription_rate: newStats.subscription_health.no_subscription_rate,
            average_subscription_length: newStats.subscription_health.average_subscription_length
        },
        timestamp: new Date().toISOString()
    });
}, { immediate: true });

// Also log the raw props data
watch(() => props.stats, (newStats) => {
    console.log('Raw Props Data:', JSON.stringify(newStats, null, 2));
    console.log('Computed SafeStats:', JSON.stringify(safeStats.value, null, 2));
}, { immediate: true });

// Add new ref for active tab
const activeTab = ref('overview');

// Define tabs
const tabs = [
    { id: 'overview', name: 'Overview', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { id: 'subscriptions', name: 'Subscriptions', icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z' },
    { id: 'revenue', name: 'Revenue', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { id: 'metrics', name: 'Metrics', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' }
];
</script>

<template>
    <div class="space-y-6">
        <!-- Error Alert -->
        <div v-if="error" class="mb-6">
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
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
        </div>

        <!-- Dashboard Header with Tabs -->
        <div class="flex flex-col space-y-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Dashboard</h2>
                    <p class="mt-1 text-sm text-gray-600">Monitor your subscription and tenant metrics</p>
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
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'group inline-flex items-center px-1 py-4 border-b-2 font-medium text-sm'
                        ]"
                    >
                        <svg
                            class="mr-3 h-5 w-5"
                            :class="activeTab === tab.id ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                        </svg>
                        {{ tab.name }}
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="space-y-6">
            <!-- Stats Overview -->
            <div v-show="activeTab === 'overview'" class="space-y-6">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Total Schools</h3>
                        <p class="text-3xl font-bold text-indigo-600">
                            {{ safeStats.total_tenants }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ safeStats.tenants_without_subscription }} without subscription
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Active Schools</h3>
                        <p class="text-3xl font-bold text-green-600">
                            {{ safeStats.active_tenants }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ safeStats.active_subscriptions }} active subscriptions
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Subscription Rate</h3>
                        <p class="text-3xl font-bold text-blue-600">
                            {{ Math.round(safeStats.subscription_health.active_rate) }}%
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ safeStats.expired_subscription_tenants }} expired
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Monthly Revenue</h3>
                        <p class="text-3xl font-bold text-emerald-600">
                            {{ formatCurrency(safeStats.revenue_metrics.monthly_recurring_revenue) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ formatCurrency(safeStats.revenue_metrics.average_revenue_per_tenant) }} per school
                        </p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
            </div>

            <div v-show="activeTab === 'subscriptions'" class="space-y-6">
                <!-- Subscription Overview -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-green-800">Active Subscriptions</h4>
                            <p class="mt-2 text-2xl font-semibold text-green-900">
                                {{ safeStats.status_breakdown.active }}
                            </p>
                            <p class="mt-1 text-sm text-green-600">
                                {{ Math.round(safeStats.subscription_health.active_rate) }}% of total
                            </p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-yellow-800">Expiring Soon</h4>
                            <p class="mt-2 text-2xl font-semibold text-yellow-900">
                                {{ safeStats.status_breakdown.expiring_soon }}
                            </p>
                            <p class="mt-1 text-sm text-yellow-600">
                                next 30 days
                            </p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800">Trial Subscriptions</h4>
                            <p class="mt-2 text-2xl font-semibold text-blue-900">
                                {{ safeStats.status_breakdown.trial }}
                            </p>
                            <p class="mt-1 text-sm text-blue-600">
                                {{ Math.round(safeStats.subscription_health.trial_conversion_rate) }}% conversion
                            </p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-red-800">Churned</h4>
                            <p class="mt-2 text-2xl font-semibold text-red-900">
                                {{ safeStats.status_breakdown.canceled }}
                            </p>
                            <p class="mt-1 text-sm text-red-600">
                                {{ Math.round(safeStats.subscription_health.churn_rate) }}% churn rate
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Subscription Status Breakdown -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Status Details</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div v-for="(count, status) in safeStats.status_breakdown" 
                             :key="status"
                             class="bg-gray-50 p-4 rounded-lg"
                        >
                            <h4 class="text-sm font-medium text-gray-800 capitalize">{{ status.replace('_', ' ') }}</h4>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ count }}</p>
                            <p class="mt-1 text-sm text-gray-500">subscriptions</p>
                        </div>
                    </div>
                </div>

                <!-- Subscription Health -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subscription Health</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800">Active Rate</h4>
                            <p class="mt-2 text-2xl font-semibold text-blue-900">
                                {{ Math.round(safeStats.subscription_health.active_rate) }}%
                            </p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-red-800">Churn Rate</h4>
                            <p class="mt-2 text-2xl font-semibold text-red-900">
                                {{ Math.round(safeStats.subscription_health.churn_rate) }}%
                            </p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-green-800">Trial Conversion Rate</h4>
                            <p class="mt-2 text-2xl font-semibold text-green-900">
                                {{ Math.round(safeStats.subscription_health.trial_conversion_rate) }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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

                <!-- Additional Revenue Metrics -->
                <div class="bg-white rounded-lg shadow p-6">
                    <!-- ... any additional revenue metrics ... -->
                </div>
            </div>

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

                <!-- Renewal Tracking -->
                <div class="bg-white rounded-lg shadow p-6">
                    <!-- ... existing renewal tracking ... -->
                </div>
            </div>

            <!-- Filters (if needed) -->
            <div v-if="activeTab === 'overview'" class="bg-white rounded-lg shadow p-6">
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
            <div v-if="safeStats.recent_tenants?.links?.length" class="mt-4">
                <Pagination :links="safeStats.recent_tenants.links" />
            </div>

            <div v-if="activities?.links?.length" class="mt-4">
                <Pagination :links="activities.links" />
            </div>
        </div>
    </div>
</template> 