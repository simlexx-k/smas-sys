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
        router.delete(route('admin.tenants.destroy', props.tenant.id));
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
            <div class="border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4">
                    <div>
                        <div class="flex items-center">
                            <img :src="tenant.logo_url" :alt="tenant.name" class="h-12 w-12 rounded-full mr-4">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">{{ tenant.name }}</h2>
                                <p class="mt-1 text-sm text-gray-600">{{ tenant.domain }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Quick Actions Dropdown -->
                        <div class="relative" @click.away="open = false">
                            <button
                                type="button"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                @click="open = !open"
                            >
                                <span>Actions</span>
                                <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <!-- Dropdown menu -->
                            <div v-if="open" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <Link
                                        :href="route('admin.tenants.edit', tenant.id)"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        Edit School
                                    </Link>
                                    <button
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        @click="impersonateAdmin"
                                    >
                                        Login as Admin
                                    </button>
                                    <button
                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                        @click="confirmDelete"
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