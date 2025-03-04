<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

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
                                    <div class="text-2xl font-semibold text-gray-900">0</div>
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
                                <dd class="mt-1 text-sm text-gray-900">{{ tenant.domain }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ tenant.email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ tenant.phone }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ tenant.address }}</dd>
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
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.usage.storage_used }}</dd>
                        </div>
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Files</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.usage.file_count }}</dd>
                        </div>
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Last Activity</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ stats.usage.last_activity }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium text-gray-900">Subscription Information</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Current subscription plan and billing details.</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Plan</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ tenant.subscription_plan }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span :class="[
                                        tenant.subscription?.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                                    ]">
                                        {{ tenant.subscription?.status || 'No subscription' }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Trial Ends</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tenant.subscription?.trial_ends_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Subscription Ends</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tenant.subscription?.ends_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Price</dt>
                                <dd class="mt-1 text-sm text-gray-900">${{ tenant.subscription?.price || '0.00' }}/month</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ tenant.subscription?.payment_method || 'None' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Features</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li v-for="feature in parseFeatures(tenant.subscription?.features)" :key="feature">
                                            {{ feature }}
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
        </div>
    </div>
</template>