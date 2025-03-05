<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

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
    subscriptionPlans: Record<string, string>;
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

const form = useForm({
    // Basic tenant information
    name: props.tenant?.name ?? '',
    domain: props.tenant?.domain ?? '',
    email: props.tenant?.email ?? '',
    phone: props.tenant?.phone ?? '',
    address: props.tenant?.address ?? '',
    logo: null as File | null,
    status: props.tenant?.status ?? 'active',
    school_type: props.tenant?.school_type ?? '',
    subscription_plan: props.tenant?.subscription_plan ?? 'basic',
    
    // Admin information
    admin_name: props.tenant?.admin?.name ?? '',
    admin_email: props.tenant?.admin?.email ?? '',
    admin_password: '', // Only required for new tenants
    
    // Subscription information
    subscription_starts_at: props.tenant?.subscription?.starts_at ?? new Date().toISOString().slice(0, 16),
    trial_ends_at: props.tenant?.subscription?.trial_ends_at ?? new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().slice(0, 16),
});

const submit = () => {
    console.log('Form submission data:', {
        formData: form,
        availablePlans: props.subscriptionPlans
    });

    console.log('Form submission started', {
        formData: form.data(),
        processing: form.processing,
        errors: form.errors
    });

    if (props.tenant) {
        form.put(route('admin.tenants.update', props.tenant.id), {
            onSuccess: () => console.log('Update successful'),
            onError: (errors) => console.error('Update failed:', errors),
        });
    } else {
        console.log('Attempting to create new tenant...');
        form.post(route('admin.tenants.store'), {
            onSuccess: () => console.log('Creation successful'),
            onError: (errors) => console.error('Creation failed:', errors),
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Basic Information Section -->
                <div class="sm:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                </div>

                <!-- School Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">School Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- Domain -->
                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700">Domain</label>
                    <input
                        id="domain"
                        v-model="form.domain"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.domain" class="mt-1 text-sm text-red-600">{{ form.errors.domain }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>

                <!-- Address -->
                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                </div>

                <!-- Admin Information Section -->
                <div class="sm:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900">Administrator Information</h3>
                </div>

                <!-- Admin Name -->
                <div>
                    <label for="admin_name" class="block text-sm font-medium text-gray-700">Admin Name</label>
                    <input
                        id="admin_name"
                        v-model="form.admin_name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.admin_name" class="mt-1 text-sm text-red-600">{{ form.errors.admin_name }}</p>
                </div>

                <!-- Admin Email -->
                <div>
                    <label for="admin_email" class="block text-sm font-medium text-gray-700">Admin Email</label>
                    <input
                        id="admin_email"
                        v-model="form.admin_email"
                        type="email"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.admin_email" class="mt-1 text-sm text-red-600">{{ form.errors.admin_email }}</p>
                </div>

                <!-- Admin Password (only for new tenants) -->
                <div v-if="!props.tenant">
                    <label for="admin_password" class="block text-sm font-medium text-gray-700">Admin Password</label>
                    <input
                        id="admin_password"
                        v-model="form.admin_password"
                        type="password"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.admin_password" class="mt-1 text-sm text-red-600">{{ form.errors.admin_password }}</p>
                </div>

                <!-- Subscription Information Section -->
                <div class="sm:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900">Subscription Information</h3>
                </div>

                <!-- School Type -->
                <div>
                    <label for="school_type" class="block text-sm font-medium text-gray-700">School Type</label>
                    <select
                        id="school_type"
                        v-model="form.school_type"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">Select a type</option>
                        <option 
                            v-for="(label, value) in schoolTypes" 
                            :key="value" 
                            :value="value"
                        >
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="form.errors.school_type" class="mt-1 text-sm text-red-600">{{ form.errors.school_type }}</p>
                </div>

                <!-- Subscription Plan -->
                <div>
                    <label for="subscription_plan" class="block text-sm font-medium text-gray-700">Subscription Plan</label>
                    <select
                        id="subscription_plan"
                        v-model="form.subscription_plan"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">Select a plan</option>
                        <option
                            v-for="(name, slug) in subscriptionPlans"
                            :key="slug"
                            :value="slug"
                        >
                            {{ name }}
                        </option>
                    </select>
                    <p v-if="form.errors.subscription_plan" class="mt-1 text-sm text-red-600">{{ form.errors.subscription_plan }}</p>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select
                        id="status"
                        v-model="form.status"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option 
                            v-for="(label, value) in statuses" 
                            :key="value" 
                            :value="value"
                        >
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                </div>

                <!-- Subscription Start Date -->
                <div>
                    <label for="subscription_starts_at" class="block text-sm font-medium text-gray-700">Subscription Start Date</label>
                    <input
                        id="subscription_starts_at"
                        v-model="form.subscription_starts_at"
                        type="datetime-local"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.subscription_starts_at" class="mt-1 text-sm text-red-600">{{ form.errors.subscription_starts_at }}</p>
                </div>

                <!-- Trial End Date -->
                <div>
                    <label for="trial_ends_at" class="block text-sm font-medium text-gray-700">Trial End Date</label>
                    <input
                        id="trial_ends_at"
                        v-model="form.trial_ends_at"
                        type="datetime-local"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                    <p v-if="form.errors.trial_ends_at" class="mt-1 text-sm text-red-600">{{ form.errors.trial_ends_at }}</p>
                </div>

                <!-- Logo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Logo</label>
                    <div class="mt-1 flex items-center">
                        <span class="inline-block h-12 w-12 overflow-hidden rounded-full bg-gray-100">
                            <img
                                v-if="tenant?.logo_url"
                                :src="tenant.logo_url"
                                :alt="tenant.name"
                                class="h-full w-full object-cover"
                            >
                            <svg
                                v-else
                                class="h-full w-full text-gray-300"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </span>
                        <input
                            type="file"
                            accept="image/*"
                            class="ml-5 rounded-md border border-gray-300 bg-white py-2 px-3 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            @input="form.logo = $event.target.files?.[0]"
                        >
                    </div>
                    <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</p>
                </div>
            </div>
        </div>

        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
            <button
                type="submit"
                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                :disabled="form.processing"
            >
                {{ props.tenant ? 'Update School' : 'Create School' }}
            </button>
        </div>
    </form>
</template>