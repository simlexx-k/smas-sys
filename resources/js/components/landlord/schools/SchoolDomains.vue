<script setup lang="ts">
interface Domain {
    id: number;
    domain: string;
    is_primary: boolean;
    verified_at: string | null;
    created_at: string;
}

interface Props {
    tenant: {
        id: number;
        domain: string;
        domains?: Domain[];
        logo_url?: string;
        name: string;
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
</script>

<template>
    <div class="space-y-6">
        <!-- Domain List -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 h-9 w-9">
                        <img 
                            v-if="tenant.logo_url"
                            :src="tenant.logo_url"
                            class="h-9 w-9 rounded-full"
                            :alt="tenant.name"
                        >
                        <div 
                            v-else
                            class="h-9 w-9 rounded-full bg-gray-100 flex items-center justify-center"
                        >
                            <GlobeAltIcon class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-medium">Custom Domains</h3>
                        <p class="text-sm text-gray-500">for {{ tenant.name }}</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ tenant.domain }}
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Primary
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Verified
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(tenant.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="text-indigo-600 hover:text-indigo-900">Manage</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DNS Settings -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">DNS Settings</h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>Configure your DNS settings to point to our servers.</p>
                </div>
                <div class="mt-4">
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">CNAME Record</dt>
                                <dd class="mt-1 flex justify-between items-center">
                                    <code class="text-sm text-gray-900">{{ tenant.domain }}</code>
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Copy
                                    </button>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template> 