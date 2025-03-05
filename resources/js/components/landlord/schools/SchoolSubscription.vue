<script setup lang="ts">
interface Invoice {
    id: number;
    number: string;
    amount: string;
    status: string;
    billing_period_start: string;
    billing_period_end: string;
    paid_at: string | null;
    payment_method: string | null;
    total: string;
}

interface Subscription {
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
    cancels_at: string | null;
    invoices: Invoice[];
    plan: {
        name: string;
        slug: string;
    };
}

interface Props {
    tenant: {
        subscription?: Subscription;
        past_subscriptions?: Subscription[];
        subscription_plan: string;
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

const getStatusColor = (status: string) => {
    const colors = {
        'paid': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'overdue': 'bg-red-100 text-red-800',
        'canceled': 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <div class="space-y-6">
        <!-- Current Plan -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="sm:flex sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Current Plan</h3>
                        <div class="mt-2 max-w-xl text-sm text-gray-500">
                            <p>Your subscription plan and billing details.</p>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex sm:flex-shrink-0 sm:items-center">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-sm"
                            @click="$emit('changePlan', 'premium')"
                        >
                            Change Plan
                        </button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Current Plan</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ tenant.subscription_plan }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span :class="[
                                tenant.subscription?.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium'
                            ]">
                                {{ tenant.subscription?.status || 'No subscription' }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Price</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ tenant.subscription?.price || '0.00' }}/month</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Next Payment</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tenant.subscription?.next_payment_at) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Features</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <li v-for="feature in parseFeatures(tenant.subscription?.features)" :key="feature" class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ feature }}
                                </li>
                            </ul>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Billing History -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Billing History</h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>View your billing history and download invoices.</p>
                </div>
                
                <!-- Current Subscription Invoices -->
                <div v-if="tenant.subscription?.invoices?.length" class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Current Subscription</h4>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Invoice Number</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Period</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="invoice in tenant.subscription.invoices" :key="invoice.id">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900">{{ invoice.number }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ formatDate(invoice.billing_period_start) }} - {{ formatDate(invoice.billing_period_end) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">${{ invoice.total }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span :class="[getStatusColor(invoice.status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                            {{ invoice.status }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a 
                                            :href="`/admin/invoices/${invoice.id}/download`"
                                            class="text-indigo-600 hover:text-indigo-900"
                                            target="_blank"
                                        >
                                            Download PDF
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Past Subscriptions -->
                <div v-if="tenant.past_subscriptions?.length" class="mt-8">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Past Subscriptions</h4>
                    <div v-for="subscription in tenant.past_subscriptions" :key="subscription.id" class="mb-8">
                        <div class="bg-gray-50 px-4 py-3 rounded-t-lg border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">{{ subscription.plan.name }}</h5>
                                    <p class="text-xs text-gray-500">
                                        {{ formatDate(subscription.starts_at) }} - {{ formatDate(subscription.ends_at) }}
                                    </p>
                                </div>
                                <span :class="[getStatusColor(subscription.status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                    {{ subscription.status }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Invoice Number</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Period</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="invoice in subscription.invoices" :key="invoice.id">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900">{{ invoice.number }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ formatDate(invoice.billing_period_start) }} - {{ formatDate(invoice.billing_period_end) }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">${{ invoice.total }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <span :class="[getStatusColor(invoice.status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                                {{ invoice.status }}
                                            </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <a 
                                                :href="`/admin/invoices/${invoice.id}/download`"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                target="_blank"
                                            >
                                                Download PDF
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- No Billing History -->
                <div v-if="!tenant.subscription?.invoices?.length && !tenant.past_subscriptions?.length" class="mt-6">
                    <p class="text-sm text-gray-500 italic">No billing history available.</p>
                </div>
            </div>
        </div>
    </div>
</template>