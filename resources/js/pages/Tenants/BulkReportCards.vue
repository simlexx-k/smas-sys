<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Bulk Report Cards Entry" />
    <div class="flex flex-col gap-6 p-6 bg-gray-50 min-h-screen">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Bulk Report Cards Entry</h1>
        <Link href="/report-cards" class="btn-secondary">
          Back to Report Cards
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
            <select v-model="filters.class_id" class="form-select w-full rounded-md">
              <option value="">Select Class</option>
              <option v-for="schoolClass in classes" :key="schoolClass.id" :value="schoolClass.id">
                {{ schoolClass.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Exam</label>
            <select v-model="filters.exam_id" class="form-select w-full rounded-md">
              <option value="">Select Exam</option>
              <option v-for="exam in exams" :key="exam.id" :value="exam.id">
                {{ exam.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <select v-model="filters.subject_id" class="form-select w-full rounded-md">
              <option value="">Select Subject</option>
              <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                {{ subject.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Students Table -->
      <div v-if="showTable" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="student in filteredStudents" :key="student.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ student.full_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input 
                    type="number" 
                    v-model="scores[student.id].score" 
                    class="form-input w-24 rounded-md"
                    min="0"
                    max="100"
                    step="0.01"
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input 
                    type="text" 
                    v-model="scores[student.id].grade" 
                    class="form-input w-24 rounded-md"
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input 
                    type="text" 
                    v-model="scores[student.id].remarks" 
                    class="form-input w-full rounded-md"
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      existingReportCards[student.id] ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ existingReportCards[student.id] ? 'Update' : 'New' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Save Button -->
        <div class="p-4 bg-gray-50 border-t border-gray-200">
          <button 
            @click="saveScores" 
            class="btn-primary"
            :disabled="!isValid || isSaving"
          >
            {{ isSaving ? 'Saving...' : 'Save All Scores' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

// Interfaces
interface SchoolClass {
  id: number;
  name: string;
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

interface ReportCard {
  id: number;
  student_id: number;
  exam_id: number;
  subject_id: number;
  score: number;
  grade: string;
  remarks: string;
}

// Setup refs and state
const toast = useToast();
const page = usePage();
const tenantId = computed(() => {
  const user = page.props.auth?.user;
  if (!user?.tenant_id) {
    console.error('No tenant ID found in auth user:', page.props.auth);
    toast.error('Tenant information not found');
    return null;
  }
  return user.tenant_id;
});

const classes = ref<SchoolClass[]>([]);
const exams = ref<Exam[]>([]);
const subjects = ref<Subject[]>([]);
const students = ref<Student[]>([]);
const isSaving = ref(false);

const filters = ref({
  class_id: '',
  exam_id: '',
  subject_id: ''
});

const scores = ref<Record<number, { score: number | null; grade: string; remarks: string }>>({});
const existingReportCards = ref<Record<number, ReportCard>>({});

const breadcrumbs = [
  { title: 'Report Cards', href: '/report-cards' },
  { title: 'Bulk Entry', href: '/bulk-report-cards' },
];

// Computed properties
const showTable = computed(() => {
  return filters.value.class_id && filters.value.exam_id && filters.value.subject_id;
});

const filteredStudents = computed(() => {
  if (!filters.value.class_id) return [];
  return students.value.filter(student => 
    Number(student.school_class_id) === Number(filters.value.class_id)
  );
});

const isValid = computed(() => {
  return filteredStudents.value.every(student => {
    const score = scores.value[student.id]?.score;
    return score !== null && score >= 0 && score <= 100;
  });
});

// Watchers
watch(
  // Watch the individual filter values instead of the array
  [
    () => filters.value.class_id,
    () => filters.value.exam_id,
    () => filters.value.subject_id
  ],
  async ([newClassId, newExamId, newSubjectId]) => {
    // Clear existing data when filters change
    existingReportCards.value = {};
    
    if (newClassId && newExamId && newSubjectId) {
      try {
        console.log('Fetching report cards with params:', {
          tenant_id: tenantId.value,
          class_id: newClassId,
          exam_id: newExamId,
          subject_id: newSubjectId
        });

        const response = await axios.get('/api/report-cards', {
          params: {
            tenant_id: tenantId.value,
            class_id: newClassId,
            exam_id: newExamId,
            subject_id: newSubjectId
          }
        });
        
        console.log('Received report cards:', response.data);
        
        // Create a map of student_id to report card
        existingReportCards.value = response.data.reduce((acc: Record<number, ReportCard>, card: ReportCard) => {
          acc[card.student_id] = card;
          return acc;
        }, {});

        // Initialize scores with existing data
        filteredStudents.value.forEach(student => {
          const existingCard = existingReportCards.value[student.id];
          if (existingCard) {
            console.log('Found existing card for student:', student.id, existingCard);
          }
          scores.value[student.id] = {
            score: existingCard?.score ?? null,
            grade: existingCard?.grade ?? '',
            remarks: existingCard?.remarks ?? ''
          };
        });
      } catch (error) {
        console.error('Error fetching existing report cards:', error);
        toast.error('Failed to load existing scores');
      }
    }
  },
  { immediate: true } // This will run the watcher immediately when component mounts
);

// API Functions
async function fetchClasses() {
  if (!tenantId.value) {
    toast.error('Tenant ID is required');
    return;
  }

  try {
    const response = await axios.get('/api/classes', {
      params: { 
        tenant_id: tenantId.value 
      }
    });
    classes.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching classes:', error);
    toast.error('Failed to load classes');
  }
}

async function fetchExams() {
  if (!tenantId.value) {
    toast.error('Tenant ID is required');
    return;
  }

  try {
    const response = await axios.get('/api/exams', {
      params: { 
        tenant_id: tenantId.value 
      }
    });
    exams.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching exams:', error);
    toast.error('Failed to load exams');
  }
}

async function fetchSubjects() {
  if (!tenantId.value) {
    toast.error('Tenant ID is required');
    return;
  }

  try {
    const response = await axios.get('/api/subjects', {
      params: { 
        tenant_id: tenantId.value 
      }
    });
    subjects.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching subjects:', error);
    toast.error('Failed to load subjects');
  }
}

async function fetchStudents() {
  if (!tenantId.value) {
    toast.error('Tenant ID is required');
    return;
  }

  try {
    const response = await axios.get('/api/students', {
      params: { 
        tenant_id: tenantId.value 
      }
    });
    students.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching students:', error);
    toast.error('Failed to load students');
  }
}

async function saveScores() {
  if (!tenantId.value) {
    toast.error('Tenant ID is required');
    return;
  }

  if (!isValid.value) {
    toast.error('Please enter valid scores for all students');
    return;
  }

  isSaving.value = true;
  try {
    const reportCards = filteredStudents.value.map(student => ({
      student_id: student.id,
      exam_id: filters.value.exam_id,
      subject_id: filters.value.subject_id,
      score: scores.value[student.id].score,
      grade: scores.value[student.id].grade,
      remarks: scores.value[student.id].remarks,
      tenant_id: tenantId.value
    }));

    await axios.post('/api/report-cards/bulk', reportCards);
    toast.success('Scores saved successfully');
    scores.value = {};
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save scores');
    console.error('Error saving scores:', error);
  } finally {
    isSaving.value = false;
  }
}

// Initialize data only if we have a tenant ID
if (tenantId.value) {
  fetchClasses();
  fetchExams();
  fetchSubjects();
  fetchStudents();
} else {
  toast.error('No tenant ID found. Please refresh the page.');
}
</script>

<style scoped>
.btn-primary {
  @apply bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700;
}

.btn-secondary {
  @apply bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700;
}

.form-select {
  @apply block w-full px-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md;
}

.form-input {
  @apply block w-full px-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md;
}
</style>