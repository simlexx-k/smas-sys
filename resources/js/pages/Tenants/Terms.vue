<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const toast = useToast();
const showModal = ref(false);
const loading = ref(false);
const isEditing = ref(false);
const terms = ref([]);

const form = ref({
  id: null,
  tenant_id: page.props.auth?.user?.tenant?.id,
  name: '',
  start_date: '',
  end_date: '',
  academic_year: '',
  status: 'active'
});

const fetchTerms = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/terms');
    terms.value = response.data;
  } catch (error: any) {
    toast.error(error.response?.data?.error || 'Failed to fetch terms');
  } finally {
    loading.value = false;
  }
};

const saveTerm = async () => {
  try {
    loading.value = true;
    form.value.tenant_id = page.props.auth?.user?.tenant?.id;
    
    if (isEditing.value) {
      await axios.put(`/api/terms/${form.value.id}`, form.value);
      toast.success('Term updated successfully');
    } else {
      await axios.post('/api/terms', form.value);
      toast.success('Term created successfully');
    }
    await fetchTerms();
    showModal.value = false;
    resetForm();
  } catch (error: any) {
    toast.error(error.response?.data?.error || 'Failed to save term');
  } finally {
    loading.value = false;
  }
};

const editTerm = (term: any) => {
  isEditing.value = true;
  form.value = { ...term };
  showModal.value = true;
};

const deleteTerm = async (term: any) => {
  if (!confirm('Are you sure you want to delete this term?')) return;
  
  try {
    loading.value = true;
    await axios.delete(`/api/terms/${term.id}`);
    toast.success('Term deleted successfully');
    await fetchTerms();
  } catch (error: any) {
    toast.error(error.response?.data?.error || 'Failed to delete term');
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  form.value = {
    id: null,
    tenant_id: page.props.auth?.user?.tenant?.id,
    name: '',
    start_date: '',
    end_date: '',
    academic_year: '',
    status: 'active'
  };
  isEditing.value = false;
};

onMounted(() => {
  fetchTerms();
});
</script>

<template>
  <AppLayout>
    <Head title="Academic Terms" />
    
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-900">Academic Terms</h2>
              <button
                @click="showModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                Add New Term
              </button>
            </div>

            <!-- Terms List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Academic Year
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Duration
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="term in terms" :key="term.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ term.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ term.academic_year }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-500">
                        {{ new Date(term.start_date).toLocaleDateString() }} - 
                        {{ new Date(term.end_date).toLocaleDateString() }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full"
                        :class="{
                          'bg-green-100 text-green-800': term.status === 'active',
                          'bg-yellow-100 text-yellow-800': term.status === 'inactive',
                          'bg-blue-100 text-blue-800': term.status === 'completed'
                        }">
                        {{ term.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button
                        @click="editTerm(term)"
                        class="text-indigo-600 hover:text-indigo-900 mr-4"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteTerm(term)"
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
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="saveTerm" class="p-6">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input
                v-model="form.name"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
              >
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Academic Year</label>
              <input
                v-model="form.academic_year"
                type="text"
                placeholder="e.g., 2024/2025"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
              >
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Start Date</label>
              <input
                v-model="form.start_date"
                type="date"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
              >
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">End Date</label>
              <input
                v-model="form.end_date"
                type="date"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
              >
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select
                v-model="form.status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button
                type="button"
                @click="showModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {{ loading ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 