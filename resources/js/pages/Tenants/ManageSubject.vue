<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

interface Subject {
  id?: number;
  tenant_id: number;
  class_id: number;
  name: string;
  description: string;
  code: string;
}

const page = usePage();
const tenantId = ref<number | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);
const subjectId = ref<number | null>(null);

const form = ref<Subject>({
  tenant_id: null,
  class_id: null,
  name: '',
  description: '',
  code: ''
});

const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const showSuccessToast = (message: string) => {
  toastMessage.value = message;
  toastType.value = 'success';
  showToast.value = true;
  setTimeout(() => showToast.value = false, 3000);
};

const showErrorToast = (message: string) => {
  toastMessage.value = message;
  toastType.value = 'error';
  showToast.value = true;
  setTimeout(() => showToast.value = false, 3000);
};

const validateForm = () => {
  if (!form.value.name) {
    showErrorToast('Subject name is required');
    return false;
  }
  if (!form.value.code) {
    showErrorToast('Subject code is required');
    return false;
  }
  if (!form.value.class_id) {
    showErrorToast('Class ID is required');
    return false;
  }
  return true;
};

const saveSubject = async () => {
  if (!validateForm()) return;

  try {
    loading.value = true;
    if (!form.value.tenant_id || !form.value.class_id) {
      throw new Error('Tenant ID and Class ID are required');
    }

    const payload = {
      ...form.value,
      tenant_id: form.value.tenant_id,
      class_id: form.value.class_id
    };

    if (subjectId.value) {
      await axios.put(`/api/subjects/${subjectId.value}`, payload);
      showSuccessToast('Subject updated successfully');
    } else {
      await axios.post('/api/subjects', payload);
      showSuccessToast('Subject created successfully');
    }
  } catch (err) {
    console.error('Save operation failed:', err);
    showErrorToast('Failed to save subject: ' + err.message);
  } finally {
    loading.value = false;
  }
};

const getTenantId = () => {
  // Try to get tenant ID from page props first
  if (page.props.tenant?.id) {
    return page.props.tenant.id;
  }
  
  // Fallback to user session if available
  if (page.props.auth?.user?.tenant_id) {
    return page.props.auth.user.tenant_id;
  }
  
  throw new Error('Tenant ID could not be determined');
};

const classes = ref<Array<{id: number, name: string}>>([]);
const selectedClassId = ref<number | null>(null);

const fetchClasses = async () => {
  try {
    const response = await axios.get('/api/classes', {
      params: {
        tenant_id: tenantId.value
      }
    });
    classes.value = response.data.data;
  } catch (err) {
    console.error('Failed to fetch classes:', err);
    showErrorToast('Failed to load classes');
  }
};

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    window.location.href = '/subjects';
  }
};

onMounted(async () => {
  try {
    const id = getTenantId();
    tenantId.value = id;
    form.value.tenant_id = id;
    console.log('Tenant ID set:', id);
    
    const subject = page.props.subject;
    if (subject) {
      subjectId.value = subject.id;
      form.value = subject;
    }
  } catch (err) {
    console.error('Failed to set tenant ID:', err);
    showErrorToast('Tenant information is not available');
  }
  await fetchClasses();
  if (page.props.subject) {
    selectedClassId.value = page.props.subject.class_id;
  }
});
</script>

<template>
  <AppLayout>
    <Head :title="subjectId ? 'Edit Subject' : 'Create Subject'" />
    <div class="flex flex-col gap-6 p-6 bg-gray-50 min-h-screen">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-4">
          <button
            @click="goBack"
            class="flex items-center text-gray-600 hover:text-gray-900 transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            <span class="ml-2">Back</span>
          </button>
          <h1 class="text-2xl font-bold text-gray-900">
            {{ subjectId ? 'Edit Subject' : 'Create New Subject' }}
          </h1>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form @submit.prevent="saveSubject" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Subject Name</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                :class="{ 'border-red-500': error && !form.name }"
                required
              />
              <p v-if="error && !form.name" class="text-sm text-red-500 mt-1">Subject name is required</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Subject Code</label>
              <input
                v-model="form.code"
                type="text"
                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                :class="{ 'border-red-500': error && !form.code }"
                required
              />
              <p v-if="error && !form.code" class="text-sm text-red-500 mt-1">Subject code is required</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
            <select
              v-model="form.class_id"
              class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              :class="{ 'border-red-500': error && !form.class_id }"
              required
            >
              <option value="">Select a class</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.name }}
              </option>
            </select>
            <p v-if="error && !form.class_id" class="text-sm text-red-500 mt-1">Class is required</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="form.description"
              class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              rows="4"
            ></textarea>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 transition-all"
              :disabled="loading"
            >
              <span v-if="!loading">{{ subjectId ? 'Update' : 'Create' }} Subject</span>
              <span v-else class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
