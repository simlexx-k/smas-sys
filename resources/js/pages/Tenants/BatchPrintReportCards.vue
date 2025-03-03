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
            class="border rounded-lg overflow-hidden"
          >
            <!-- Class Header -->
            <div class="p-4 cursor-pointer transition-all duration-200 flex items-center justify-between">
              <!-- Click handler for the main area (selects the class) -->
              <div 
                class="flex-1" 
                @click="selectedClass = classItem.id"
              >
                <h3 class="font-medium text-gray-900">{{ classItem.name }}</h3>
                <p class="text-sm text-gray-500">{{ classItem.student_count }} student(s)</p>
              </div>
              <div class="flex items-center gap-2">
                <svg 
                  v-if="selectedClass === classItem.id" 
                  class="w-5 h-5 text-primary-500" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <!-- Separate click handler for the expand/collapse button -->
                <button
                  @click.stop="toggleExpand(classItem.id)"
                  class="p-1 hover:bg-gray-100 rounded-full"
                >
                  <svg 
                    class="w-5 h-5 text-gray-400 transform transition-transform"
                    :class="{ 'rotate-180': expandedClasses[classItem.id] }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Students List (Expandable) -->
            <div 
              v-if="expandedClasses[classItem.id]"
              class="border-t border-gray-200 bg-gray-50"
            >
              <div class="p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Students List</h4>
                <div class="max-h-48 overflow-y-auto">
                  <ul class="space-y-1">
                    <li 
                      v-for="student in classItem.students" 
                      :key="student.id"
                      class="text-sm text-gray-600 py-1 px-2 rounded hover:bg-gray-100"
                    >
                      {{ student.full_name }}
                    </li>
                  </ul>
                  <div v-if="!classItem.students?.length" class="text-sm text-gray-500 italic">
                    No students found
                  </div>
                </div>
              </div>
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

      <!-- Student Selection Section -->
      <div v-if="selectedClass && selectedExam" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Select a Student</h2>
        
        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        </div>
        
        <div v-else class="grid grid-cols-1 gap-4">
          <!-- Find and display students for selected class -->
          <div v-if="selectedClassStudents.length > 0" class="border rounded-lg p-4">
            <div class="max-h-64 overflow-y-auto">
              <div
                v-for="student in selectedClassStudents"
                :key="student.id"
                @click="selectedStudent = student.id"
                class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors duration-200"
                :class="{
                  'bg-primary-50 border border-primary-200': selectedStudent === student.id,
                  'hover:bg-gray-50 border border-transparent': selectedStudent !== student.id
                }"
              >
                <input
                  type="radio"
                  :name="'student'"
                  :value="student.id"
                  v-model="selectedStudent"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500"
                />
                <div>
                  <h3 class="font-medium text-gray-900">{{ student.full_name }}</h3>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            No students found in this class
          </div>
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
            @click="downloadBatchReportCards"
            class="flex flex-col items-center justify-center p-6 border rounded-lg hover:bg-gray-50 transition-colors duration-200"
            :disabled="!selectedClass || !selectedExam || printLoading"
          >
            <svg class="w-8 h-8 text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span class="font-medium text-gray-900">Batch Print Report Cards</span>
            <span class="text-sm text-gray-500">Download all report cards as one PDF</span>
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
import { ref, watch, computed } from 'vue';
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

interface Student {
  id: number;
  full_name: string;
}

interface Class {
  id: number;
  name: string;
  student_count: number;
  students: Student[];
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

// Add new ref for expanded classes
const expandedClasses = ref<Record<number, boolean>>({});

// Add student selection state
const selectedStudent = ref<number | null>(null);

const breadcrumbs = [
  {
    title: 'Batch Print Report Cards',
    href: '/batch-print-report-cards',
  },
];

// Add function to toggle class details
function toggleExpand(classId: number) {
  expandedClasses.value[classId] = !expandedClasses.value[classId];
}

// Update fetchClasses function to include student data
async function fetchClasses() {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await axios.get('/api/classes', {
      params: { 
        tenant_id: tenantId,
        include: 'students'
      }
    });

    console.log('API Response:', response.data);

    if (response.data && Array.isArray(response.data.data)) {
      classes.value = response.data.data.map(classItem => ({
        id: classItem.id,
        name: classItem.name,
        student_count: classItem.students?.length || 0,
        students: classItem.students?.map(student => ({
          id: student.id,
          full_name: student.full_name || `${student.first_name} ${student.last_name}`
        })) || []
      })).filter(classItem => classItem.id && typeof classItem.id === 'number');

      console.log('Processed classes:', classes.value);
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

// Modify the print function
async function printIndividual() {
    if (!selectedClass.value || !selectedExam.value || !selectedStudent.value) {
        error.value = 'Please select a class, exam and student';
        return;
    }

    try {
        printLoading.value = true;
        printError.value = null;

        const response = await axios.get(`/api/report-cards/batch-print`, {
            params: {
                class_id: Number(selectedClass.value),
                exam_id: Number(selectedExam.value),
                student_id: Number(selectedStudent.value),
                type: 'individual',
                tenant_id: tenantId
            },
            responseType: 'blob'
        });

        // Create Blob and download
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `report-card.pdf`);
        document.body.appendChild(link);
        link.click();
        window.URL.revokeObjectURL(url);
    } catch (err) {
        printError.value = err.message || 'Failed to download report card';
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

async function downloadBatchReportCards() {
    if (!selectedClass.value || !selectedExam.value) return;
    
    try {
        printLoading.value = true;
        printError.value = null;

        const response = await axios.get('/api/report-cards/batch-print', {
            params: {
                class_id: selectedClass.value,
                exam_id: selectedExam.value,
                type: 'batch',
                tenant_id: tenantId
            },
            responseType: 'blob'
        });

        // Create and trigger download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `report_cards_${selectedClass.value}_${selectedExam.value}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        printError.value = 'Failed to download report cards';
        console.error('Error downloading report cards:', error);
    } finally {
        printLoading.value = false;
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

// Add this computed property
const selectedClassStudents = computed(() => {
  const selectedClassData = classes.value.find(c => c.id === selectedClass.value);
  return selectedClassData?.students || [];
});

// Initial fetch
fetchClasses();

// Add these helper functions at the script section
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

// Add this to your data properties
const allowOverride = ref(false);

// Add this method to your script section
const updateGradeAndRemarks = (subject) => {
    if (!allowOverride.value) {
        const score = parseFloat(subject.score);
        if (!isNaN(score)) {
            subject.grade = calculateGrade(score);
            subject.remarks = generateRemarks(score);
        }
    }
};

// Update your save/submit method to include the calculated values
const submitForm = async () => {
    try {
        // Update grades and remarks before submission if not overridden
        if (!allowOverride.value) {
            subjects.value.forEach(subject => {
                const score = parseFloat(subject.score);
                if (!isNaN(score)) {
                    subject.grade = calculateGrade(score);
                    subject.remarks = generateRemarks(score);
                }
            });
        }

        // Your existing submission code...
        
    } catch (error) {
        console.error('Error submitting form:', error);
        // Error handling...
    }
};

// Add a watcher for the allowOverride value
watch(allowOverride, (newValue) => {
    if (!newValue) {
        // When override is disabled, recalculate all grades and remarks
        subjects.value.forEach(subject => {
            updateGradeAndRemarks(subject);
        });
    }
});
</script>

<style scoped>
/* Add these styles for smooth transitions */
.transform {
  transition: transform 0.2s ease-in-out;
}

/* Add custom scrollbar styles */
.max-h-48 {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E0 #EDF2F7;
}

.max-h-48::-webkit-scrollbar {
  width: 6px;
}

.max-h-48::-webkit-scrollbar-track {
  background: #EDF2F7;
}

.max-h-48::-webkit-scrollbar-thumb {
  background-color: #CBD5E0;
  border-radius: 3px;
}
</style>
