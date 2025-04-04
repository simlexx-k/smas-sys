<template>
    <AppLayout :breadcrumbs="breadcrumbs">
      <Head title="Report Cards" />
      <div class="flex flex-col gap-6 p-6 bg-gray-50 min-h-screen">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Report Card Management</h1>
          </div>
          <div class="flex gap-2">
            <button @click="openCreateModal" class="btn-primary">
              Create Report Card
            </button>
            <Link href="/bulk-report-cards" class="btn-primary">
              Bulk Entry
            </Link>
            <Link href="/batch-print-report-cards" class="btn-primary">
              Batch Print Report Cards
            </Link>
          </div>
        </div>
  
        <!-- Filters Section -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
              <select v-model="filters.class_id" class="form-select w-full rounded-md">
                <option value="">All Classes</option>
                <option v-for="schoolClass in classes" :key="schoolClass.id" :value="schoolClass.id">
                  {{ schoolClass.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Student</label>
              <select 
                v-model="filters.student_id" 
                class="form-select w-full rounded-md"
                :disabled="!filters.class_id"
              >
                <option value="">{{ filters.class_id ? 'Select Student' : 'Select a class first' }}</option>
                <option 
                  v-for="student in filteredStudents" 
                  :key="student.id" 
                  :value="student.id"
                >
                  {{ student.full_name }}
                </option>
              </select>
              <div v-if="filters.class_id" class="text-xs text-gray-500 mt-1">
                {{ filteredStudents.length }} students found
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Exam</label>
              <select v-model="filters.exam_id" class="form-select w-full rounded-md">
                <option value="">All Exams</option>
                <option v-for="exam in exams" :key="exam.id" :value="exam.id">
                  {{ exam.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
              <select v-model="filters.subject_id" class="form-select w-full rounded-md">
                <option value="">All Subjects</option>
                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                  {{ subject.name }}
                </option>
              </select>
            </div>
          </div>
          <!-- Filtering indicator -->
          <div v-if="isFiltering" class="mt-4 flex items-center justify-center text-sm text-gray-500">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Updating results...
          </div>
        </div>
  
        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
          <!-- Loading overlay for table -->
          <div v-if="isLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
            <div class="flex items-center space-x-4">
              <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span class="text-gray-700 text-lg font-medium">Loading report cards...</span>
            </div>
          </div>
  
          <!-- Table content -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="reportCard in filteredReportCards" :key="reportCard.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ reportCard.student?.full_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ reportCard.exam?.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ reportCard.subject?.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ reportCard.score }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ reportCard.grade }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ reportCard.remarks }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button @click="editReportCard(reportCard)" class="btn-secondary">Edit</button>
                    <button @click="deleteReportCard(reportCard.id)" class="btn-danger">Delete</button>
                    <button @click="printReportCard(reportCard)" class="btn-primary">Print</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
  
        <!-- Create/Edit Modal -->
        <transition enter-active-class="ease-out duration-300" leave-active-class="ease-in duration-200">
          <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
              <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
              </div>
              <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <h2 class="text-lg font-medium text-gray-900">{{ isEditing ? 'Edit' : 'Create' }} Report Card</h2>
                  <form @submit.prevent="saveReportCard()">
                    <div class="mt-4">
                      <label class="block text-sm font-medium text-gray-700">Class:</label>
                      <select v-model="modalFilters.class_id" class="form-select w-full rounded-md">
                        <option value="">Select Class</option>
                        <option v-for="schoolClass in classes" :key="schoolClass.id" :value="schoolClass.id">
                          {{ schoolClass.name }}
                        </option>
                      </select>
                    </div>
                    <div class="mt-4">
                      <label class="block text-sm font-medium text-gray-700">Student:</label>
                      <select v-model="currentReportCard.student_id" required class="form-select w-full rounded-md">
                        <option value="">Select Student</option>
                        <option v-for="student in modalFilteredStudents" :key="student.id" :value="student.id">
                          {{ student.full_name }}
                        </option>
                      </select>
                    </div>
                    <div class="mt-4">
                      <label class="block text-sm font-medium text-gray-700">Exam:</label>
                      <select v-model="currentReportCard.exam_id" required class="form-select w-full rounded-md">
                        <option value="">Select Exam</option>
                        <option v-for="exam in exams" :key="exam.id" :value="exam.id">
                          {{ exam.name }}
                        </option>
                      </select>
                    </div>
                    <div class="mt-4">
                      <label class="block text-sm font-medium text-gray-700">Subject:</label>
                      <select v-model="currentReportCard.subject_id" required class="form-select w-full rounded-md">
                        <option value="">Select Subject</option>
                        <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                          {{ subject.name }}
                        </option>
                      </select>
                    </div>
                    <div class="mt-4">
                      <label class="block text-sm font-medium text-gray-700">Score:</label>
                      <input 
                        type="number" 
                        v-model="currentReportCard.score" 
                        min="0" 
                        max="100" 
                        step="0.01" 
                        required
                        class="form-input w-full rounded-md"
                      >
                    </div>
                    <div class="mt-4">
                      <label class="block text-sm font-medium text-gray-700">Grade:</label>
                      <input 
                        type="text" 
                        v-model="currentReportCard.grade" 
                        required
                        class="form-input w-full rounded-md"
                      >
                    </div>
                    <div class="mt-4">
                      <label class="block text-sm font-medium text-gray-700">Remarks:</label>
                      <textarea 
                        v-model="currentReportCard.remarks"
                        class="form-textarea w-full rounded-md"
                        rows="3"
                      ></textarea>
                    </div>
                    <div class="mt-6">
                      <button type="submit" class="btn-primary">{{ isEditing ? 'Update' : 'Create' }}</button>
                      <button type="button" @click="closeModal" class="btn-secondary ml-2">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </AppLayout>
  </template>
  
  <script setup lang="ts">
  import { ref, computed, watch, nextTick, onMounted } from 'vue';
  import { Head, Link } from '@inertiajs/vue3';
  import AppLayout from '@/layouts/AppLayout.vue';
  import axios from 'axios';
  import { usePage } from '@inertiajs/vue3';
  import { useToast } from 'vue-toastification';
  
  interface ReportCard {
    id: number | null;
    student_id: number | null;
    exam_id: number | null;
    subject_id: number | null;
    score: number | null;
    grade: string;
    remarks: string;
    tenant_id: number | null;
    student?: {
      id: number;
      full_name: string;
      school_class_id: number;
    };
    exam?: {
      id: number;
      name: string;
    };
    subject?: {
      id: number;
      name: string;
    };
  }
  
  interface Student {
    id: number;
    full_name: string;
    school_class_id: number;
  }
  
  interface Exam {
    id: number;
    name: string;
  }
  
  interface Subject {
    id: number;
    name: string;
  }
  
  interface SchoolClass {
    id: number;
    name: string;
  }
  
  const reportCards = ref<ReportCard[]>([]);
  const showCreateModal = ref(false);
  const showEditModal = ref(false);
  const isEditing = ref(false);
  const currentReportCard = ref<ReportCard>({
    id: null,
    student_id: null,
    exam_id: null,
    subject_id: null,
    score: null,
    grade: '',
    remarks: '',
    tenant_id: usePage().props.tenant.id
  });
  const students = ref<Student[]>([]);
  const exams = ref<Exam[]>([]);
  const subjects = ref<Subject[]>([]);
  const classes = ref<SchoolClass[]>([]);
  
  const breadcrumbs = [
    {
      title: 'Report Cards',
      href: '/report_cards',
    },
  ];
  
  // Add filters ref
  const filters = ref({
    student_id: '',
    exam_id: '',
    subject_id: '',
    class_id: ''
  });
  
  // Add computed property for filtered report cards
  const filteredReportCards = computed(() => {
    return reportCards.value.filter(card => {
      if (filters.value.student_id && card.student_id !== filters.value.student_id) return false;
      if (filters.value.exam_id && card.exam_id !== filters.value.exam_id) return false;
      if (filters.value.subject_id && card.subject_id !== filters.value.subject_id) return false;
      return true;
    });
  });
  
  // Add computed property for filtered students
  const filteredStudents = computed(() => {
    console.log('Computing filtered students:', {
      classId: filters.value.class_id,
      allStudents: students.value,
      filtered: students.value.filter(student => 
        student.school_class_id === parseInt(filters.value.class_id)
      )
    });
    
    if (!filters.value.class_id) return [];
    return students.value.filter(student => 
      student.school_class_id === parseInt(filters.value.class_id)
    );
  });
  
  // Add these refs at the top with other refs
  const isLoading = ref(true);
  const isFiltering = ref(false);
  
  const toast = useToast();
  
  // Add watch to refresh data when filters change
  watch(filters, async () => {
    isFiltering.value = true;
    try {
      await fetchReportCards();
    } catch (error) {
      toast.error("Failed to apply filters");
    } finally {
      isFiltering.value = false;
    }
  }, { deep: true });
  
  // Add watch for class_id to reset student_id when class changes
  watch(() => filters.value.class_id, async (newVal, oldVal) => {
    console.log('Class changed from', oldVal, 'to', newVal);
    if (newVal) {
      await fetchStudents();
    } else {
      students.value = [];
    }
    filters.value.student_id = ''; // Reset student selection when class changes
  }, { immediate: true });
  
  const page = usePage();
  const tenantId = computed(() => page.props.tenant?.id);
  
  async function fetchReportCards() {
    try {
      const params = {
        student_id: filters.value.student_id,
        exam_id: filters.value.exam_id,
        subject_id: filters.value.subject_id,
        class_id: filters.value.class_id,
        tenant_id: tenantId.value
      };
      const response = await axios.get('/api/report-cards', { params });
      reportCards.value = response.data;
    } catch (error) {
      toast.error("Failed to fetch report cards");
      console.error('Error fetching report cards:', error);
    }
  }
  
  const fetchStudents = async () => {
    try {
      console.log('Fetching students for class:', filters.value.class_id);
      const response = await axios.get('/api/report-cards/students-by-class', {
        params: {
          class_id: filters.value.class_id
        }
      });
      
      students.value = response.data;
      console.log('Students loaded:', students.value);
    } catch (error) {
      console.error('Error fetching students:', error);
      toast.error('Failed to fetch students');
      students.value = [];
    }
  };
  
  async function fetchExams() {
    try {
      const response = await axios.get('/api/exams', {
        params: {
          tenant_id: tenantId.value,
          paginate: false
        }
      });
      if (response.data.data) {
        exams.value = response.data.data;
      } else {
        exams.value = response.data;
      }
    } catch (error) {
      console.error('Error fetching exams:', error);
    }
  }
  
  async function fetchSubjects() {
    try {
      const response = await axios.get('/api/subjects', {
        params: {
          tenant_id: tenantId.value,
          paginate: false
        }
      });
      if (response.data.data) {
        subjects.value = response.data.data;
      } else {
        subjects.value = response.data;
      }
    } catch (error) {
      console.error('Error fetching subjects:', error);
    }
  }
  
  const fetchClasses = async () => {
    try {
      if (!tenantId.value) {
        console.error('No tenant ID available');
        return;
      }

      const response = await axios.get('/api/classes', {
        params: {
          tenant_id: tenantId.value,
          paginate: false
        }
      });
      
      classes.value = response.data.data || response.data;
    } catch (error) {
      console.error('Error fetching classes:', error);
      toast.error('Failed to fetch classes');
    }
  };
  
  // Add modalFilters ref
  const modalFilters = ref({
    class_id: ''
  });
  
  // Add computed for modal filtered students
  const modalFilteredStudents = computed(() => {
    if (!modalFilters.value.class_id) return [];
    return students.value.filter(student => 
      student.school_class_id === parseInt(modalFilters.value.class_id)
    );
  });
  
  function openCreateModal() {
    // Reset the form
    currentReportCard.value = { 
      id: null, 
      student_id: null, 
      exam_id: null, 
      subject_id: null, 
      score: null, 
      grade: '', 
      remarks: '', 
      tenant_id: tenantId.value 
    };
    
    // Reset the class filter
    modalFilters.value.class_id = '';
    
    // Reset student selection
    currentReportCard.value.student_id = null;
    
    showCreateModal.value = true;
    isEditing.value = false;
  }
  
  function editReportCard(reportCard: ReportCard) {
    try {
      // Set the current report card data first
      currentReportCard.value = {
        id: reportCard.id,
        student_id: reportCard.student_id,
        exam_id: reportCard.exam_id,
        subject_id: reportCard.subject_id,
        score: reportCard.score,
        grade: reportCard.grade,
        remarks: reportCard.remarks,
        tenant_id: reportCard.tenant_id
      };

      // Then set the class filter if we have student data
      if (reportCard.student) {
        modalFilters.value.class_id = reportCard.student.school_class_id.toString();
      } else {
        // If no nested student data, find the student from our students array
        const student = students.value.find(s => s.id === reportCard.student_id);
        if (student) {
          modalFilters.value.class_id = student.school_class_id.toString();
        }
      }

      showEditModal.value = true;
      isEditing.value = true;

      // Debug log
      console.log('Edit modal data:', {
        reportCard,
        currentReportCard: currentReportCard.value,
        modalFilters: modalFilters.value,
        student: reportCard.student,
        modalFilteredStudents: modalFilteredStudents.value
      });
    } catch (error) {
      console.error('Error setting up edit modal:', error);
      toast.error("Failed to load report card for editing");
    }
  }
  
  function validateReportCard(reportCard: ReportCard) {
    if (!isEditing.value && !reportCard.student_id) {
      toast.error("Please select a student");
      return false;
    }
    if (!reportCard.exam_id) {
      toast.error("Please select an exam");
      return false;
    }
    if (!reportCard.subject_id) {
      toast.error("Please select a subject");
      return false;
    }
    if (!reportCard.score || reportCard.score < 0 || reportCard.score > 100) {
      toast.error("Please enter a valid score between 0 and 100");
      return false;
    }
    if (!reportCard.grade) {
      toast.error("Please enter a grade");
      return false;
    }
    return true;
  }
  
  async function saveReportCard() {
    if (!validateReportCard(currentReportCard.value)) {
      return;
    }
    try {
      const payload = { ...currentReportCard.value, tenant_id: tenantId.value };
      if (isEditing.value) {
        await axios.put(`/api/report-cards/${currentReportCard.value.id}`, payload);
        toast.success("Report card updated successfully");
      } else {
        await axios.post('/api/report-cards', payload);
        toast.success("Report card created successfully");
      }
      closeModal();
      await fetchReportCards();
    } catch (error) {
      toast.error(error.response?.data?.message || "Failed to save report card");
      console.error('Error saving report card:', error);
    }
  }
  
  async function deleteReportCard(id: number) {
    try {
      if (!confirm('Are you sure you want to delete this report card?')) {
        return;
      }
      await axios.delete(`/api/report-cards/${id}`);
      toast.success("Report card deleted successfully");
      await fetchReportCards();
    } catch (error) {
      toast.error("Failed to delete report card");
      console.error('Error deleting report card:', error);
    }
  }
  
  function closeModal() {
    showCreateModal.value = false;
    showEditModal.value = false;
  }
  
  function getStudentName(studentId: number | null) {
    const student = students.value.find((student) => student.id === studentId);
    return student ? student.full_name : '';
  }
  
  const printReportCard = (reportCard: ReportCard) => {
    const printWindow = window.open('', '', 'width=800,height=600');
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>Report Card</title>
            <style>
              body { font-family: Arial, sans-serif; }
              h1 { text-align: center; }
              table { width: 100%; border-collapse: collapse; margin-top: 20px; }
              th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            </style>
          </head>
          <body>
            <h1>Report Card</h1>
            <table>
              <tr><th>Student</th><td>${getStudentName(reportCard.student_id)}</td></tr>
              <tr><th>Exam</th><td>${reportCard.exam_id}</td></tr>
              <tr><th>Subject</th><td>${reportCard.subject_id}</td></tr>
              <tr><th>Score</th><td>${reportCard.score}</td></tr>
              <tr><th>Grade</th><td>${reportCard.grade}</td></tr>
              <tr><th>Remarks</th><td>${reportCard.remarks}</td></tr>
            </table>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };
  
  // Update the watch for modalFilters to handle both create and edit modes
  watch(() => modalFilters.value.class_id, () => {
    if (!isEditing.value) {
      currentReportCard.value.student_id = null;
    }
    // Add debug logging
    console.log('Class changed:', {
      classId: modalFilters.value.class_id,
      students: students.value,
      filtered: modalFilteredStudents.value
    });
  });
  
  console.log('Tenant ID on load:', usePage().props.tenant.id);
  onMounted(async () => {
    isLoading.value = true;
    try {
      await Promise.all([
        fetchClasses(),
        fetchExams(),
        fetchSubjects()
      ]);
      // Only fetch students if a class is selected
      if (filters.value.class_id) {
        await fetchStudents();
      }
      await fetchReportCards();
    } catch (error) {
      console.error('Error initializing data:', error);
      toast.error('Failed to load initial data');
    } finally {
      isLoading.value = false;
    }
  });
  </script>
  
  <style scoped>
  .btn-primary {
    @apply bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700;
  }
  .btn-secondary {
    @apply bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700;
  }
  .btn-danger {
    @apply bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700;
  }
  .form-select {
    @apply block w-full px-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md;
  }
  .loading-overlay {
    @apply absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10;
  }
  
  .loading-content {
    @apply flex items-center space-x-4;
  }
  
  .loading-spinner {
    @apply animate-spin h-8 w-8 text-indigo-600;
  }
  
  /* Custom toast styles */
  .Vue-Toastification__toast {
    @apply font-sans;
  }
  
  .Vue-Toastification__toast--success {
    @apply bg-green-600;
  }
  
  .Vue-Toastification__toast--error {
    @apply bg-red-600;
  }
  
  .Vue-Toastification__toast--info {
    @apply bg-blue-600;
  }
  
  .Vue-Toastification__toast--warning {
    @apply bg-yellow-500;
  }
  </style>