<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Subscription {
    id: number;
    plan: {
        name: string;
        price: number;
        features: string[];
    };
    status: string;
    starts_at: string;
    ends_at: string | null;
    trial_ends_at: string | null;
    price: number;
}

interface Props {
    schoolId: number;
    subscription: Subscription | null;
}

defineProps<Props>();

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

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active': return 'text-green-800 bg-green-100';
        case 'trial': return 'text-blue-800 bg-blue-100';
        case 'expired': return 'text-red-800 bg-red-100';
        default: return 'text-gray-800 bg-gray-100';
    }
};
</script>

<template>
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Subscription</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage school's subscription and billing
                    </p>
                </div>
                <Link
                    v-if="!subscription"
                    :href="route('admin.subscriptions.create', { tenant_id: schoolId })"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    Add Subscription
                </Link>
            </div>

            <div v-if="subscription" class="mt-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-base font-medium text-gray-900">
                            {{ subscription.plan.name }}
                        </h4>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ formatPrice(subscription.price) }} / month
                        </p>
                    </div>
                    <span
                        :class="[
                            getStatusColor(subscription.status),
                            'px-2 py-1 text-xs rounded-full'
                        ]"
                    >
                        {{ subscription.status }}
                    </span>
                </div>

                <dl class="mt-6 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ formatDate(subscription.starts_at) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">End Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ formatDate(subscription.ends_at) }}
                        </dd>
                    </div>

                    <div v-if="subscription.trial_ends_at">
                        <dt class="text-sm font-medium text-gray-500">Trial Ends</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ formatDate(subscription.trial_ends_at) }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900">Features</h4>
                    <ul class="mt-2 space-y-2">
                        <li
                            v-for="feature in subscription.plan.features"
                            :key="feature"
                            class="flex items-start"
                        >
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="ml-2 text-sm text-gray-500">{{ feature }}</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <Link
                        :href="route('admin.subscriptions.show', { id: subscription.id })"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        View Details
                    </Link>
                </div>
            </div>

            <div v-else class="mt-6">
                <div class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No subscription</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a subscription plan.</p>
                </div>
            </div>
        </div>
    </div>
</template> 