<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

interface Plan {
    id: number;
    name: string;
    description: string;
    price: number;
    billing_period: string;
    trial_period_days: number | null;
    features: string[];
    is_active: boolean;
}

interface Tenant {
    id: number;
    name: string;
}

interface Props {
    tenants: Tenant[];
    plans: Plan[];
}

const { tenants, plans } = defineProps<Props>();

const form = useForm({
    tenant_id: '',
    plan_id: '' as string | number,
    starts_at: new Date().toISOString().split('T')[0],
    ends_at: '',
    trial_ends_at: '',
    price: 0,
    features: [] as string[],
    payment_method: 'credit_card',
    payment_method_id: ''
});

const selectedPlan = ref<Plan | null>(null);

const selectPlan = (plan: Plan) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id.toString();
    form.price = plan.price;
    form.features = plan.features;
    
    if (plan.trial_period_days) {
        const trialEnd = new Date();
        trialEnd.setDate(trialEnd.getDate() + plan.trial_period_days);
        form.trial_ends_at = trialEnd.toISOString().split('T')[0];
    } else {
        form.trial_ends_at = '';
    }
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard'
    },
    {
        title: 'Subscriptions',
        href: route('admin.subscriptions.index')
    },
    {
        title: 'Create Subscription',
        href: route('admin.subscriptions.create')
    }
];

const submit = () => {
    form.post(route('admin.subscriptions.store'));
};
</script>

<template>
    <AppLayout title="Create Subscription" :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold text-gray-900">Create New Subscription</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Tenant Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">School</label>
                                <select
                                    v-model="form.tenant_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required
                                >
                                    <option value="">Select a school</option>
                                    <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                                        {{ tenant.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Plan Selection -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Select Plan</h3>
                                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div
                                        v-for="plan in plans"
                                        :key="plan.id"
                                        @click="selectPlan(plan)"
                                        :class="[
                                            'relative rounded-lg border p-4 cursor-pointer focus:outline-none',
                                            selectedPlan?.id === plan.id
                                                ? 'border-indigo-500 ring-2 ring-indigo-500'
                                                : 'border-gray-300'
                                        ]"
                                    >
                                        <div class="flex flex-col">
                                            <h4 class="text-lg font-medium text-gray-900">
                                                {{ plan.name }}
                                            </h4>
                                            <p class="mt-1 text-sm text-gray-500">{{ plan.description }}</p>
                                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                                {{ formatPrice(plan.price) }}
                                                <span class="text-sm font-normal text-gray-500">/{{ plan.billing_period }}</span>
                                            </p>
                                            <ul class="mt-4 space-y-2">
                                                <li
                                                    v-for="feature in plan.features"
                                                    :key="feature"
                                                    class="flex items-center"
                                                >
                                                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span class="ml-2 text-sm text-gray-600">{{ feature }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subscription Details -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input
                                        type="date"
                                        v-model="form.starts_at"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input
                                        type="date"
                                        v-model="form.ends_at"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Trial End Date</label>
                                    <input
                                        type="date"
                                        v-model="form.trial_ends_at"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                    <select
                                        v-model="form.payment_method"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="credit_card">Credit Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="paypal">PayPal</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Card Element -->
                            <div v-if="form.payment_method === 'credit_card'" class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Card Details</label>
                                <div id="card-element" class="mt-1 p-3 border rounded-md"></div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition"
                                >
                                    Create Subscription
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 