<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Batch Print Report Cards" />
    <div class="flex flex-col gap-6 p-6 bg-gray-50 min-h-screen">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Batch Print Report Cards</h1>
          <p class="text-sm text-gray-500 mt-1">Select a class and choose your printing option</p>
        </div>
      </div>

      <!-- Class Selection Section -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Select a Class</h2>
        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="classItem in classes"
            :key="classItem.id"
            @click="selectedClass = classItem.id"
            class="p-4 border rounded-lg cursor-pointer transition-all duration-200"
            :class="{
              'border-primary-500 bg-primary-50': selectedClass === classItem.id,
              'border-gray-200 hover:border-gray-300': selectedClass !== classItem.id
            }"
          >
            <div class="flex items-center gap-3">
              <div class="flex-1">
                <h3 class="font-medium text-gray-900">{{ classItem.name }}</h3>
                <p class="text-sm text-gray-500">{{ classItem.student_count }} student(s)</p>
              </div>
              <svg v-if="selectedClass === classItem.id" class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>
        <div v-if="error" class="mt-4 text-sm text-red-600">
          {{ error }}
        </div>
      </div>

      <!-- Exam Selection Section -->
      <div v-if="selectedClass" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Select an Exam</h2>
        <div v-if="examLoading" class="flex justify-center py-4">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="exam in exams"
            :key="exam.id"
            @click="selectedExam = exam.id"
            class="p-4 border rounded-lg cursor-pointer transition-all duration-200"
            :class="{
              'border-primary-500 bg-primary-50': selectedExam === exam.id,
              'border-gray-200 hover:border-gray-300': selectedExam !== exam.id
            }"
          >
            <div class="flex items-center gap-3">
              <div class="flex-1">
                <h3 class="font-medium text-gray-900">{{ exam.name }}</h3>
                <p class="text-sm text-gray-500">{{ exam.date }}</p>
                <p class="text-sm text-gray-500">{{ exam.subject }}</p>
              </div>
              <svg v-if="selectedExam === exam.id" class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>
        <div v-if="examError" class="mt-4 text-sm text-red-600">
          {{ examError }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Print Options</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button
            @click="printIndividual"
            class="flex flex-col items-center justify-center p-6 border rounded-lg hover:bg-gray-50 transition-colors duration-200"
            :disabled="!selectedClass || !selectedExam || printLoading"
          >
            <svg class="w-8 h-8 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span class="font-medium text-gray-900">Individual Report Cards</span>
            <span class="text-sm text-gray-500">Download separately</span>
          </button>
          <button
            @click="printNominalList"
            class="flex flex-col items-center justify-center p-6 border rounded-lg hover:bg-gray-50 transition-colors duration-200"
            :disabled="!selectedClass || !selectedExam"
          >
            <svg class="w-8 h-8 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span class="font-medium text-gray-900">Nominal List</span>
            <span class="text-sm text-gray-500">Print as single document</span>
          </button>
          <button
            @click="downloadZip"
            class="flex flex-col items-center justify-center p-6 border rounded-lg hover:bg-gray-50 transition-colors duration-200"
            :disabled="!selectedClass || !selectedExam"
          >
            <svg class="w-8 h-8 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span class="font-medium text-gray-900">Download ZIP</span>
            <span class="text-sm text-gray-500">Download all as ZIP</span>
          </button>
        </div>
        <div v-if="printError" class="mt-4 text-sm text-red-600">
          {{ printError }}
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';

// Get current user from page props
const user = usePage().props.auth.user;
if (!user) {
  console.error('User information not found in page props');
}
const tenantId = user?.tenant_id;

console.log('Tenant ID:', tenantId);

interface Class {
  id: number;
  name: string;
  student_count: number;
}

interface Exam {
  id: number;
  name: string;
  date: string;
  subject: string;
}

// State management
const classes = ref<Class[]>([]);
const selectedClass = ref<number | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);
const printLoading = ref(false);
const printError = ref<string | null>(null);

const exams = ref<Exam[]>([]);
const selectedExam = ref<number | null>(null);
const examLoading = ref(false);
const examError = ref<string | null>(null);

const breadcrumbs = [
  {
    title: 'Batch Print Report Cards',
    href: '/batch-print-report-cards',
  },
];

// Fetch classes with proper error handling
async function fetchClasses() {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await axios.get('/api/classes', {
      params: { tenant_id: tenantId }
    });

    console.log('API Response:', response.data);

    // Validate response data
    if (response.data && Array.isArray(response.data.data)) {
      classes.value = response.data.data.filter(classItem => {
        console.log('Class Item:', classItem);
        if (classItem && classItem.id && typeof classItem.id === 'number') {
          classItem.student_count = classItem.students?.length || 0;
          return true;
        }
        return false;
      });
    } else {
      throw new Error('Invalid response format from API');
    }
  } catch (err) {
    error.value = err.message || 'Failed to fetch classes';
    console.error('Error fetching classes:', err);
  } finally {
    loading.value = false;
  }
}

async function printIndividual() {
  if (!selectedClass.value || !selectedExam.value) return;

  try {
    printLoading.value = true;
    printError.value = null;

    const response = await axios.get(`/api/report-cards/batch-print`, {
      params: {
        class_id: Number(selectedClass.value),
        exam_id: Number(selectedExam.value),
        type: 'individual',
        tenant_id: tenantId
      },
      responseType: 'blob'
    });

    // Create Blob and download link
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `report-cards-class-${selectedClass.value}-exam-${selectedExam.value}.pdf`);
    document.body.appendChild(link);
    link.click();

    // Clean up
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (err) {
    printError.value = err.message || 'Failed to download report cards';
    console.error('Download error:', err);
  } finally {
    printLoading.value = false;
  }
}

async function printNominalList() {
  if (!selectedClass.value || !selectedExam.value) return;

  try {
    const response = await axios.get('/api/report-cards/batch-print', {
      params: {
        class_id: selectedClass.value,
        exam_id: selectedExam.value,
        type: 'nominal',
        tenant_id: tenantId
      },
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `nominal-list-${selectedClass.value}-${selectedExam.value}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    printError.value = 'Failed to download nominal list';
    console.error('Error downloading nominal list:', error);
  }
}

function downloadZip() {
  if (selectedClass.value && selectedExam.value) {
    window.location.href = `/api/report-cards/batch-print?class_id=${Number(selectedClass.value)}&exam_id=${Number(selectedExam.value)}&type=zip&tenant_id=${tenantId}`;
  }
}

// Watch for class selection changes
watch(selectedClass, async (newClassId) => {
  if (newClassId) {
    await fetchExams(newClassId);
  } else {
    exams.value = [];
    selectedExam.value = null;
  }
});

// Function to fetch exams
async function fetchExams(classId: number) {
  try {
    examLoading.value = true;
    examError.value = null;
    
    const response = await axios.get('/api/exams', {
      params: { 
        class_id: classId,
        tenant_id: tenantId,
        include: 'subject'
      }
    });

    if (Array.isArray(response.data)) {
      // Filter and map exams to ensure they belong to current tenant
      exams.value = response.data
        .filter(exam => 
          exam && 
          exam.id && 
          typeof exam.id === 'number' &&
          exam.tenant_id === tenantId
        )
        .map(exam => ({
          id: exam.id,
          name: exam.name,
          date: new Date(exam.date).toLocaleDateString(),
          subject: exam.subject?.name || 'General'
        }));

      if (exams.value.length === 0) {
        examError.value = 'No exams found for this class';
      }
    } else {
      throw new Error('Invalid response format from API');
    }
  } catch (err) {
    examError.value = err.message || 'Failed to fetch exams';
    console.error('Error fetching exams:', err);
  } finally {
    examLoading.value = false;
  }
}

// Initial fetch
fetchClasses();
</script>
