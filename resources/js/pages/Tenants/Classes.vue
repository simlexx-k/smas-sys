<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Classes" />
    <div class="p-6">
      <!-- Success Message -->
      <div v-if="showSuccessMessage" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg transition-opacity duration-300">
        {{ successMessage }}
      </div>

      <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <h1 class="text-2xl font-bold">Classes</h1>
        <div class="flex items-center gap-4 w-full md:w-auto">
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search classes..."
            class="px-4 py-2 border rounded-md flex-grow md:flex-grow-0"
          />
          <button @click="openCreateModal" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 whitespace-nowrap">
            Create Class
          </button>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
        {{ error }}
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center p-8">
        <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <!-- Class List -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th 
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                  @click="toggleSort('name')"
                >
                  <div class="flex items-center">
                    <span>Name</span>
                    <span v-if="sortBy === 'name'" class="ml-2">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="classItem in sortedClasses" :key="classItem.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ classItem.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button @click="openEditModal(classItem)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                  <button @click="openDeleteModal(classItem)" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && classes.length === 0" class="p-6 text-center text-gray-500">
          No classes found. <button @click="openCreateModal" class="text-blue-500 hover:text-blue-700">Create one?</button>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <transition name="modal-fade">
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
          <div class="bg-white rounded-lg shadow-lg w-full max-w-[95%] md:max-w-md mx-4 transform transition-all">
            <div class="p-6">
              <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Class' : 'Create Class' }}</h2>
              <form @submit.prevent="saveClass">
                <div class="space-y-4">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Class Name</label>
                    <input
                      v-model="form.name"
                      type="text"
                      id="name"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': errors.name }"
                      required
                    />
                    <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</div>
                  </div>
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                  <button @click="showModal = false" type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                  </button>
                  <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    {{ isEditing ? 'Update' : 'Create' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </transition>

      <!-- Delete Modal -->
      <transition name="modal-fade">
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
          <div class="bg-white rounded-lg shadow-lg w-full max-w-[95%] md:max-w-md mx-4 transform transition-all">
            <div class="p-6">
              <div class="flex items-center mb-4">
                <svg class="h-6 w-6 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-xl font-bold">Delete Class</h2>
              </div>
              <p class="text-gray-700 mb-6">Are you sure you want to delete "{{ selectedClass?.name }}"? This action cannot be undone.</p>
              <div class="flex justify-end space-x-4">
                <button @click="showDeleteModal = false" type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                  Cancel
                </button>
                <button @click="confirmDelete" type="button" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
});

interface Class {
  id: number;
  name: string;
}

const breadcrumbs = [
  { title: 'Classes', href: '/classes' },
];

const classes = ref<Class[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const showModal = ref(false);
const isEditing = ref(false);
const showDeleteModal = ref(false);
const selectedClass = ref<Class | null>(null);
const searchQuery = ref('');
const sortBy = ref('name');
const sortDirection = ref('asc');
const showSuccessMessage = ref(false);
const successMessage = ref('');
const errors = ref<Record<string, string[]>>({});

const user = ref(usePage().props.auth.user);

const form = ref({
  id: null,
  name: '',
  tenant_id: null
});

const filteredClasses = computed(() => {
  if (!searchQuery.value) return classes.value;
  return classes.value.filter(c => 
    c.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const sortedClasses = computed(() => {
  return [...filteredClasses.value].sort((a, b) => {
    const modifier = sortDirection.value === 'asc' ? 1 : -1;
    return a[sortBy.value].localeCompare(b[sortBy.value]) * modifier;
  });
});

const toggleSort = (column: string) => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = 'asc';
  }
};

const showSuccess = (message: string) => {
  successMessage.value = message;
  showSuccessMessage.value = true;
  setTimeout(() => {
    showSuccessMessage.value = false;
  }, 3000);
};

const openCreateModal = () => {
  errors.value = {};
  form.value = { id: null, name: '', tenant_id: null };
  isEditing.value = false;
  showModal.value = true;
};

const openEditModal = (classItem: Class) => {
  errors.value = {};
  form.value = { ...classItem, tenant_id: user.value.tenant_id };
  isEditing.value = true;
  showModal.value = true;
};

const openDeleteModal = (classItem: Class) => {
  selectedClass.value = classItem;
  showDeleteModal.value = true;
};

const saveClass = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/classes/${form.value.id}`, form.value);
    } else {
      await api.post('/classes', form.value);
    }
    showSuccess(isEditing.value ? 'Class updated successfully' : 'Class created successfully');
    showModal.value = false;
    await fetchClasses();
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors;
    } else {
      error.value = 'An error occurred. Please try again.';
    }
  }
};

const confirmDelete = async () => {
  if (!selectedClass.value) return;
  
  try {
    await api.delete(`/classes/${selectedClass.value.id}`);
    showSuccess('Class deleted successfully');
    showDeleteModal.value = false;
    await fetchClasses();
  } catch (err) {
    error.value = 'Failed to delete class. Please try again.';
  }
};

const fetchClasses = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await api.get('/classes');
    classes.value = response.data.data.map((item: any) => ({
      id: item.id,
      name: item.name,
    }));
  } catch (err) {
    error.value = 'Failed to fetch classes. Please try again.';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchClasses();
  if (!user.value.tenant_id) {
    try {
      const response = await axios.get('/api/tenant');
      user.value.tenant_id = response.data.tenant_id;
    } catch (err) {
      console.error('Error fetching tenant ID:', err);
    }
  }
  form.value.tenant_id = user.value.tenant_id;
});
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>