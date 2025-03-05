<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SchoolList from '@/components/landlord/schools/SchoolList.vue';
import { type BreadcrumbItem } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    schools?: {
        data: Array<any>;
        links: Array<any>;
    };
    filters?: {
        search?: string;
        status?: string;
    };
    trashedCount: number;
}

// Add default values for props
const props = withDefaults(defineProps<Props>(), {
    schools: () => ({
        data: [],
        links: []
    }),
    filters: () => ({})
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard'
    },
    {
        title: 'Schools',
        href: route('admin.tenants.index')
    }
];
</script>

<template>
    <Head title="Schools" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Schools</h2>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Trash Link -->
                    <Link
                        :href="route('admin.tenants.trash')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Trash
                        <span v-if="trashedCount > 0" class="ml-1.5 py-0.5 px-2 text-xs font-medium bg-gray-100 rounded-full">
                            {{ trashedCount }}
                        </span>
                    </Link>

                    <!-- Import Schools Button -->
                    <Link
                        href="#"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Import
                    </Link>

                    <!-- Create School Button -->
                    <Link
                        :href="route('admin.tenants.create')"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New School
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Add v-if to prevent rendering until data is available -->
                <SchoolList 
                    v-if="props.schools"
                    :schools="props.schools"
                    :filters="props.filters" 
                />
            </div>
        </div>
    </AppLayout>
</template>