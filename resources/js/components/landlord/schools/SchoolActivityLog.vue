<script setup lang="ts">
interface Activity {
    id: number;
    type: string;
    description: string;
    created_at: string;
    causer?: {
        name: string;
        email: string;
    };
}

interface Props {
    tenant: {
        id: number;
        name: string;
    };
    activities?: Activity[];
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getActivityIcon = (type: string) => {
    const icons = {
        login: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />`,
        update: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />`,
        create: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />`,
        delete: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />`,
        default: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`
    };
    return icons[type as keyof typeof icons] || icons.default;
};
</script>

<template>
    <div class="space-y-6">
        <!-- Activity Feed -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="sm:flex sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Activity Log</h3>
                        <div class="mt-2 max-w-xl text-sm text-gray-500">
                            <p>Recent activity and changes to the school account.</p>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex sm:flex-shrink-0 sm:items-center">
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Export Log
                        </button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <li v-for="activity in activities" :key="activity.id">
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true" />
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-html="getActivityIcon(activity.type)" />
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                {{ activity.description }}
                                                <span v-if="activity.causer" class="font-medium text-gray-900">
                                                    by {{ activity.causer.name }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time :datetime="activity.created_at">{{ formatDate(activity.created_at) }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Activity Filters</h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>Filter activity log by type and date range.</p>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="activity-type" class="block text-sm font-medium text-gray-700">Activity Type</label>
                        <select
                            id="activity-type"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Activities</option>
                            <option value="login">Login</option>
                            <option value="update">Updates</option>
                            <option value="create">Creation</option>
                            <option value="delete">Deletion</option>
                        </select>
                    </div>
                    <div>
                        <label for="date-from" class="block text-sm font-medium text-gray-700">From</label>
                        <input
                            type="date"
                            id="date-from"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                    <div>
                        <label for="date-to" class="block text-sm font-medium text-gray-700">To</label>
                        <input
                            type="date"
                            id="date-to"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>