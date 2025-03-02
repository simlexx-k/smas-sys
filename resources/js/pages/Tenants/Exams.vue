<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-semibold mb-6">Exams</h1>

      <div class="mb-6">
        <button @click="openCreateModal" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Add New Exam
        </button>
      </div>

      <div class="flex flex-col gap-6">
        <!-- Filters Section -->
        <div class="flex flex-col md:flex-row gap-4">
          <div class="relative flex-1">
            <input
              v-model="searchQuery"
              type="text"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              placeholder="Search exams..."
            />
            <Search class="absolute right-3 top-2.5 h-4 w-4 text-gray-400" />
          </div>

          <select
            v-model="selectedSubject"
            class="block w-full md:w-48 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          >
            <option value="All">All Subjects</option>
            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
              {{ subject.name }}
            </option>
          </select>
        </div>

        <!-- Exams Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Subject
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="exam in paginatedExams" :key="exam.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ exam.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ exam.subject?.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ new Date(exam.date).toLocaleDateString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="openEditModal(exam)"
                      class="text-blue-600 hover:text-blue-900 mr-4"
                    >
                      Edit
                    </button>
                    <button
                      @click="confirmDelete(exam)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="currentPage = Math.max(1, currentPage - 1)"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Previous
              </button>
              <button
                @click="currentPage = Math.min(totalPages, currentPage + 1)"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredExams.length) }}</span>
                  of
                  <span class="font-medium">{{ filteredExams.length }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="currentPage = 1"
                    :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    <ChevronFirst class="h-5 w-5" />
                  </button>
                  <button
                    @click="currentPage = Math.max(1, currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    <ChevronLeft class="h-5 w-5" />
                  </button>
                  <button
                    @click="currentPage = Math.min(totalPages, currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    <ChevronRight class="h-5 w-5" />
                  </button>
                  <button
                    @click="currentPage = totalPages"
                    :disabled="currentPage === totalPages"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    <ChevronLast class="h-5 w-5" />
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <!-- Create/Edit Modal -->
        <transition enter-active-class="ease-out duration-300" leave-active-class="ease-in duration-200">
          <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
              <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
              </div>
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
              <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                      <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ isEditing ? 'Edit Exam' : 'Create Exam' }}
                      </h3>
                      <div class="mt-2">
                        <form @submit.prevent="saveExam">
                          <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Exam Name</label>
                            <input
                              v-model="form.name"
                              type="text"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                              required
                            />
                          </div>
                          <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input
                              v-model="form.date"
                              type="date"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                              required
                            />
                          </div>
                          <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Tenant</label>
                            <input
                              v-model="form.tenant_id"
                              type="text"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                              required
                              disabled
                            />
                          </div>
                          <div class="flex justify-end">
                            <button
                              type="button"
                              @click="closeModal"
                              class="mr-2 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                              Cancel
                            </button>
                            <button
                              type="submit"
                              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                              Save
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </transition>

        <!-- Delete Confirmation Modal -->
        <transition enter-active-class="ease-out duration-300" leave-active-class="ease-in duration-200">
          <div v-if="showDeleteConfirmation" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
              <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
              </div>
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
              <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                      <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Delete Exam
                      </h3>
                      <div class="mt-2">
                        <p class="text-sm text-gray-500">
                          Are you sure you want to delete this exam? This action cannot be undone.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button
                    type="button"
                    @click="proceedWithDelete"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                  >
                    Delete
                  </button>
                  <button
                    type="button"
                    @click="showDeleteConfirmation = false"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>
        </transition>

        <!-- Toast Notification -->
        <transition enter-active-class="ease-out duration-300" leave-active-class="ease-in duration-200">
          <div
            v-if="showToast"
            class="fixed bottom-4 right-4 z-50"
          >
            <div
              :class="{
                'bg-green-500': toastType === 'success',
                'bg-red-500': toastType === 'error'
              }"
              class="text-white px-4 py-2 rounded-md shadow-md"
            >
              {{ toastMessage }}
            </div>
          </div>
        </transition>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';
import { Search, Download, PlusCircle, ChevronFirst, ChevronLeft, ChevronRight, ChevronLast, X } from 'lucide-vue-next';
import jsPDF from 'jspdf';
import 'jspdf-autotable';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

interface Exam {
  id: number;
  tenant_id: number;
  name: string;
  subject_id: number;
  date: string;
  created_at: string;
  updated_at: string;
  subject?: {
    name: string;
  };
}

const tenantId = usePage().props.tenant.id;

const exams = ref<Exam[]>([]);
const showForm = ref(false);
const showExportMenu = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);
const form = ref({
  id: null,
  tenant_id: tenantId,
  name: '',
  date: '',
});

const subjects = ref([]);

const breadcrumbs = [
  {
    title: 'Exams',
    href: '/exams',
  },
];

// Filters
const searchQuery = ref('');
const selectedSubject = ref('All');

// Pagination
const itemsPerPage = ref(10);
const currentPage = ref(1);

// Computed properties for filtered and paginated data
const filteredExams = computed(() => {
  return exams.value || [];
});

const paginatedExams = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredExams.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredExams.value.length / itemsPerPage.value);
});

// Export functionality
const exportToCSV = () => {
  const headers = ['ID', 'Name', 'Subject', 'Date'];
  const rows = filteredExams.value.map(exam => [exam.id, exam.name, exam.subject?.name, exam.date]);
  const csvContent = [headers, ...rows].map(row => row.join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'exams.csv';
  link.click();
};

const exportToPDF = () => {
  const doc = new jsPDF();
  
  // Add title
  doc.setFontSize(18);
  doc.text('Exam List', 14, 22);
  
  // Prepare data for the table
  const headers = [['ID', 'Name', 'Subject', 'Date']];
  const data = filteredExams.value.map(exam => [
    exam.id,
    exam.name,
    exam.subject?.name,
    exam.date
  ]);
  
  // Add table
  doc.autoTable({
    head: headers,
    body: data,
    startY: 30,
    theme: 'striped',
    styles: {
      fontSize: 10,
      cellPadding: 2
    },
    headStyles: {
      fillColor: [41, 128, 185],
      textColor: 255
    }
  });
  
  // Save the PDF
  doc.save('exams.pdf');
};

// Subject options for filter
const subjectOptions = computed(() => {
  const subjects = new Set(exams.value.map(exam => exam.subject_id));
  return ['All', ...Array.from(subjects)];
});

const fetchSubjects = async () => {
  try {
    const response = await axios.get(`/api/subjects?tenant_id=${tenantId}`);
    subjects.value = response.data.data;
  } catch (err) {
    error.value = 'Failed to fetch subjects';
  }
};

const fetchExams = async () => {
  try {
    const response = await axios.get('/api/exams', {
      params: { tenant_id: tenantId }
    });
    exams.value = response.data;
  } catch (err) {
    error.value = 'Failed to fetch exams';
    console.error(err);
  }
};

const openCreateModal = () => {
  form.value = { id: null, tenant_id: tenantId, name: '', date: '' };
  isEditing.value = false;
  showModal.value = true;
};

const openEditModal = (exam: Exam) => {
  form.value = { ...exam };
  isEditing.value = true;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const showSuccessToast = (message) => {
  toastMessage.value = message;
  toastType.value = 'success';
  showToast.value = true;
  setTimeout(() => showToast.value = false, 3000);
};

const showErrorToast = (message) => {
  toastMessage.value = message;
  toastType.value = 'error';
  showToast.value = true;
  setTimeout(() => showToast.value = false, 3000);
};

const validateForm = () => {
  if (!form.value.name || !form.value.date) {
    showErrorToast('Please fill all required fields');
    return false;
  }
  return true;
};

const saveExam = async () => {
  if (!validateForm()) return;

  try {
    if (isEditing.value) {
      await axios.put(`/api/exams/${form.value.id}`, form.value);
      showSuccessToast('Exam updated successfully');
    } else {
      await axios.post('/api/exams', form.value);
      showSuccessToast('Exam created successfully');
    }
    fetchExams();
    closeModal();
  } catch (err) {
    showErrorToast('Error saving exam');
  }
};

const showDeleteConfirmation = ref(false);
const examToDelete = ref(null);

const confirmDelete = (exam: Exam) => {
  examToDelete.value = exam;
  showDeleteConfirmation.value = true;
};

const proceedWithDelete = async () => {
  if (!examToDelete.value) return;

  try {
    await axios.delete(`/api/exams/${examToDelete.value.id}`);
    showSuccessToast('Exam deleted successfully');
    fetchExams();
  } catch (err) {
    showErrorToast('Error deleting exam');
  } finally {
    showDeleteConfirmation.value = false;
    examToDelete.value = null;
  }
};

onMounted(() => {
  fetchSubjects();
  fetchExams();
});
</script>

<style scoped>
/* Add custom styles here */
</style>
