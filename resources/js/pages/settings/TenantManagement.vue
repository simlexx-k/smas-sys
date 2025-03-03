<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

// Add props definition
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
const isLoading = ref(false);
const showEditModal = ref(false);
const isSubmitting = ref(false);

const toast = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'School Settings',
        href: '/settings/tenant-management',
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
        // Update the local tenant data with the response
        tenant.value = response.data;
    } catch (error) {
        console.error('Error updating school details:', error);
        toast.error('Failed to update school details');
    } finally {
        isSubmitting.value = false;
    }
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="School Settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall 
                    title="School Settings" 
                    description="Manage your school information and configurations" 
                />

                <!-- Debug output -->
                <pre class="bg-gray-100 p-4 rounded">{{ tenant }}</pre>

                <!-- School Details -->
                <div v-if="tenant" class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">School Information</h2>
                            <p class="mt-1 text-sm text-gray-500">View and update your school's details</p>
                        </div>
                        <button 
                            @click="editSchoolDetails"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700"
                        >
                            Edit Details
                        </button>
                    </div>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                        <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500">School Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ tenant.name }}</dd>
                        </div>

                        <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ tenant.email || 'Not set' }}</dd>
                        </div>

                        <div class="col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ tenant.phone || 'Not set' }}</dd>
                        </div>

                        <div class="col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ tenant.address || 'Not set' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Edit Modal -->
                <div v-if="showEditModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
                        <h2 class="text-lg font-medium mb-4">Edit School Details</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">School Name</label>
                                <input v-model="tenant.name" type="text" class="form-input w-full rounded-md" />
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input v-model="tenant.email" type="email" class="form-input w-full rounded-md" />
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input v-model="tenant.phone" type="text" class="form-input w-full rounded-md" />
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea v-model="tenant.address" rows="3" class="form-textarea w-full rounded-md"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                @click="showEditModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="saveSchoolDetails"
                                :disabled="isSubmitting"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
