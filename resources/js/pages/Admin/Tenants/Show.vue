<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SchoolDetails from '@/components/landlord/schools/SchoolDetails.vue';
import SchoolSubscription from '@/components/landlord/schools/SchoolSubscription.vue';
import SchoolAdministrator from '@/components/landlord/schools/SchoolAdministrator.vue';
import SchoolDomains from '@/components/landlord/schools/SchoolDomains.vue';
import SchoolActivityLog from '@/components/landlord/schools/SchoolActivityLog.vue';
import { type BreadcrumbItem } from '@/types';
import { onClickOutside } from '@vueuse/core';
import { useToast } from 'vue-toastification';

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
        subscription?: {
            status: string;
            ends_at: string;
        };
        admin?: {
            name: string;
            email: string;
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
const currentTab = ref('overview');
const open = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

onClickOutside(dropdownRef, () => {
    open.value = false;
});

const toast = useToast();

const tabs = [
    { id: 'overview', name: 'Overview' },
    { id: 'subscription', name: 'Subscription' },
    { id: 'admin', name: 'Administrator' },
    { id: 'domains', name: 'Domains' },
    { id: 'activity', name: 'Activity Log' },
];

const impersonateAdmin = () => {
    if (confirm('Are you sure you want to login as this school administrator?')) {
        router.post(route('admin.tenants.impersonate', props.tenant.id));
    }
};

const resetAdminPassword = () => {
    if (confirm('Are you sure you want to reset the administrator password? A new password will be sent to their email.')) {
        router.post(route('admin.tenants.reset-password', props.tenant.id));
    }
};

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this school? This action cannot be undone.')) {
        router.delete(route('admin.tenants.destroy', props.tenant.id), {
            onSuccess: () => {
                toast.success('School has been successfully deleted');
            },
            onError: () => {
                toast.error('Failed to delete school. Please try again.');
            }
        });
    }
};

const changePlan = (plan: string) => {
    if (confirm(`Are you sure you want to change the subscription plan to ${plan}?`)) {
        router.post(route('admin.tenants.change-plan', props.tenant.id), {
            plan: plan
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schools',
        href: route('admin.tenants.index')
    },
    {
        title: props.tenant.name,
        href: route('admin.tenants.show', props.tenant.id)
    }
];
</script>

<template>
    <Head :title="tenant.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ tenant.name }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Quick Actions Dropdown -->
                    <div class="relative" ref="dropdownRef">
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            @click="open = !open"
                        >
                            Actions
                            <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-show="open" 
                            class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100">
                            <div class="py-1">
                                <Link
                                    :href="route('admin.tenants.edit', tenant.id)"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    Edit School
                                </Link>
                                <button
                                    @click="impersonateAdmin"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    Login as Admin
                                </button>
                                <button
                                    @click="resetAdminPassword"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                >
                                    Reset Admin Password
                                </button>
                            </div>
                            <div class="py-1">
                                <button
                                    @click="confirmDelete"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100"
                                >
                                    Delete School
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Primary Action -->
                    <Link
                        :href="route('admin.tenants.edit', tenant.id)"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit School
                    </Link>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mt-4">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        :class="[
                            currentTab === tab.id
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                        @click="currentTab = tab.id"
                    >
                        {{ tab.name }}
                    </button>
                </nav>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <SchoolDetails 
                    v-if="currentTab === 'overview'"
                    :tenant="tenant"
                    :stats="stats"
                />
                <SchoolSubscription
                    v-if="currentTab === 'subscription'"
                    :tenant="tenant"
                    @change-plan="changePlan"
                />
                <SchoolAdministrator
                    v-if="currentTab === 'admin'"
                    :tenant="tenant"
                    @reset-password="resetAdminPassword"
                    @impersonate="impersonateAdmin"
                />
                <SchoolDomains
                    v-if="currentTab === 'domains'"
                    :tenant="tenant"
                />
                <SchoolActivityLog
                    v-if="currentTab === 'activity'"
                    :tenant="tenant"
                    :activities="[]"
                />
            </div>
        </div>
    </AppLayout>
</template>