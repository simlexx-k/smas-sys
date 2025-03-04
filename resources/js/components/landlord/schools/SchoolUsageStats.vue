<script setup lang="ts">
interface UsageStats {
    storage: {
        used: number;
        total: number;
        percentage: number;
    };
    users: {
        active: number;
        total: number;
        percentage: number;
    };
    students: {
        active: number;
        total: number;
        percentage: number;
    };
    last_activity: string | null;
}

interface Props {
    stats: UsageStats;
}

defineProps<Props>();

const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (date: string | null): string => {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
};
</script>

<template>
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900">Usage Statistics</h3>
            
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <!-- Storage Usage -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <h4 class="text-sm font-medium text-gray-500">Storage</h4>
                        <span class="text-sm text-gray-900">
                            {{ stats.storage.percentage }}%
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div
                                class="bg-indigo-600 rounded-full h-2"
                                :style="{ width: `${stats.storage.percentage}%` }"
                            ></div>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ formatBytes(stats.storage.used) }} of {{ formatBytes(stats.storage.total) }}
                    </div>
                </div>

                <!-- Users -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <h4 class="text-sm font-medium text-gray-500">Active Users</h4>
                        <span class="text-sm text-gray-900">
                            {{ stats.users.percentage }}%
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div
                                class="bg-green-600 rounded-full h-2"
                                :style="{ width: `${stats.users.percentage}%` }"
                            ></div>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ stats.users.active }} of {{ stats.users.total }} users
                    </div>
                </div>

                <!-- Students -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <h4 class="text-sm font-medium text-gray-500">Active Students</h4>
                        <span class="text-sm text-gray-900">
                            {{ stats.students.percentage }}%
                        </span>
                    </div>
                    <div class="mt-2">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div
                                class="bg-blue-600 rounded-full h-2"
                                :style="{ width: `${stats.students.percentage}%` }"
                            ></div>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        {{ stats.students.active }} of {{ stats.students.total }} students
                    </div>
                </div>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="text-sm text-gray-500">
                    Last activity: {{ formatDate(stats.last_activity) }}
                </div>
            </div>
        </div>
    </div>
</template> 