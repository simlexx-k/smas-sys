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
        school_type: string;
        subscription_plan: string;
    };
    schoolTypes: Record<string, string>;
    subscriptionPlans: Record<string, string>;
    statuses: Record<string, string>;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.tenant?.name ?? '',
    domain: props.tenant?.domain ?? '',
    email: props.tenant?.email ?? '',
    phone: props.tenant?.phone ?? '',
    address: props.tenant?.address ?? '',
    logo: null as File | null,
    status: props.tenant?.status ?? 'active',
    school_type: props.tenant?.school_type ?? '',
    subscription_plan: props.tenant?.subscription_plan ?? 'basic'
});

const submit = () => {
    if (props.tenant) {
        form.put(route('admin.tenants.update', props.tenant.id));
    } else {
        form.post(route('admin.tenants.store'));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- School Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">School Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
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

                <!-- School Type -->
                <div>
                    <label for="school_type" class="block text-sm font-medium text-gray-700">School Type</label>
                    <select
                        id="school_type"
                        v-model="form.school_type"
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
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option 
                            v-for="(label, value) in subscriptionPlans" 
                            :key="value" 
                            :value="value"
                        >
                            {{ label }}
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
                Save Changes
            </button>
        </div>
    </form>
</template>