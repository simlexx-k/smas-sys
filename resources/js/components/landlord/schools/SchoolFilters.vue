<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

interface Props {
    filters?: {
        search?: string;
        status?: string;
        subscription?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    filters: () => ({
        search: '',
        status: '',
        subscription: ''
    })
});

const search = ref(props.filters.search);
const status = ref(props.filters.status);
const subscription = ref(props.filters.subscription);

// Debounced search
watch(search, debounce((value: string) => {
    updateFilters();
}, 300));

// Immediate filter updates
watch([status, subscription], () => {
    updateFilters();
});

const updateFilters = () => {
    router.get(route('admin.tenants.index'), {
        search: search.value,
        status: status.value,
        subscription: subscription.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input
                    type="text"
                    v-model="search"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Search schools..."
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select
                    v-model="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Subscription</label>
                <select
                    v-model="subscription"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">All Subscriptions</option>
                    <option value="active">Active</option>
                    <option value="trial">Trial</option>
                    <option value="expired">Expired</option>
                    <option value="none">No Subscription</option>
                </select>
            </div>
        </div>
    </div>
</template> 