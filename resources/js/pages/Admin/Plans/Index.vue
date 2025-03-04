<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { BreadcrumbItem } from '@/types';

// Define our own Plan interface
interface Plan {
    id: number; // Change back to number since that's what the backend uses
    name: string;
    description?: string;
    price: number;
    billing_period: string;
    trial_period_days?: number;
    features: string[];
    is_active: boolean;
    sort_order: number;
}

interface Props {
    plans: {
        data: Plan[];
        links: any[];
    };
}

const props = defineProps<Props>();
const toast = useToast();

// Add this debug line
console.log('Plans data:', props.plans);

// Add breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard'
    },
    {
        title: 'Plans',
        href: route('admin.plans.index')
    }
];

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
};

const getStatusBadgeClass = (isActive: boolean) => {
    return isActive
        ? 'bg-green-100 text-green-800'
        : 'bg-gray-100 text-gray-800';
};

const copyFeatures = async (features: string[]) => {
    try {
        await navigator.clipboard.writeText(features.join('\n'));
        toast.success('Features copied to clipboard');
    } catch {
        toast.error('Failed to copy features');
    }
};

const deletePlan = (planId: number) => {
    router.delete(route('admin.plans.destroy', { id: planId }), {
        onSuccess: () => {
            toast.success('Plan deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete plan');
        },
    });
};

const planPresets = [
    {
        name: 'Basic Plan',
        description: 'Perfect for small schools',
        price: 29.99,
        billing_period: 'monthly',
        trial_period_days: 14,
        features: [
            'Up to 100 students',
            'Basic attendance tracking',
            'Report card generation',
            'Email support'
        ],
        is_active: true,
        sort_order: 1
    },
    {
        name: 'Professional Plan',
        description: 'For growing institutions',
        price: 79.99,
        billing_period: 'monthly',
        trial_period_days: 14,
        features: [
            'Up to 500 students',
            'Advanced attendance tracking',
            'Custom report cards',
            'SMS notifications',
            'Priority support'
        ],
        is_active: true,
        sort_order: 2
    },
    {
        name: 'Enterprise Plan',
        description: 'For large educational institutions',
        price: 199.99,
        billing_period: 'monthly',
        trial_period_days: 14,
        features: [
            'Unlimited students',
            'All Professional features',
            'API access',
            'Custom integrations',
            'Dedicated support',
            'Data analytics'
        ],
        is_active: true,
        sort_order: 3
    }
];

const createPlanFromPreset = (preset: typeof planPresets[0]) => {
    router.get(route('admin.plans.create'), { 
        preset: JSON.stringify(preset)
    });
};
</script>

<template>
    <AppLayout title="Subscription Plans" :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Subscription Plans</h2>
                <div class="relative">
                    <div class="flex space-x-3">
                        <Link
                            :href="route('admin.plans.create')"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                        >
                            Create Custom Plan
                        </Link>
                        <div class="relative group">
                            <button
                                type="button"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                Use Template
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <!-- Dropdown menu -->
                            <div class="absolute right-0 mt-2 w-72 bg-white rounded-md shadow-lg z-10 hidden group-hover:block">
                                <div class="py-1 divide-y divide-gray-100">
                                    <button
                                        v-for="preset in planPresets"
                                        :key="preset.name"
                                        @click="createPlanFromPreset(preset)"
                                        class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors duration-150"
                                    >
                                        <div class="font-medium text-gray-900">{{ preset.name }}</div>
                                        <div class="text-sm text-gray-500">{{ preset.description }}</div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            Starting at {{ formatPrice(preset.price) }}/{{ preset.billing_period }}
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Add this debug output -->
                <pre class="text-sm text-gray-500 mb-4">
                    {{ JSON.stringify(props.plans, null, 2) }}
                </pre>

                <div v-if="!props.plans?.data?.length" class="text-center py-12">
                    <h3 class="text-lg font-medium text-gray-900">No plans found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new plan.</p>
                </div>

                <!-- Plans Grid View -->
                <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div 
                        v-for="plan in props.plans.data" 
                        :key="plan.id"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ plan.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ plan.description }}</p>
                                </div>
                                <span 
                                    :class="[
                                        'px-2 py-1 text-xs font-semibold rounded-full',
                                        getStatusBadgeClass(plan.is_active)
                                    ]"
                                >
                                    {{ plan.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="mt-4">
                                <div class="text-3xl font-bold text-gray-900">
                                    {{ formatPrice(plan.price) }}
                                    <span class="text-sm font-normal text-gray-500">
                                        /{{ plan.billing_period }}
                                    </span>
                                </div>
                                
                                <div class="mt-4 space-y-2">
                                    <div class="flex justify-between text-sm text-gray-500">
                                        <span>Trial Period:</span>
                                        <span>{{ plan.trial_period_days ? `${plan.trial_period_days} days` : 'No trial' }}</span>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Features</h4>
                                    <ul class="space-y-2">
                                        <li 
                                            v-for="feature in plan.features" 
                                            :key="feature"
                                            class="flex items-center text-sm text-gray-600"
                                        >
                                            <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ feature }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-between items-center">
                                <button
                                    @click="copyFeatures(plan.features)"
                                    class="text-sm text-gray-500 hover:text-gray-700"
                                    title="Copy features"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <div class="space-x-2">
                                    <Link
                                        :href="route('admin.plans.edit', { id: plan.id })"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deletePlan(plan.id)"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add this debug output for pagination -->
                <div class="text-sm text-gray-500 mb-4">
                    Pagination links: {{ props.plans?.links?.length }}
                </div>

                <div v-if="props.plans?.links?.length > 2" class="mt-6">
                    <Pagination :links="props.plans.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.group:hover .group-hover\:block {
    display: block;
}

.group-hover\:block {
    display: none;
}
</style> 