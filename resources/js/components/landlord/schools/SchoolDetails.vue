<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface School {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    logo_url: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    domain: string;
    users_count: number;
    students_count: number;
}

interface Props {
    school: School;
}

defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center space-x-5">
                <div class="flex-shrink-0">
                    <img
                        v-if="school.logo_url"
                        :src="school.logo_url"
                        :alt="school.name"
                        class="h-20 w-20 rounded-full"
                    >
                    <div v-else class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-600">
                            {{ school.name.charAt(0) }}
                        </span>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ school.name }}
                        <span
                            :class="[
                                school.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                                'ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                            ]"
                        >
                            {{ school.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </h3>
                    <div class="mt-1 text-sm text-gray-500">
                        Added {{ formatDate(school.created_at) }}
                    </div>
                </div>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Domain</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ school.domain }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ school.email || 'Not set' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ school.phone || 'Not set' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ school.address || 'Not set' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Users</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ school.users_count }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Students</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ school.students_count }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <Link
                    :href="route('admin.tenants.edit', { id: school.id })"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                    Edit School
                </Link>
                <Link
                    :href="`https://${school.domain}`"
                    target="_blank"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    Visit School
                </Link>
            </div>
        </div>
    </div>
</template> 