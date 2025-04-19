<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

interface Props {
    tenant: {
        id: number;
        name: string;
        domain: string;
        email: string;
        phone: string;
        address: string;
        logo_url: string;
        status: string;
        school_type: string | null;
        subscription_plan: string;
        subscription?: {
            id: number;
            status: string;
            starts_at: string;
            ends_at: string;
            trial_ends_at: string;
            price: string;
            features: string;
            payment_method: string;
            last_payment_at: string | null;
            next_payment_at: string | null;
            plan?: {
                name: string;
                billing_period: string;
                price: string;
                features: string[];
            };
        };
        admin?: {
            id: number;
            name: string;
            email: string;
            email_verified_at: string | null;
            created_at: string;
            updated_at: string;
        };
    };
    stats: {
        usage: {
            storage_used: string;
            file_count: number;
            last_activity: string;
        };
        tenant: {
            subscription_status: string;
            subscription_ends: string;
            created_at: string;
            domain: string;
            admin_email: string;
            is_active: boolean;
            user_count: number;
        };
    };
}

const props = defineProps<Props>();

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const parseFeatures = (features: string | undefined) => {
    if (!features) return [];
    try {
        return JSON.parse(features);
    } catch {
        return [];
    }
};

const getInitials = (name: string) => {
    return name.split(' ').map(word => word[0]).join('');
};

const statusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        trial: 'bg-blue-100 text-blue-800',
        canceled: 'bg-yellow-100 text-yellow-800',
        expired: 'bg-red-100 text-red-800',
        expiring_soon: 'bg-orange-100 text-orange-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status) => {
    const statusMap = {
        active: 'Active',
        trial: 'Trial',
        canceled: 'Canceled',
        expired: 'Expired',
        expiring_soon: 'Expiring Soon'
    };
    return statusMap[status] || 'Unknown';
};

onMounted(() => {
    // Safe JSON logging
    console.log('Full Tenant Data:', props.tenant);
    console.log('Raw Subscription Data:', props.tenant.subscription);
    
    if(props.tenant.subscription) {
        console.log('Parsed Subscription:', JSON.parse(JSON.stringify(props.tenant.subscription)));
        console.log('Plan Features:', 
            Array.isArray(props.tenant.subscription.plan?.features) 
                ? props.tenant.subscription.plan.features 
                : 'Invalid features format'
        );
    } else {
        console.log('No subscription found');
    }
    
    // Simplified relationship check
    console.log('Subscription exists:', !!props.tenant.subscription);
    console.log('Plan exists:', !!props.tenant.subscription?.plan);
});
</script>

<template>
    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ stats.tenant.user_count }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Storage Used</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ stats.usage.storage_used }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add more quick stat cards -->
        </div>

        <!-- Previous content organized in tabs -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- School Info -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">School Information</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Details and basic information about the school.</p>
                        </div>
                        <span :class="[
                            tenant.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                        ]">
                            {{ tenant.status }}
                        </span>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">School name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ tenant.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Domain</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ tenant.domain || 'Not set' }}
                                    <span v-if="!tenant.domain" class="text-red-500 text-xs">(Missing in database)</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ tenant.email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ tenant.phone || 'Not provided' }}
                                    <span v-if="!tenant.phone" class="text-red-500 text-xs">(Missing in database)</span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                                    <template v-if="tenant.address">
                                        <span v-html="tenant.address"></span>
                                    </template>
                                    <template v-else>
                                        <span class="text-gray-500">No address available</span>
                                        <span class="text-red-500 text-xs block">(Missing in database)</span>
                                    </template>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">School Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ tenant.school_type || 'Not specified' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tenant.created_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="space-y-6">
                <!-- Usage Stats -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">Usage Statistics</h3>
                    <dl class="mt-6 grid grid-cols-1 gap-5">
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Storage Used</dt>
                            <dd class="mt-1">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-3xl font-semibold text-gray-900">{{ stats.usage.storage_used }}</span>
                                    <span class="ml-2 text-sm font-medium text-gray-500">of 5 GB used</span>
                                </div>
                                <div class="mt-2 relative">
                                    <div class="flex h-2 rounded bg-gray-200 overflow-hidden">
                                        <div 
                                            class="bg-indigo-600" 
                                            :style="{ width: `${(parseFloat(stats.usage.storage_used) / 5120) * 100}%` }"
                                            aria-label="Storage usage"
                                        ></div>
                                    </div>
                                </div>
                            </dd>
                        </div>
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Files</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.usage.file_count }}</dd>
                        </div>
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Last Activity</dt>
                            <dd class="mt-1 flex items-center">
                                <span class="text-lg font-semibold text-gray-900">{{ stats.usage.last_activity }}</span>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    {{ stats.tenant.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Subscription Information Card -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium text-gray-900">Subscription Details</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Current plan and billing information</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <!-- Plan Name -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Plan Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ tenant.subscription?.plan?.name || 'No active plan' }}
                                    <span v-if="tenant.subscription?.plan" class="text-gray-500 text-xs block">
                                        ({{ tenant.subscription_plan }})
                                    </span>
                                </dd>
                            </div>

                            <!-- Subscription Status -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span :class="[
                                        tenant.subscription?.status === 'active' ? 'bg-green-100 text-green-800' :
                                        tenant.subscription?.status === 'trial' ? 'bg-blue-100 text-blue-800' :
                                        'bg-yellow-100 text-yellow-800',
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                                    ]">
                                        {{ tenant.subscription?.status?.toUpperCase() || 'INACTIVE' }}
                                    </span>
                                </dd>
                            </div>

                            <!-- Billing Details -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Billing Cycle</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ tenant.subscription?.plan?.billing_period || 'Monthly' }}
                                </dd>
                            </div>

                            <!-- Price -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Price</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    ${{ Number(tenant.subscription?.price || tenant.subscription?.plan?.price || 0).toFixed(2) }}
                                </dd>
                            </div>

                            <!-- Subscription Period -->
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Subscription Period</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(tenant.subscription?.starts_at) }} 
                                    - 
                                    {{ formatDate(tenant.subscription?.ends_at) || 'Ongoing' }}
                                </dd>
                            </div>

                            <!-- Plan Features -->
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Included Features</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li v-for="(feature, index) in tenant.subscription?.plan?.features" 
                                            :key="index">
                                            {{ feature }}
                                        </li>
                                        <li v-if="!tenant.subscription?.plan?.features?.length" class="text-gray-500">
                                            No additional features specified
                                        </li>
                                    </ul>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Admin Details -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg font-medium text-gray-900">Administrator</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">School administrator contact information.</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ tenant.admin?.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ tenant.admin?.email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email Verification</dt>
                            <dd class="mt-1">
                                <span :class="[
                                    tenant.admin?.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                                ]">
                                    {{ tenant.admin?.email_verified_at ? 'Verified' : 'Not Verified' }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tenant.admin?.created_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Add subscription timeline -->
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h4 class="text-sm font-medium text-gray-900 mb-4">Subscription Timeline</h4>
                <div class="flow-root">
                    <ul class="-mb-8">
                        <li>
                            <div class="relative pb-8">
                                <div class="relative flex items-start space-x-3">
                                    <div class="relative">
                                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5">
                                        <div class="text-sm text-gray-500">
                                            <span class="font-medium text-gray-900">Subscription Started</span>
                                            {{ formatDate(tenant.subscription?.starts_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="relative pb-8">
                                <div class="relative flex items-start space-x-3">
                                    <div class="relative">
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5">
                                        <div class="text-sm text-gray-500">
                                            <span class="font-medium text-gray-900">Next Payment Due</span>
                                            {{ formatDate(tenant.subscription?.next_payment_at) || 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Admin Contact Actions -->
            <div class="mt-6 border-t border-gray-200 pt-6">
                <div class="flex space-x-3">
                    <button 
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Send Message
                    </button>
                    <button 
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        View Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this at the bottom of the template -->
    <div v-if="false" class="hidden debug-info">
        <h3 class="text-red-500 font-bold">Debug Data</h3>
        <pre>{{ JSON.stringify(tenant, null, 2) }}</pre>
        <pre>{{ JSON.stringify(stats, null, 2) }}</pre>
    </div>
</template>