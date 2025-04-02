<template>
  <AppLayout>
    <Head title="Subjects" />
    <div class="p-6 bg-gray-50 min-h-screen">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Subject Management</h1>
          <p class="text-sm text-gray-500">Manage all subjects in your institution</p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
          <button
            @click="navigateToCreateSubject"
            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm w-full md:w-auto"
          >
            <PlusCircle class="w-5 h-5" />
            Add Subject
          </button>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="relative flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search subjects..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <Search class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow">
        <div class="animate-pulse">
          <div class="h-16 bg-gray-200 rounded-t-lg"></div>
          <div class="p-4 space-y-3">
            <div v-for="n in 5" :key="n" class="flex items-center space-x-4">
              <div class="h-4 bg-gray-200 rounded w-1/4"></div>
              <div class="h-4 bg-gray-200 rounded w-1/6"></div>
              <div class="h-4 bg-gray-200 rounded w-1/3"></div>
              <div class="h-4 bg-gray-200 rounded w-1/6"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subjects Table -->
      <div v-else class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="subject in paginatedSubjects" :key="subject?.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ subject?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ subject?.code }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ subject?.description }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="openEditModal(subject)" class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
                <button @click="confirmDelete(subject?.id)" class="text-red-600 hover:text-red-900">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="!isLoading && subjects.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No subjects</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new subject.</p>
        <div class="mt-6">
          <button
            @click="navigateToCreateSubject"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <PlusCircle class="w-5 h-5 mr-2" />
            Add Subject
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6">
        <Pagination
          :links="paginationLinks"
          @page-changed="currentPage = $event"
        />
      </div>

      <!-- Create/Edit Modal -->
      <Teleport to="body">
        <div v-if="showModal" v-show="showModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @click.self="closeModal" style="border: 2px solid red;">
          <div class="bg-white rounded-lg w-full max-w-md p-6" style="border: 2px solid blue;">
            <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Subject' : 'Create Subject' }}</h2>
            <form @submit.prevent="saveSubject">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Class</label>
                  <select
                    v-model="form.class_id"
                    class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
                    required
                  >
                    <option value="">Select a class</option>
                    <option v-for="classOption in classes" :key="classOption.id" :value="classOption.id">
                      {{ classOption.name }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Name</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Code</label>
                  <input
                    v-model="form.code"
                    type="text"
                    class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea
                    v-model="form.description"
                    class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2"
                    rows="3"
                  ></textarea>
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700">Score</label>
                  <input 
                    type="number" 
                    v-model="form.score"
                    min="0"
                    max="100"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    @input="validateScore"
                  >
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700">Grade (Auto-calculated)</label>
                  <input 
                    type="text" 
                    v-model="form.grade"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                    readonly
                  >
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700">Remarks (Auto-generated)</label>
                  <textarea 
                    v-model="form.remarks"
                    rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                    readonly
                  ></textarea>
                </div>
                <div class="mb-4">
                  <label class="inline-flex items-center">
                    <input 
                      type="checkbox" 
                      v-model="allowOverride"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <span class="ml-2 text-sm text-gray-600">Allow manual override of grade and remarks</span>
                  </label>
                </div>
                <template v-if="allowOverride">
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Custom Grade</label>
                    <input 
                      type="text" 
                      v-model="form.grade"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                  </div>
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Custom Remarks</label>
                    <textarea 
                      v-model="form.remarks"
                      rows="2"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                  </div>
                </template>
              </div>
              <div class="mt-6 flex justify-end gap-2">
                <button
                  type="button"
                  @click="closeModal"
                  class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                  {{ isEditing ? 'Update' : 'Create' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import { useToast } from "vue-toastification";

// Add these defaults to ensure cookies are sent with requests
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add this to get the CSRF token from the meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

interface Tenant {
  id: number;
  // add other tenant properties if needed
}

interface Subject {
  id: number;
  name: string;
  code: string;
  description: string;
  class_id?: number | null;
}

interface Class {
  id: number;
  name: string;
}

interface SubjectForm {
  id: number | null;
  class_id: number | null;
  name: string;
  code: string;
  description: string;
}

const tenant = usePage().props.tenant as Tenant;
const tenantId = tenant?.id || null;
console.log('Initial tenant:', tenant);
console.log('Initial tenantId:', tenantId);
if (!tenantId) {
  console.error('No tenant ID found');
}

const subjects = ref<Subject[]>([]);
const showModal = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');
const itemsPerPage = ref(10);
const currentPage = ref(1);
const classes = ref<Class[]>([]);

const form = ref<SubjectForm>({
  id: null,
  class_id: null,
  name: '',
  code: '',
  description: ''
});

const allowOverride = ref(false);

const toast = useToast();

// Add loading state
const isLoading = ref(true);

const navigateToCreateSubject = () => {
  router.visit(route('manage-subject'));
};

const navigateToEditSubject = (subjectId: number) => {
  router.visit(route('manage-subject', { id: subjectId }));
};

const filteredSubjects = computed(() => {
  return subjects.value.filter(subject => 
    subject.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    subject.code.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const paginatedSubjects = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredSubjects.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredSubjects.value.length / itemsPerPage.value);
});

const paginationLinks = computed(() => ({
  prev: currentPage.value > 1 ? currentPage.value - 1 : null,
  next: currentPage.value < totalPages.value ? currentPage.value + 1 : null
}));

const openCreateModal = () => {
  console.group('openCreateModal');
  console.log('Current tenantId:', tenantId);
  console.log('Current form:', form.value);
  console.log('Current showModal:', showModal.value);
  console.groupEnd();
  form.value = { 
    id: null, 
    class_id: null, 
    name: '', 
    code: '', 
    description: '' 
  };
  isEditing.value = false;
  showModal.value = true;
  console.log('Modal opened');
}

const openEditModal = (subject: Subject) => {
  console.log('Opening edit modal with subject:', subject);
  form.value = { 
    ...subject,
    class_id: subject.class_id || null 
  };
  isEditing.value = true;
  showModal.value = true;
};

const closeModal = () => {
  console.log('Closing modal with current state:', { showModal: showModal.value, isEditing: isEditing.value });
  showModal.value = false;
  isEditing.value = false;
  form.value = {
    id: null,
    class_id: null,
    name: '',
    code: '',
    description: ''
  };
  allowOverride.value = false;
  console.log('Modal state after closing:', { showModal: showModal.value, isEditing: isEditing.value });
};

const saveSubject = async () => {
  try {
    if (isEditing.value && form.value.id) {
      await axios.put(`/api/subjects/${form.value.id}`, form.value);
      toast.success("Subject updated successfully!");
    } else {
      await axios.post('/api/subjects', form.value);
      toast.success("Subject created successfully!");
    }
    await fetchSubjects();
    closeModal();
  } catch (error) {
    const errorMessage = error.response?.data?.message || "An error occurred while saving the subject";
    toast.error(errorMessage);
    console.error('Error saving subject:', error);
  }
};

const confirmDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this subject?')) {
    try {
      await axios.delete(`/api/subjects/${id}`);
      toast.success("Subject deleted successfully!");
      await fetchSubjects();
    } catch (error) {
      toast.error("Failed to delete subject");
      console.error('Error deleting subject:', error);
    }
  }
};

const fetchSubjects = async () => {
  try {
    isLoading.value = true;
    const response = await axios.get('/api/subjects');
    subjects.value = response.data;
  } catch (error) {
    toast.error("Failed to fetch subjects");
    console.error('Error fetching subjects:', error);
  } finally {
    isLoading.value = false;
  }
};

const fetchClasses = async () => {
  try {
    const response = await axios.get(`/api/classes?tenant_id=${tenantId}`);
    classes.value = response.data.data;
  } catch (error) {
    toast.error("Failed to fetch classes");
    console.error('Error fetching classes:', error.response?.data || error.message);
    if (error.response?.status === 401) {
      toast.warning("Session expired. Please refresh the page.");
      console.log('Session may have expired, refreshing page...');
      window.location.reload();
    }
  }
};

const initializeCsrf = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie');
  } catch (error) {
    console.error('Error getting CSRF cookie:', error);
  }
};

const calculateGrade = (score) => {
    if (score >= 80) return 'A';
    if (score >= 70) return 'B';
    if (score >= 60) return 'C';
    if (score >= 50) return 'D';
    if (score >= 40) return 'E';
    return 'F';
};

const generateRemarks = (score) => {
    if (score >= 90) return "Exceptional mastery of subject. Shows deep understanding and excellent analytical skills.";
    if (score >= 80) return "Strong performance. Demonstrates thorough understanding of concepts.";
    if (score >= 70) return "Good grasp of subject matter. Shows consistent effort and understanding.";
    if (score >= 60) return "Satisfactory performance. More practice needed in some areas.";
    if (score >= 50) return "Fair understanding. Needs to focus on improving key concepts.";
    if (score >= 40) return "Below average. Requires additional support and dedicated practice.";
    if (score >= 30) return "Significant improvement needed. Recommend remedial classes.";
    return "Critical attention required. Parent-teacher meeting recommended.";
};

const validateScore = (event) => {
    let value = parseFloat(event.target.value);
    if (isNaN(value)) {
        form.value.score = '';
    } else {
        value = Math.min(100, Math.max(0, value));
        form.value.score = value.toString();
        form.value.grade = calculateGrade(value);
        form.value.remarks = generateRemarks(value);
    }
};

onMounted(async () => {
  await initializeCsrf();
  await fetchClasses();
  await fetchSubjects();
});
</script>

<style scoped>
/* Add custom styles here */
</style>

<style>
/* Custom toast styles */
.Vue-Toastification__toast {
  padding: 1rem;
  border-radius: 0.5rem;
}

.Vue-Toastification__toast--success {
  background-color: #059669 !important;
}

.Vue-Toastification__toast--error {
  background-color: #dc2626 !important;
}

.Vue-Toastification__toast--warning {
  background-color: #d97706 !important;
}

/* Add loading animation styles */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>