<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

interface Domain {
    id: number;
    domain: string;
    is_primary: boolean;
    is_verified: boolean;
    verification_method: 'dns' | 'file';
    verification_token: string | null;
    verified_at: string | null;
}

interface Props {
    schoolId: number;
    domains: Domain[];
    currentDomain: string;
}

const props = defineProps<Props>();

const form = useForm({
    domain: '',
    is_primary: false,
    verification_method: 'dns' as 'dns' | 'file'
});

const showAddDomainModal = ref(false);
const verificationInstructions = ref<string | null>(null);

const addDomain = () => {
    form.post(route('admin.tenants.domains.store', { id: props.schoolId }), {
        onSuccess: () => {
            showAddDomainModal.value = false;
            form.reset();
        }
    });
};

const verifyDomain = (domain: Domain) => {
    if (domain.verification_method === 'dns') {
        verificationInstructions.value = `
            Add the following TXT record to your DNS settings:
            Name: _school-verify.${domain.domain}
            Value: ${domain.verification_token}
        `;
    } else {
        verificationInstructions.value = `
            Create a file at: ${domain.domain}/.well-known/school-verification.txt
            With content: ${domain.verification_token}
        `;
    }
};

const setPrimaryDomain = (domain: Domain) => {
    useForm().put(route('admin.tenants.domains.primary', { 
        id: props.schoolId,
        domain_id: domain.id 
    }));
};

const deleteDomain = (domain: Domain) => {
    if (confirm('Are you sure you want to delete this domain?')) {
        useForm().delete(route('admin.tenants.domains.destroy', { 
            id: props.schoolId,
            domain_id: domain.id 
        }));
    }
};

const formatDate = (date: string | null): string => {
    if (!date) return 'Not verified';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Domain Settings</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage custom domains for this school
                    </p>
                </div>
                <button
                    @click="showAddDomainModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    Add Domain
                </button>
            </div>

            <div class="mt-6">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Domain</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Verified At</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="domain in domains" :key="domain.id">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                    {{ domain.domain }}
                                    <span v-if="domain.is_primary" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Primary
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span
                                        :class="[
                                            domain.is_verified
                                                ? 'text-green-800 bg-green-100'
                                                : 'text-yellow-800 bg-yellow-100',
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                                        ]"
                                    >
                                        {{ domain.is_verified ? 'Verified' : 'Unverified' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ formatDate(domain.verified_at) }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <button
                                        v-if="!domain.is_verified"
                                        @click="verifyDomain(domain)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-4"
                                    >
                                        Verify
                                    </button>
                                    <button
                                        v-if="!domain.is_primary && domain.is_verified"
                                        @click="setPrimaryDomain(domain)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-4"
                                    >
                                        Set as Primary
                                    </button>
                                    <button
                                        v-if="!domain.is_primary"
                                        @click="deleteDomain(domain)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Domain Modal -->
        <div v-if="showAddDomainModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full">
                <h3 class="text-lg font-medium text-gray-900">Add New Domain</h3>
                <form @submit.prevent="addDomain" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Domain Name</label>
                        <input
                            type="text"
                            v-model="form.domain"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="example.com"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Verification Method</label>
                        <select
                            v-model="form.verification_method"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="dns">DNS Record</option>
                            <option value="file">File Upload</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.is_primary"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label class="ml-2 block text-sm text-gray-900">
                            Set as primary domain
                        </label>
                    </div>

                    <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showAddDomainModal = false"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700"
                        >
                            Add Domain
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Verification Instructions Modal -->
        <div v-if="verificationInstructions" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full">
                <h3 class="text-lg font-medium text-gray-900">Domain Verification Instructions</h3>
                <div class="mt-4">
                    <pre class="bg-gray-50 p-4 rounded-md text-sm whitespace-pre-wrap">{{ verificationInstructions }}</pre>
                </div>
                <div class="mt-5 flex justify-end">
                    <button
                        @click="verificationInstructions = null"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template> 