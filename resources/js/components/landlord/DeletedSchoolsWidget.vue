<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';

interface DeletedSchool {
    id: number;
    name: string;
    deleted_at: string;
}

interface Props {
    recentlyDeleted: DeletedSchool[];
    totalDeleted: number;
}

const props = withDefaults(defineProps<Props>(), {
    recentlyDeleted: () => [],
    totalDeleted: 0
});

console.log('DeletedSchoolsWidget props:', props);
</script>

<template>
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Recently Deleted Schools
                </h3>
                <Link
                    v-if="props.totalDeleted > 0"
                    :href="route('admin.tenants.trash')"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                >
                    View all
                </Link>
            </div>
            <div class="mt-5">
                <div v-if="props.recentlyDeleted?.length > 0" class="flow-root">
                    <ul role="list" class="-my-4 divide-y divide-gray-200">
                        <li v-for="school in props.recentlyDeleted" 
                            :key="school.id" 
                            class="py-4"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ school.name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Deleted {{ formatDistanceToNow(new Date(school.deleted_at), { addSuffix: true }) }}
                                    </p>
                                </div>
                                <div>
                                    <Link
                                        :href="route('admin.tenants.trash')"
                                        class="inline-flex items-center shadow-sm px-2.5 py-0.5 border border-gray-300 text-sm leading-5 font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        View
                                    </Link>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div v-else class="text-sm text-gray-500 text-center py-4">
                    No recently deleted schools
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <Link
                    :href="route('admin.tenants.trash')"
                    class="font-medium text-indigo-600 hover:text-indigo-500"
                >
                    View trash bin
                    <span v-if="props.totalDeleted > 0">&middot; {{ props.totalDeleted }} total</span>
                </Link>
            </div>
        </div>
    </div>
</template> 