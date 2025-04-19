<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { AcademicCapIcon } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';

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
    generated_at?: string;
    error?: boolean;
}

interface Subscription {
    id: number;
    status: string;
    starts_at: string;
    ends_at: string;
    trial_ends_at: string;
    price: string;
    features: string[];
    payment_method: string;
    last_payment_at: string | null;
    next_payment_at: string | null;
    cancels_at: string | null;
    invoices: Invoice[];
    plan: {
        name: string;
        slug: string;
        features: string[];
    };
}

interface Props {
    tenant: {
        subscription?: Subscription;
        past_subscriptions?: Subscription[];
        subscription_plan: string;
        logo_url?: string;
        name: string;
        id: number;
    };
}

const props = defineProps<Props>();
const generatingPdf = ref<number | null>(null);
const errorMessage = ref<string | null>(null);
const env = import.meta.env;
const toast = useToast();

const apiUrl = ref(env.VITE_API_URL);
const debugMode = ref(env.MODE === 'development');

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatStatus = (status: string) => {
    const statusMap: Record<string, string> = {
        active: 'Active',
        trial: 'Trial',
        canceled: 'Canceled',
        expired: 'Expired',
        pending: 'Pending'
    };
    return statusMap[status?.toLowerCase()] || 'Unknown';
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        trial: 'bg-blue-100 text-blue-800',
        canceled: 'bg-yellow-100 text-yellow-800',
        expired: 'bg-red-100 text-red-800',
        pending: 'bg-gray-100 text-gray-800'
    };
    return colors[status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const generatePdf = async (invoiceId) => {
    try {
        if (!props.tenant?.id) {
            throw new Error('Tenant ID is missing in component props');
        }
        
        generatingPdf.value = invoiceId;
        errorMessage.value = null;
        
        const response = await axios.post(
            `${apiUrl.value}/admin/tenants/${props.tenant.id}/invoices/${invoiceId}/generate-pdf`,
            {},
            { headers: { 'Content-Type': 'application/json' } }
        );

        if (response.data.success) {
            toast.success('PDF generated successfully');
            // Force reactive update
            const updatedInvoices = props.tenant.subscription?.invoices.map(invoice => {
                if (invoice.id === invoiceId) {
                    return Object.assign({}, invoice, response.data.invoice, { error: false });
                }
                return invoice;
            }) || [];
            
            if (props.tenant.subscription) {
                props.tenant.subscription.invoices = [...updatedInvoices];
            }
        }

    } catch (error) {
        toast.error('PDF generation failed: ' + (error.response?.data?.message || error.message));
        // Update local error state
        const updatedInvoices = props.tenant.subscription?.invoices.map(inv => 
            inv.id === invoiceId ? { ...inv, error: true } : inv
        ) || [];
        
        if (props.tenant.subscription) {
            props.tenant.subscription.invoices = updatedInvoices;
        }
    } finally {
        generatingPdf.value = null;
    }
};

const sendInvoice = async (invoiceId) => {
    try {
        if (!props.tenant?.id) {
            throw new Error('Tenant ID is missing');
        }
        
        const response = await axios.post(
            `${apiUrl.value}/admin/tenants/${props.tenant.id}/invoices/${invoiceId}/send`,
            {},
            {
                headers: {
                    'Content-Type': 'application/json'
                }
            }
        );

        if (response.data.success) {
            toast.success('Invoice sent to tenant admin');
        }
    } catch (error) {
        toast.error('Failed to send invoice: ' + (error.response?.data?.message || error.message));
    }
};

console.log('Initial subscription data:', props.tenant.subscription);
console.log('Invoices count:', props.tenant.subscription?.invoices?.length);
</script>

<template>
    <div class="space-y-6">
        <!-- Current Plan -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <img 
                            v-if="tenant.logo_url"
                            :src="tenant.logo_url" 
                            class="h-12 w-12 rounded-full"
                            :alt="tenant.name"
                        >
                        <div 
                            v-else
                            class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center"
                        >
                            <AcademicCapIcon class="h-6 w-6 text-gray-400" />
                        </div>
                    </div>
                    <div class="flex-1">
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
                                getStatusColor(tenant.subscription?.status),
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                            ]">
                                {{ formatStatus(tenant.subscription?.status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Price</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            ${{ Number(tenant.subscription?.price || 0).toFixed(2) }}/month
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Next Payment</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tenant.subscription?.next_payment_at) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Features</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <li v-for="(feature, index) in tenant.subscription?.plan?.features" 
                                    :key="index" 
                                    class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ feature }}
                                </li>
                                <li v-if="!tenant.subscription?.plan?.features?.length" 
                                    class="text-gray-400 text-sm">
                                    No features specified
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
                    <p>Manage and download your billing invoices.</p>
                </div>
                
                <!-- Current Subscription Invoices -->
                <div v-if="tenant.subscription?.invoices?.length" class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Current Subscription</h4>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Invoice #</th>
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
                                        {{ formatDate(invoice.billing_period_start) }} – {{ formatDate(invoice.billing_period_end) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">${{ Number(invoice.total).toFixed(2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <div class="flex flex-col gap-1">
                                            <span :class="[
                                                invoice.generated_at && !invoice.error 
                                                    ? 'bg-green-100 text-green-800'
                                                    : invoice.error
                                                    ? 'bg-red-100 text-red-800'
                                                    : 'bg-gray-100 text-gray-800',
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                            ]">
                                                {{ invoice.generated_at && !invoice.error ? 'PDF Ready' : invoice.error ? 'Generation Failed' : 'Pending PDF' }}
                                            </span>
                                            <span :class="[
                                                getStatusColor(invoice.status),
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                            ]">
                                                {{ formatStatus(invoice.status) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex items-center justify-end space-x-3">
                                            <!-- Loading State -->
                                            <span v-if="generatingPdf === invoice.id" class="text-gray-500">
                                                Generating...
                                            </span>

                                            <template v-else>
                                                <!-- Successful Generation -->
                                                <template v-if="invoice.generated_at && !invoice.error">
                                                    <a
                                                        :href="`${apiUrl}/admin/invoices/${invoice.id}/download`"
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                        target="_blank"
                                                    >
                                                        Download
                                                    </a>
                                                    <button
                                                        @click="generatePdf(invoice.id)"
                                                        class="text-yellow-600 hover:text-yellow-900"
                                                    >
                                                        Regenerate
                                                    </button>
                                                    <button
                                                        @click="sendInvoice(invoice.id)"
                                                        class="text-green-600 hover:text-green-900"
                                                    >
                                                        Send
                                                    </button>
                                                </template>

                                                <!-- Error State -->
                                                <template v-else-if="invoice.error">
                                                    <button
                                                        @click="generatePdf(invoice.id)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Retry
                                                    </button>
                                                </template>

                                                <!-- Initial Generation -->
                                                <template v-else>
                                                    <button
                                                        @click="generatePdf(invoice.id)"
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        Generate
                                                    </button>
                                                </template>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- No Invoices Message -->
                <div v-else class="mt-6 text-center py-6 text-gray-500">
                    No invoices found for current subscription.
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
                                            <div class="flex flex-col gap-1">
                                                <span :class="[
                                                    invoice.generated_at && !invoice.error 
                                                        ? 'bg-green-100 text-green-800'
                                                        : invoice.error
                                                        ? 'bg-red-100 text-red-800'
                                                        : 'bg-gray-100 text-gray-800',
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                                ]">
                                                    {{ invoice.generated_at && !invoice.error ? 'PDF Ready' : invoice.error ? 'Generation Failed' : 'Pending PDF' }}
                                                </span>
                                                <span :class="[
                                                    getStatusColor(invoice.status),
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                                ]">
                                                    {{ formatStatus(invoice.status) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <a 
                                                :href="`${apiUrl}/admin/invoices/${invoice.id}/download`"
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