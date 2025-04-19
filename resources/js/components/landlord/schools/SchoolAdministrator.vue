<script setup lang="ts">
interface Props {
    tenant: {
        admin?: {
            id: number;
            name: string;
            email: string;
            email_verified_at: string | null;
            created_at: string;
            updated_at: string;
        };
        logo_url?: string;
        name: string;
        domain: string;
    };
}

const props = defineProps<Props>();

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getInitials = (name: string) => {
    const names = name.split(' ');
    return names.map(n => n.charAt(0).toUpperCase()).join('');
};
</script>

<template>
    <div class="space-y-6">
        <!-- Admin Profile -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="sm:flex sm:items-start sm:justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 h-12 w-12">
                            <img 
                                v-if="tenant.logo_url"
                                :src="tenant.logo_url"
                                class="h-12 w-12 rounded-full"
                                :alt="tenant.name"
                            >
                            <span 
                                v-else
                                class="inline-block h-12 w-12 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center"
                            >
                                {{ getInitials(tenant.name) }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ tenant.name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ tenant.domain }}</p>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex sm:flex-shrink-0 sm:items-center space-x-3">
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            @click="$emit('resetPassword')"
                        >
                            Reset Password
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                            @click="$emit('impersonate')"
                        >
                            Login as Admin
                        </button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ tenant.admin?.name }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ tenant.admin?.email }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span :class="[
                                tenant.admin?.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800',
                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium'
                            ]">
                                {{ tenant.admin?.email_verified_at ? 'Verified' : 'Unverified' }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tenant.admin?.created_at) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Recent Activity</h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>Recent actions performed by the administrator.</p>
                </div>
                <div class="mt-4 flow-root">
                    <ul role="list" class="-mb-8">
                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-500">Last login at <span class="font-medium text-gray-900">{{ formatDate(tenant.admin?.updated_at) }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Permissions</h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>Administrator access and permissions.</p>
                </div>
                <div class="mt-4">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input
                                    id="full_access"
                                    type="checkbox"
                                    checked
                                    disabled
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="full_access" class="font-medium text-gray-700">Full Access</label>
                                <p class="text-gray-500">Can manage all aspects of the school account</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template> 