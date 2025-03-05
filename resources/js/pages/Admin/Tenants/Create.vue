<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SchoolForm from '@/components/landlord/schools/SchoolForm.vue';

interface Props {
    tenant: any;
    schoolTypes: Record<string, string>;
    subscriptionPlans: Record<string, string>;
    statuses: Record<string, string>;
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            role: string;
        };
    };
    errors: Record<string, string>;
}

// Define props that match what the controller sends
const props = defineProps<Props>();

// Debug log to verify data is received by the page
console.log('Create Page Props:', props);

const breadcrumbs = [
    { title: 'Schools', href: route('admin.tenants.index') },
    { title: 'Create', href: route('admin.tenants.create') }
];
</script>

<template>
    <Head title="Create School" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold text-gray-900">
                Create New School
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <SchoolForm
                            :tenant="tenant"
                            :school-types="schoolTypes"
                            :subscription-plans="subscriptionPlans"
                            :statuses="statuses"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 