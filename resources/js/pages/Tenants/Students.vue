<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';
import { Search, Download, PlusCircle, ChevronFirst, ChevronLeft, ChevronRight, ChevronLast, X } from 'lucide-vue-next';
import jsPDF from 'jspdf';
import 'jspdf-autotable';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

interface Student {
  id: number;
  tenant_id: number;
  school_class_id: number;
  first_name: string;
  last_name: string;
  date_of_birth: string;
  gender: 'male' | 'female' | 'other';
  address: string;
  phone_number: string;
  email: string;
  created_at: string;
  updated_at: string;
}

const tenantId = usePage().props.tenant.id;

const students = ref<Student[]>([]);
const showForm = ref(false);
const showExportMenu = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);
const form = ref({
  id: null,
  tenant_id: usePage().props.tenant.id,
  school_class_id: null,
  first_name: '',
  last_name: '',
  date_of_birth: '',
  gender: 'male',
  address: '',
  phone_number: '',
  email: ''
});

const classes = ref([]);

const breadcrumbs = [
  {
    title: 'Students',
    href: '/students',
  },
];

// Filters
const searchQuery = ref('');
const selectedClass = ref('All');
const statusFilter = ref('All');

// Pagination
const itemsPerPage = ref(10);
const currentPage = ref(1);

// Computed properties for filtered and paginated data
const filteredStudents = computed(() => {
  return students.value.filter(student => {
    const matchesSearch = student.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         student.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         student.email.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesClass = selectedClass.value === 'All' || student.school_class_id === selectedClass.value;
    const matchesStatus = statusFilter.value === 'All' || student.gender === statusFilter.value;
    return matchesSearch && matchesClass && matchesStatus;
  });
});

const paginatedStudents = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredStudents.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredStudents.value.length / itemsPerPage.value);
});

// Export functionality
const exportToCSV = () => {
  const headers = ['ID', 'First Name', 'Last Name', 'Date of Birth', 'Gender', 'Address', 'Phone Number', 'Email'];
  const rows = filteredStudents.value.map(student => [student.id, student.first_name, student.last_name, student.date_of_birth, student.gender, student.address, student.phone_number, student.email]);
  const csvContent = [headers, ...rows].map(row => row.join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'students.csv';
  link.click();
};

const exportToPDF = () => {
  const doc = new jsPDF();
  
  // Add title
  doc.setFontSize(18);
  doc.text('Student List', 14, 22);
  
  // Prepare data for the table
  const headers = [['ID', 'First Name', 'Last Name', 'Date of Birth', 'Gender', 'Address', 'Phone Number', 'Email']];
  const data = filteredStudents.value.map(student => [
    student.id,
    student.first_name,
    student.last_name,
    student.date_of_birth,
    student.gender,
    student.address,
    student.phone_number,
    student.email
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
  doc.save('students.pdf');
};

// Class options for filter
const classOptions = computed(() => {
  const classes = new Set(students.value.map(student => student.school_class_id));
  return ['All', ...Array.from(classes)];
});

// Status options for filter
const statusOptions = ['All', 'male', 'female', 'other'];

const fetchClasses = async () => {
  try {
    const response = await axios.get(`/api/classes?tenant_id=${tenantId.value}`);
    classes.value = response.data.data;
  } catch (err) {
    error.value = 'Failed to fetch classes';
  }
};

// New CRUD operations
const fetchStudents = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await axios.get('/api/students');
    students.value = response.data;
  } catch (err) {
    error.value = 'Failed to fetch students';
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  form.value = { id: null, tenant_id: usePage().props.tenant.id, school_class_id: null, first_name: '', last_name: '', date_of_birth: '', gender: 'male', address: '', phone_number: '', email: '' };
  isEditing.value = false;
  showModal.value = true;
};

const openEditModal = (student: Student) => {
  form.value = { ...student };
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
  const errors = [];
  if (!form.value.first_name) errors.push('First name is required');
  if (!form.value.last_name) errors.push('Last name is required');
  if (!form.value.date_of_birth) errors.push('Date of birth is required');
  if (!form.value.gender) errors.push('Gender is required');
  if (!form.value.school_class_id) errors.push('Class is required');
  if (!form.value.address) errors.push('Address is required');
  if (!form.value.phone_number) errors.push('Phone number is required');
  if (!form.value.email) errors.push('Email is required');
  return errors;
};

const saveStudent = async () => {
  const errors = validateForm();
  if (errors.length > 0) {
    showErrorToast(errors.join('\n'));
    return;
  }

  try {
    loading.value = true;
    if (isEditing.value) {
      await axios.put(`/api/students/${form.value.id}`, form.value);
      showSuccessToast('Student updated successfully');
    } else {
      await axios.post('/api/students', form.value);
      showSuccessToast('Student created successfully');
    }
    await fetchStudents();
    closeModal();
  } catch (err) {
    showErrorToast('Failed to save student: ' + err.message);
  } finally {
    loading.value = false;
  }
};

const showDeleteConfirmation = ref(false);
const studentToDelete = ref(null);

const confirmDelete = (student) => {
  studentToDelete.value = student;
  showDeleteConfirmation.value = true;
};

const proceedWithDelete = async () => {
  if (studentToDelete.value) {
    await deleteStudent(studentToDelete.value.id);
    showDeleteConfirmation.value = false;
    studentToDelete.value = null;
  }
};

const deleteStudent = async (id: number) => {
  try {
    await axios.delete(`/api/students/${id}`);
    await fetchStudents();
  } catch (err) {
    error.value = 'Failed to delete student';
  }
};

onMounted(() => {
  fetchClasses();
  fetchStudents();
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Students" />
    <div class="flex flex-col gap-6 p-6 bg-gray-50 min-h-screen">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Learner Management</h1>
          <p class="text-sm text-gray-500 mt-1">Manage your institution's learners efficiently</p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
          <button 
            @click="openCreateModal" 
            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm w-full md:w-auto"
          >
            <PlusCircle class="w-5 h-5" />
            Add New Learner
          </button>
          <div class="relative">
            <button 
              @click="showExportMenu = !showExportMenu" 
              class="flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm w-full md:w-auto"
            >
              <Download class="w-5 h-5 text-gray-500" />
              Export
            </button>
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div 
                v-if="showExportMenu" 
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-10"
              >
                <button 
                  @click="exportToCSV" 
                  class="block w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 text-left"
                >
                  Export to CSV
                </button>
                <button 
                  @click="exportToPDF" 
                  class="block w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 text-left"
                >
                  Export to PDF
                </button>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search learners..."
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm placeholder-gray-400"
          />
        </div>
        <select 
          v-model="selectedClass" 
          class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700 bg-white"
        >
          <option v-for="option in classOptions" :key="option" :value="option">
            {{ option || 'Select Class' }}
          </option>
        </select>
        <select 
          v-model="statusFilter" 
          class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700 bg-white"
        >
          <option v-for="option in statusOptions" :key="option" :value="option">
            {{ option }}
          </option>
        </select>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="student in paginatedStudents" :key="student.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                      <span class="text-indigo-600 font-medium">{{ student.first_name[0] }}{{ student.last_name[0] }}</span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ student.first_name }} {{ student.last_name }}</div>
                      <div class="text-sm text-gray-500">{{ student.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                  {{ new Date(student.date_of_birth).toLocaleDateString() }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 capitalize">{{ student.gender }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div>{{ student.phone_number }}</div>
                  <div class="text-gray-500">{{ student.address }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ student.school_class_id || '–' }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-3">
                    <button 
                      @click="openEditModal(student)" 
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                      </svg>
                    </button>
                    <button 
                      @click="confirmDelete(student)" 
                      class="text-red-600 hover:text-red-900"
                      title="Delete"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!paginatedStudents.length" class="text-center py-12">
          <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
          </div>
          <p class="text-gray-500 text-sm">No learners found matching your criteria</p>
        </div>
      </div>

      <!-- Enhanced Modal -->
      <transition enter-active-class="ease-out duration-300" leave-active-class="ease-in duration-200">
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
              <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-6">
                  <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ isEditing ? 'Edit Learner Details' : 'Register New Learner' }}
                  </h3>
                  <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <X class="h-6 w-6" />
                  </button>
                </div>
                <!-- Enhanced Form -->
                <form @submit.prevent="saveStudent" class="space-y-6">
                  <!-- Form content similar to original but with better spacing and labels -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                      <input v-model="form.first_name" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                      <input v-model="form.last_name" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                      <input v-model="form.date_of_birth" type="date" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                      <select v-model="form.gender" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                    <select v-model="form.school_class_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                      <option v-for="classOption in classes" :key="classOption.id" :value="classOption.id">{{ classOption.name }}</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input v-model="form.address" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                      <input v-model="form.phone_number" type="text" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                      <input v-model="form.email" type="email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                  </div>

                  <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Save Student</button>
                  </div>
                </form>
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
                  <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                      Delete Learner
                    </h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        Are you sure you want to delete 
                        <span class="font-medium text-gray-900">
                          {{ studentToDelete?.first_name }} {{ studentToDelete?.last_name }}
                        </span>? 
                        This action cannot be undone.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                  @click="proceedWithDelete"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                  Delete Learner
                </button>
                <button
                  @click="showDeleteConfirmation = false"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- Enhanced Pagination -->
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="text-sm text-gray-500">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredStudents.length) }} of {{ filteredStudents.length }} results
        </div>
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-700">Rows per page:</span>
            <select 
              v-model="itemsPerPage" 
              class="px-2 py-1 border border-gray-300 rounded-md text-sm bg-white"
            >
              <option v-for="n in [5, 10, 20, 50]" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <div class="flex gap-1">
            <!-- Pagination buttons with numbers -->
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<style scoped>
.modal {
  display: none;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
  display: block;
}
/* Add custom transitions and animations */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}

@keyframes slideIn {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-enter-active {
  animation: slideIn 0.3s ease-out;
}
</style>