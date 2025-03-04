<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Modal from '@/components/Modal.vue';
import { BreadcrumbItem } from '@/types';

interface Plan {
    id: number;
    name: string;
    description: string;
    price: number;
    features: string[];
}

interface Tenant {
    id: number;
    name: string;
}

interface Subscription {
    id: number;
    tenant: Tenant;
    plan: Plan;
    status: string;
    starts_at: string;
    ends_at: string | null;
    trial_ends_at: string | null;
    price: number;
    features: string[];
    payment_method: string;
    last_payment_at: string | null;
    next_payment_at: string | null;
    plan_id: number;
}

interface Props {
    subscription: Subscription;
    availablePlans: Array<{
        id: string;
        name: string;
        price: number;
        features: string[];
    }>;
}

const props = defineProps<Props>();
const showCancelModal = ref(false);
const showRenewModal = ref(false);

const form = useForm({
    plan: props.subscription?.plan?.id ?? '',
    ends_at: props.subscription?.ends_at ?? '',
    price: props.subscription?.price ?? 0,
    features: props.subscription?.features ?? [],
});

const cancelForm = useForm({
    cancellation_reason: '',
    immediate: false,
});

const renewForm = useForm({
    duration_months: 12,
    price: props.subscription?.price ?? 0,
});

const updateSubscription = () => {
    form.put(`/admin/subscriptions/${props.subscription.id}`, {
        preserveScroll: true,
    });
};

const cancelSubscription = () => {
    cancelForm.delete(`/admin/subscriptions/${props.subscription.id}`, {
        preserveScroll: true,
        onSuccess: () => showCancelModal.value = false,
    });
};

const renewSubscription = () => {
    renewForm.post(`/admin/subscriptions/${props.subscription.id}/renew`, {
        preserveScroll: true,
        onSuccess: () => showRenewModal.value = false,
    });
};

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
        title: 'View Subscription',
        href: route('admin.subscriptions.show', { id: props.subscription.id })
    }
];
</script>

<template>
    <AppLayout title="View Subscription" :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">
                    Subscription Details
                </h2>
                <Link
                    :href="route('admin.subscriptions.index')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">School</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ subscription.tenant.name }}</dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Plan</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ subscription.plan?.name ?? 'N/A' }}
                                    <span v-if="!subscription.plan" class="text-xs text-red-500">
                                        (Plan ID: {{ subscription.plan_id }})
                                    </span>
                                </dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span :class="[
                                        subscription.status === 'active' ? 'text-green-800 bg-green-100' :
                                        subscription.status === 'trial' ? 'text-blue-800 bg-blue-100' :
                                        subscription.status === 'cancelled' ? 'text-yellow-800 bg-yellow-100' :
                                        subscription.status === 'expired' ? 'text-red-800 bg-red-100' :
                                        'text-gray-800 bg-gray-100',
                                        'px-2 py-1 rounded-full text-xs'
                                    ]">
                                        {{ subscription.status }}
                                    </span>
                                </dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Price</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatPrice(subscription.price) }}</dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(subscription.starts_at) }}</dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(subscription.ends_at) }}</dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Trial End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(subscription.trial_ends_at) }}</dd>
                            </div>

                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ subscription.payment_method }}</dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Features</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li v-for="feature in subscription.features" :key="feature">
                                            {{ feature }}
                                        </li>
                                    </ul>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Subscription Actions
                </h2>
                <div class="flex space-x-3">
                    <button
                        v-if="subscription.status === 'active'"
                        @click="showCancelModal = true"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    >
                        Cancel Subscription
                    </button>
                    <button
                        v-if="['expired', 'cancelled'].includes(subscription.status)"
                        @click="showRenewModal = true"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                    >
                        Renew Subscription
                    </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Details Form -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <form @submit.prevent="updateSubscription">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Plan</label>
                                    <select
                                        v-model="form.plan"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option v-for="plan in availablePlans" :key="plan.id" :value="plan.id">
                                            {{ plan.name }} - {{ formatPrice(plan.price) }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input
                                        type="date"
                                        v-model="form.ends_at"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Update Subscription
                                </button>
                            </div>
                        </form>
            </div>
        </div>

        <!-- Cancel Modal -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">Cancel Subscription</h3>
                <form @submit.prevent="cancelSubscription" class="mt-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cancellation Reason</label>
                        <textarea
                            v-model="cancelForm.cancellation_reason"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>
                    <div class="mt-4">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="cancelForm.immediate"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <span class="ml-2 text-sm text-gray-600">Cancel Immediately</span>
                        </label>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showCancelModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="cancelForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700"
                        >
                            Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Renew Modal -->
        <Modal :show="showRenewModal" @close="showRenewModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">Renew Subscription</h3>
                <form @submit.prevent="renewSubscription" class="mt-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duration (Months)</label>
                        <select
                            v-model="renewForm.duration_months"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="1">1 Month</option>
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Price</label>
                        <input
                            type="number"
                            v-model="renewForm.price"
                            step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showRenewModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="renewForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700"
                        >
                            Renew Subscription
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template> 