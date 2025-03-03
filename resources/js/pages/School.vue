<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const props = defineProps<{
    tenant: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        logo_url: string | null;
        created_at: string;
    }
}>();

const tenant = ref(props.tenant);
const showEditModal = ref(false);
const isSubmitting = ref(false);

const toast = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'School Settings',
        href: '/settings/school',
    },
];

function editSchoolDetails() {
    showEditModal.value = true;
}

async function saveSchoolDetails() {
    if (!tenant.value) return;
    
    isSubmitting.value = true;
    try {
        const response = await axios.put('/settings/school', tenant.value);
        showEditModal.value = false;
        toast.success('School details updated successfully');
        tenant.value = response.data;
    } catch (error) {
        console.error('Error updating school details:', error);
        toast.error('Failed to update school details');
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="School Settings" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <HeadingSmall 
                            title="School Settings" 
                            description="Manage your school information and configurations" 
                        />

                        <!-- School Details Card -->
                        <div v-if="tenant" class="mt-6">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h2 class="text-lg font-medium text-gray-900">School Information</h2>
                                    <p class="mt-1 text-sm text-gray-500">View and update your school's details</p>
                                </div>
                                <button 
                                    @click="editSchoolDetails"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Edit Details
                                </button>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-200">
                                <dl class="divide-y divide-gray-200">
                                    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">School Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ tenant.name }}</dd>
                                    </div>
                                    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ tenant.email || 'Not set' }}</dd>
                                    </div>
                                    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ tenant.phone || 'Not set' }}</dd>
                                    </div>
                                    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ tenant.address || 'Not set' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div v-if="showEditModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full mx-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-lg font-medium">Edit School Details</h2>
                                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">Close</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">School Name</label>
                                        <input v-model="tenant.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input v-model="tenant.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input v-model="tenant.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>

                                    <div class="space-y-2 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Address</label>
                                        <textarea v-model="tenant.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end space-x-3">
                                    <button 
                                        @click="showEditModal = false"
                                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Cancel
                                    </button>
                                    <button 
                                        @click="saveSchoolDetails"
                                        :disabled="isSubmitting"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                    >
                                        {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 