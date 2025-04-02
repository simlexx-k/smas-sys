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
                    @input="validateScore($event, student.id)"
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
      </div>

      <!-- Save Button -->
      <div class="mt-6 flex justify-end">
        <button
          @click="saveScores"
          :disabled="!isValid || isSaving"
          class="btn-primary"
        >
          <span v-if="!isSaving">Save Scores</span>
          <span v-else class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Saving...
          </span>
        </button>
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

interface Score {
  score: number | null;
}

interface ReportCard {
  id: number;
  student_id: number;
  exam_id: number;
  subject_id: number;
  score: string;
  student: {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    school_class_id: number;
  };
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

const scores = ref<{ [key: number]: Score }>({});
const existingReportCards = ref<{ [key: number]: ReportCard }>({});

const breadcrumbs = [
  { title: 'Report Cards', href: '/report-cards' },
  { title: 'Bulk Entry', href: '/bulk-report-cards' },
];

// Computed properties
const showTable = computed(() => {
  return filters.value.class_id && filters.value.exam_id && filters.value.subject_id;
});

const filteredStudents = computed(() => {
  if (!students.value.length) return [];
  
  return students.value.map(student => {
    // Initialize score object if it doesn't exist
    if (!scores.value[student.id]) {
      scores.value[student.id] = {
        score: null
      };
    }
    return {
      ...student,
      hasExistingScore: !!existingReportCards.value[student.id]
    };
  });
});

const isValid = computed(() => {
  return Object.values(scores.value).every(score => {
    return score.score !== null && 
           score.score >= 0 && 
           score.score <= 100;
  });
});

// Watchers
watch(
  [() => filters.value.class_id, () => filters.value.exam_id, () => filters.value.subject_id],
  async ([newClassId, newExamId, newSubjectId]) => {
    if (newClassId && newExamId && newSubjectId) {
      try {
        const response = await axios.get('/api/report-cards', {
          params: {
            tenant_id: tenantId.value,
            class_id: newClassId,
            exam_id: newExamId,
            subject_id: newSubjectId
          }
        });

        // Reset scores and existing report cards
        scores.value = {};
        existingReportCards.value = {};

        // Populate only scores from existing report cards
        if (Array.isArray(response.data)) {
          response.data.forEach((reportCard) => {
            if (reportCard.student_id) {
              scores.value[reportCard.student_id] = {
                score: reportCard.score ? parseFloat(reportCard.score) : null
              };
              existingReportCards.value[reportCard.student_id] = reportCard;
            }
          });
        }

      } catch (error) {
        console.error('Error fetching report cards:', error);
        toast.error('Failed to fetch existing report cards');
      }
    }
  },
  { immediate: true }
);

watch(() => filters.value.class_id, async (newVal) => {
  if (newVal) {
    await fetchStudents();
  } else {
    students.value = [];
  }
});

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
  if (!tenantId.value || !filters.value.class_id) {
    students.value = [];
    return;
  }

  try {
    const response = await axios.get('/api/report-cards/students-by-class', {
      params: { 
        class_id: filters.value.class_id
      }
    });
    
    students.value = response.data;
    console.log('Students loaded:', students.value);

    // Initialize scores for new students
    students.value.forEach(student => {
      if (!scores.value[student.id]) {
        scores.value[student.id] = {
          score: null
        };
      }
    });
  } catch (error) {
    console.error('Error fetching students:', error);
    toast.error('Failed to load students');
    students.value = [];
  }
}

async function saveScores() {
  if (!isValid.value) {
    toast.error('Please enter valid scores (0-100) for all students');
    return;
  }

  try {
    isSaving.value = true;
    const reportCards = filteredStudents.value.map(student => ({
      student_id: student.id,
      exam_id: filters.value.exam_id,
      subject_id: filters.value.subject_id,
      tenant_id: tenantId.value,
      score: scores.value[student.id].score
    }));

    await axios.post('/api/report-cards/bulk', reportCards);
    toast.success('Scores saved successfully');
  } catch (error) {
    console.error('Error saving scores:', error);
    toast.error('Failed to save scores');
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
  @apply bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed;
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