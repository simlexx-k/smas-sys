<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { UserCircleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Props {
    tenant?: {
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
    schoolTypes: Record<string, string>;
    subscriptionPlans: Array<{
        slug: string;
        name: string;
        price: number;
        features: string[];
        trial_period_days: number;
    }>;
    statuses: Record<string, string>;
}

const props = defineProps<Props>();

// Add console logs to debug props
console.log('SchoolForm Props:', {
    tenant: props.tenant,
    schoolTypes: props.schoolTypes,
    subscriptionPlans: props.subscriptionPlans,
    statuses: props.statuses
});

const Tenant = {
    STATUS_ACTIVE: 'active',
    STATUS_INACTIVE: 'inactive',
    STATUS_SUSPENDED: 'suspended'
};

const Subscription = {
    STATUS_ACTIVE: 'active',
    STATUS_EXPIRED: 'expired',
    STATUS_CANCELED: 'canceled',
    STATUS_TRIAL: 'trial'
};

const form = useForm({
    // Basic tenant information
    name: props.tenant?.name ?? '',
    domain: props.tenant?.domain ?? '',
    email: props.tenant?.email ?? '',
    phone: props.tenant?.phone ?? '',
    address: props.tenant?.address ?? '',
    logo: null as File | null,
    logo_url: props.tenant?.logo_url ?? '',
    status: props.tenant?.status ?? Tenant.STATUS_ACTIVE,
    school_type: props.tenant?.school_type ?? '',
    
    // Subscription information (align with Subscription model)
    subscription: {
        plan_slug: props.tenant?.subscription?.plan?.slug ?? 'basic',
        starts_at: props.tenant?.subscription?.starts_at ?? new Date().toISOString().slice(0, 16),
        trial_ends_at: props.tenant?.subscription?.trial_ends_at ?? new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().slice(0, 16),
        status: props.tenant?.subscription?.status ?? Subscription.STATUS_ACTIVE
    },
    
    // Admin information
    admin: {
        name: props.tenant?.admin?.name ?? '',
        email: props.tenant?.admin?.email ?? '',
        password: '', // Only required for new tenants
    }
});

// Fetch active plans from backend
const activePlans = ref<Array<Plan>>([]);
const loadingPlans = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get(route('admin.plans.active'));
        activePlans.value = response.data;
        
        // Set default plan if creating new tenant
        if (!props.tenant && activePlans.value.length > 0) {
            form.subscription.plan_slug = activePlans.value[0].slug;
        }
    } catch (error) {
        console.error('Error fetching plans:', error);
    } finally {
        loadingPlans.value = false;
    }
});

// Add validation rules
const validateDates = () => {
    if (new Date(form.subscription.trial_ends_at) < new Date(form.subscription.starts_at)) {
        form.errors.subscription = {
            trial_ends_at: 'Trial end date must be after subscription start date'
        };
        return false;
    }
    return true;
};

const logoInput = ref(null);

const updateLogoPreview = () => {
    const file = logoInput.value.files[0];
    if (!file) return;
    
    // Clear existing logo URL if replacing
    if (form.logo_url && form.logo_url.startsWith('blob:')) {
        URL.revokeObjectURL(form.logo_url);
    }
    
    // Show preview using object URL
    form.logo_url = URL.createObjectURL(file);
    form.logo = file;
};

// Add after form initialization
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationType = ref('success');

// Watch for page flash messages
watch(() => usePage().props.flash, (newVal) => {
    if (newVal.success || newVal.error) {
        notificationMessage.value = newVal.success || newVal.error;
        notificationType.value = newVal.success ? 'success' : 'error';
        showNotification.value = true;
        
        setTimeout(() => {
            showNotification.value = false;
        }, 5000);
    }
});

// Update submit handler
const submit = () => {
    if (!validateDates()) return;

    const formData = new FormData();
    
    // Append tenant fields
    Object.entries({
        name: form.name,
        domain: form.domain,
        email: form.email,
        phone: form.phone,
        address: form.address,
        status: form.status,
        school_type: form.school_type,
        logo: form.logo,
    }).forEach(([key, value]) => {
        if (value !== null) formData.append(key, value);
    });

    // Append nested data
    Object.entries(form.subscription).forEach(([key, value]) => {
        formData.append(`subscription[${key}]`, value);
    });

    Object.entries(form.admin).forEach(([key, value]) => {
        formData.append(`admin[${key}]`, value);
    });

    if (props.tenant) {
        form.submit('put', route('admin.tenants.update', props.tenant.id), {
            data: formData,
            onSuccess: () => {
                form.defaults({
                    // Reset form fields if needed
                });
            },
            onError: (errors) => {
                console.error('Update failed:', errors);
            },
        });
    } else {
        form.submit('post', route('admin.tenants.store'), {
            data: formData,
            onSuccess: () => {
                form.reset();
            },
            onError: (errors) => {
                console.error('Creation failed:', errors);
            },
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="bg-white shadow-lg rounded-xl" enctype="multipart/form-data">
        <div class="space-y-8 p-6 sm:p-8">
            <!-- Basic Information Section -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-xl font-semibold leading-7 text-gray-900 mb-4">School Details</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- School Name -->
                    <div class="col-span-full sm:col-span-2 lg:col-span-3">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">School Name *</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            :class="['block w-full rounded-md shadow-sm', 
                                   form.errors.name ? 'border-red-500 focus:ring-red-500 focus:border-red-500' 
                                   : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500']"
                        >
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Domain and Contact Info -->
                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-700 mb-1">Domain *</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">
                                https://
                            </span>
                            <input
                                id="domain"
                                v-model="form.domain"
                                type="text"
                                required
                                class="block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>
                        <p v-if="form.errors.domain" class="mt-2 text-sm text-red-600">
                            {{ form.errors.domain }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Contact Email *</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600">
                            {{ form.errors.phone }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Admin Information Section -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-xl font-semibold leading-7 text-gray-900 mb-4">Administrator Account</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Admin Name -->
                    <div>
                        <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input
                            id="admin_name"
                            v-model="form.admin.name"
                            type="text"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        <p v-if="form.errors.admin?.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.admin?.name }}
                        </p>
                    </div>

                    <!-- Admin Email -->
                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input
                            id="admin_email"
                            v-model="form.admin.email"
                            type="email"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        <p v-if="form.errors.admin?.email" class="mt-2 text-sm text-red-600">
                            {{ form.errors.admin?.email }}
                        </p>
                    </div>

                    <!-- Admin Password -->
                    <div v-if="!props.tenant" class="sm:col-span-2">
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">Temporary Password *</label>
                        <input
                            id="admin_password"
                            v-model="form.admin.password"
                            type="password"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        <p class="mt-2 text-sm text-gray-500">Must be at least 8 characters</p>
                        <p v-if="form.errors.admin?.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.admin?.password }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subscription Section -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-xl font-semibold leading-7 text-gray-900 mb-4">Subscription Details</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Plan Selection -->
                    <div>
                        <label for="subscription_plan" class="block text-sm font-medium text-gray-700 mb-1">Plan *</label>
                        <select
                            id="subscription_plan"
                            v-model="form.subscription.plan_slug"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :disabled="loadingPlans"
                        >
                            <option 
                                v-for="plan in activePlans" 
                                :key="plan.slug"
                                :value="plan.slug"
                            >
                                {{ plan.name }} - ${{ plan.price }}/month
                            </option>
                            <option v-if="activePlans.length === 0" disabled>
                                No available plans
                            </option>
                        </select>
                        <p v-if="loadingPlans" class="mt-2 text-sm text-gray-500">
                            Loading available plans...
                        </p>
                    </div>

                    <!-- Trial Period Display -->
                    <div v-if="selectedPlan" class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-700">
                            Includes {{ selectedPlan.trial_period_days }}-day free trial
                        </p>
                        <ul class="mt-2 list-disc list-inside text-sm text-blue-600">
                            <li v-for="feature in selectedPlan.features" :key="feature">
                                {{ feature }}
                            </li>
                        </ul>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Account Status *</label>
                        <select
                            id="status"
                            v-model="form.status"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option 
                                v-for="(label, value) in statuses" 
                                :key="value" 
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- School Type -->
                    <div>
                        <label for="school_type" class="block text-sm font-medium text-gray-700 mb-1">School Type *</label>
                        <select
                            id="school_type"
                            v-model="form.school_type"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">Select type</option>
                            <option 
                                v-for="(label, value) in schoolTypes" 
                                :key="value" 
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Subscription Dates -->
                    <div class="sm:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="subscription_starts_at" class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                                <input
                                    id="subscription_starts_at"
                                    v-model="form.subscription.starts_at"
                                    type="datetime-local"
                                    required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                            </div>
                            
                            <div>
                                <label for="trial_ends_at" class="block text-sm font-medium text-gray-700 mb-1">Trial End Date</label>
                                <input
                                    id="trial_ends_at"
                                    v-model="form.subscription.trial_ends_at"
                                    type="datetime-local"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                            </div>
                        </div>
                        <p v-if="form.errors.subscription" class="text-sm text-red-600">
                            {{ form.errors.subscription.trial_ends_at }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Logo Upload Section -->
            <div class="pb-6">
                <h3 class="text-xl font-semibold leading-7 text-gray-900 mb-4">School Logo</h3>
                <div class="flex items-center gap-8">
                    <div class="shrink-0 relative group">
                        <img 
                            v-if="form.logo_url" 
                            :src="form.logo_url"
                            class="h-24 w-24 rounded-full object-cover border-2 border-gray-200 transition-opacity group-hover:opacity-75"
                        >
                        <UserCircleIcon v-else class="h-24 w-24 text-gray-300" />
                        
                        <!-- Remove button for existing logos -->
                        <button
                            v-if="props.tenant?.logo_url && form.logo_url === props.tenant.logo_url"
                            type="button"
                            @click="form.logo_url = ''; form.logo = null"
                            class="absolute -top-2 -right-2 p-1 bg-red-500 rounded-full text-white hover:bg-red-600 transition-colors"
                            title="Remove logo"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="relative">
                        <input
                            id="logo"
                            ref="logoInput"
                            type="file"
                            accept="image/png, image/jpeg, image/webp"
                            class="sr-only"
                            @change="updateLogoPreview"
                        >
                        <label 
                            for="logo"
                            class="cursor-pointer rounded-md bg-white py-2 px-3 text-sm font-medium text-indigo-600 
                                   hover:bg-indigo-50 border border-indigo-300 hover:border-indigo-400 transition-colors"
                        >
                            {{ form.logo_url ? 'Change logo' : 'Upload logo' }}
                        </label>
                        <p class="mt-2 text-xs text-gray-500">
                            PNG, JPG up to 2MB
                        </p>
                        <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">
                            {{ form.errors.logo }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex justify-end gap-4">
                <button
                    type="button"
                    class="btn-secondary"
                    @click="$emit('cancel')"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ props.tenant ? 'Update School' : 'Create School' }}
                </button>
            </div>
        </div>
    </form>

    <Transition name="slide-fade">
        <div v-if="showNotification" 
             :class="['fixed bottom-4 right-4 p-4 rounded-lg shadow-lg text-white',
                     notificationType === 'success' ? 'bg-green-500' : 'bg-red-500']">
            <div class="flex items-center space-x-2">
                <svg v-if="notificationType === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span>{{ notificationMessage }}</span>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.btn-primary {
    @apply inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50;
}

.btn-secondary {
    @apply inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors;
}

.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(20px);
    opacity: 0;
}
</style>