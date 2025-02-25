<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div v-if="$page.props.errors.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
              {{ $page.props.errors.error }}
            </div>

            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-800">Tenants</h2>
              <Link
                href="/tenants/create"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
              >
                Create New Tenant
              </Link>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hashed ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="tenant in tenants" :key="tenant.id">
                    {{ console.log('Rendering tenant:', tenant) }}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tenant.domain }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ tenant.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tenant.admin ? tenant.admin.name : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tenant.hashed_id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link @click="openViewModal(tenant)" class="text-blue-600 hover:text-blue-900 mr-4">View</Link>
                      <Link :href="`/tenants/${tenant.hashed_id}/edit`" class="text-blue-600 hover:text-blue-900 mr-4">Edit</Link>
                      <button @click="deleteTenant(tenant.hashed_id)" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <TenantViewModal :tenant="selectedTenant" :isOpen="viewModalOpen" @close="closeViewModal" />
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import Pagination from '@/components/Pagination.vue';
import TenantViewModal from '@/Components/TenantViewModal.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
  tenants: Object,
});

onMounted(() => {
  console.log('Tenants:', props.tenants);
  console.log('Tenants structure:', JSON.stringify(props.tenants, null, 2));
});

const viewModalOpen = ref(false);
const selectedTenant = ref(null);

const openViewModal = (tenant) => {
  selectedTenant.value = tenant;
  viewModalOpen.value = true;
};

const closeViewModal = () => {
  viewModalOpen.value = false;
};

const deleteTenant = (hashedId) => {
  if (confirm('Are you sure you want to delete this tenant?')) {
    Inertia.delete(`/tenants/${hashedId}`);
  }
};
</script>
