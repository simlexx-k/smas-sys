<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SchoolForm from '@/components/landlord/schools/SchoolForm.vue';
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
        school_type: string;
        subscription_plan: string;
    };
    schoolTypes: Record<string, string>;
    subscriptionPlans: Record<string, string>;
    statuses: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schools',
        href: route('admin.tenants.index')
    },
    {
        title: props.tenant.name,
        href: route('admin.tenants.show', props.tenant.id)
    },
    {
        title: 'Edit',
        href: route('admin.tenants.edit', props.tenant.id)
    }
];
</script>

<template>
    <Head :title="`Edit ${tenant.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Edit School</h2>
                    <p class="mt-1 text-sm text-gray-600">Update school information</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.tenants.show', tenant.id)"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <SchoolForm 
                    :tenant="tenant"
                    :school-types="schoolTypes"
                    :subscription-plans="subscriptionPlans"
                    :statuses="statuses"
                />
            </div>
        </div>
    </AppLayout>
</template>