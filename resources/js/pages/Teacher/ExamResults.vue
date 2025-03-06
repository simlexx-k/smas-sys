<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import SelectInput from '@/components/SelectInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import debounce from 'lodash/debounce';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { 
  ChevronDownIcon, 
  ArrowDownTrayIcon, 
  TableCellsIcon,
  ListBulletIcon,
  FunnelIcon,
  PrinterIcon,
  AdjustmentsHorizontalIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';

interface Props {
  classes: {
    id: number;
    name: string;
    students: Array<{
      id: number;
      first_name: string;
      last_name: string;
      student_id: string;
    }>;
  }[];
  subjects: {
    id: number;
    name: string;
  }[];
  exams: {
    id: number;
    name: string;
    term_name: string;
    academic_year: string;
  }[];
  filters: {
    class_id: number | null;
    exam_id: number | null;
    subject_id: number | null;
  };
}

const props = defineProps<Props>();

const form = useForm({
  class_id: props.filters.class_id || '',
  exam_id: props.filters.exam_id || '',
  subject_id: props.filters.subject_id || '',
});

const results = ref<Array<{
  student_id: number;
  first_name: string;
  last_name: string;
  student_id_number: string;
  score: string;
  grade: string;
  remarks: string;
  status: string;
  subjects?: Array<{
    subject_id: number;
    score: string;
    grade: string;
    remarks: string;
    status: string;
  }>;
}>>([]);

const loading = ref(false);
const saving = ref(false);
const message = ref('');
const error = ref('');
const showFilters = ref(true);
const viewMode = ref<'table' | 'list'>('table');
const showAdvancedFilters = ref(false);
const selectedTerm = ref('all');
const selectedYear = ref(new Date().getFullYear().toString());

const exportFormats = [
  { name: 'Export as PDF', value: 'pdf' },
  { name: 'Export as Excel', value: 'excel' },
  { name: 'Export as CSV', value: 'csv' },
];

const terms = [
  { name: 'All Terms', value: 'all' },
  { name: 'Term 1', value: 'term1' },
  { name: 'Term 2', value: 'term2' },
  { name: 'Term 3', value: 'term3' },
];

const years = Array.from({ length: 5 }, (_, i) => {
  const year = new Date().getFullYear() - i;
  return { name: year.toString(), value: year.toString() };
});

interface Stats {
  total_students: number;
  submitted_scores: number;
  class_average: number;
  highest_score: number;
  lowest_score: number;
}

interface GradeDistribution {
  EE: number;
  ME: number;
  AE: number;
  BE: number;
}

const stats = ref<Stats>({
  total_students: 0,
  submitted_scores: 0,
  class_average: 0,
  highest_score: 0,
  lowest_score: 0,
});

const gradeDistribution = ref<GradeDistribution>({
  EE: 0,
  ME: 0,
  AE: 0,
  BE: 0,
});

const calculateGrade = (score: number): string => {
  if (score >= 76) return 'EE';
  if (score >= 51) return 'ME';
  if (score >= 26) return 'AE';
  return 'BE';
};

const generateRemarks = (score: number): string => {
  if (score >= 76) return "Exceeding Expectation. Shows exceptional understanding and mastery of subject matter.";
  if (score >= 51) return "Meeting Expectation. Demonstrates good understanding of core concepts.";
  if (score >= 26) return "Approaching Expectation. Shows basic understanding but needs more practice.";
  return "Below Expectation. Requires immediate intervention and support.";
};

const availableSubjects = ref<Array<{ id: number; name: string }>>([]);

const loadResults = async () => {
  if (!form.class_id || !form.exam_id) {
    results.value = [];
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const response = await fetch(route('teacher.exam-results.get', {
      class_id: form.class_id,
      exam_id: form.exam_id,
      subject_id: form.subject_id || ''
    }));

    if (!response.ok) throw new Error('Failed to load results');

    const data = await response.json();
    console.log('API Response:', data);
    
    if (data.success) {
      results.value = data.data.results;
      stats.value = data.data.statistics;
      gradeDistribution.value = data.data.grade_distribution;
      availableSubjects.value = data.data.available_subjects || [];
      console.log('Available Subjects:', availableSubjects.value);
    } else {
      throw new Error(data.message);
    }

  } catch (e) {
    error.value = 'Failed to load results';
    console.error('Load Results Error:', e);
  } finally {
    loading.value = false;
  }
};

const saveResults = async () => {
  if (saving.value) return;
  
  saving.value = true;
  error.value = '';
  message.value = '';

  try {
    const response = await fetch(route('teacher.exam-results.store'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        class_id: form.class_id,
        exam_id: form.exam_id,
        subject_id: form.subject_id,
        results: form.subject_id ? 
          results.value.map(r => ({
            student_id: r.student_id,
            score: parseFloat(r.score) || 0,
            grade: r.grade,
            remarks: r.remarks,
          })) :
          results.value.map(r => ({
            student_id: r.student_id,
            subjects: r.subjects?.map(s => ({
              subject_id: s.subject_id,
              score: parseFloat(s.score) || 0,
              grade: s.grade,
              remarks: s.remarks,
            }))
          })),
      }),
    });

    if (!response.ok) throw new Error('Failed to save results');

    message.value = 'Results saved successfully';
    await loadResults();

  } catch (e) {
    error.value = 'Failed to save results. Please try again.';
  } finally {
    saving.value = false;
  }
};

const updateScore = (index: number, value: string) => {
  const score = parseFloat(value);
  results.value[index].score = value;
  results.value[index].grade = calculateGrade(score);
  results.value[index].remarks = generateRemarks(score);
};

const toggleFilters = () => {
  showFilters.value = !showFilters.value;
};

const handleExport = (format: string) => {
  // Implement export functionality
  console.log(`Exporting as ${format}`);
};

const toggleViewMode = () => {
  viewMode.value = viewMode.value === 'table' ? 'list' : 'table';
};

const printResults = () => {
  window.print();
};

watch([() => form.class_id, () => form.exam_id, () => form.subject_id], async () => {
  console.log('Filters changed:', {
    class_id: form.class_id,
    exam_id: form.exam_id,
    subject_id: form.subject_id
  });
  await loadResults();
});

const currentTab = ref('filters');
const tabs = [
  { id: 'filters', name: 'Filters' },
  { id: 'stats', name: 'Statistics', count: computed(() => stats.value.total_students) },
  { id: 'results', name: 'Results', count: computed(() => results.value.length) },
];

const getRemarkColor = (grade: string) => {
  switch (grade) {
    case 'EE': return 'text-green-600 dark:text-green-400';
    case 'ME': return 'text-blue-600 dark:text-blue-400';
    case 'AE': return 'text-yellow-600 dark:text-yellow-400';
    case 'BE': return 'text-red-600 dark:text-red-400';
    default: return 'text-gray-600 dark:text-gray-400';
  }
};

const getSubjectScore = (result: any, subjectId: number): string => {
  const subject = result.subjects?.find((s: any) => s.subject_id === subjectId);
  return subject ? subject.score : '';
};

const getSubjectGrade = (result: any, subjectId: number): string => {
  const subject = result.subjects?.find((s: any) => s.subject_id === subjectId);
  return subject ? subject.grade : '';
};

const updateSubjectScore = (result: any, subjectId: number, value: string) => {
  if (!result.subjects) {
    result.subjects = [];
  }
  
  const subject = result.subjects.find((s: any) => s.subject_id === subjectId);
  if (subject) {
    subject.score = value;
    subject.grade = calculateGrade(parseFloat(value));
    subject.remarks = generateRemarks(parseFloat(value));
  } else {
    result.subjects.push({
      subject_id: subjectId,
      score: value,
      grade: calculateGrade(parseFloat(value)),
      remarks: generateRemarks(parseFloat(value)),
      status: 'pending'
    });
  }
};

const getGradeClasses = (grade: string): string => {
  return [
    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
    grade === 'EE' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
    grade === 'ME' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' :
    grade === 'AE' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' :
    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
  ].join(' ');
};
</script>

<template>
  <AppLayout>
    <Head title="Exam Results" />

    <template #header>
      <div class="space-y-4">
        <!-- Header with Title and Filters -->
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
              Exam Results
            </h2>
            <!-- Essential Filters Inline -->
            <div class="flex items-center space-x-2">
              <SelectInput
                v-model="form.class_id"
                class="w-40 text-sm"
                placeholder="Select Class"
                @update:model-value="loadResults"
              >
                <option value="">All Classes</option>
                <option v-for="class_ in classes" :key="class_.id" :value="class_.id">
                  {{ class_.name }}
                </option>
              </SelectInput>

              <SelectInput
                v-model="form.exam_id"
                class="w-48 text-sm"
                placeholder="Select Exam"
                @update:model-value="loadResults"
              >
                <option value="">All Exams</option>
                <option v-for="exam in exams" :key="exam.id" :value="exam.id">
                  {{ exam.name }}
                </option>
              </SelectInput>

              <SelectInput
                v-model="form.subject_id"
                class="w-40 text-sm"
                placeholder="Select Subject"
                @update:model-value="loadResults"
              >
                <option value="">All Subjects</option>
                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                  {{ subject.name }}
                </option>
              </SelectInput>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center space-x-2">
            <Menu as="div" class="relative">
              <MenuButton class="inline-flex items-center gap-x-1 rounded-md bg-white dark:bg-gray-800 px-2.5 py-1.5 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                <ArrowDownTrayIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                Export
                <ChevronDownIcon class="-mr-0.5 h-4 w-4 text-gray-400" aria-hidden="true" />
              </MenuButton>

              <MenuItems class="absolute right-0 z-10 mt-2 w-48 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                  <MenuItem v-for="format in exportFormats" 
                          :key="format.value" 
                          v-slot="{ active }">
                    <button
                      @click="handleExport(format.value)"
                      :class="[
                        active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-700 dark:text-gray-300',
                        'flex w-full items-center px-4 py-2 text-sm'
                      ]"
                    >
                      {{ format.name }}
                    </button>
                  </MenuItem>
                </div>
              </MenuItems>
            </Menu>

            <button
              @click="toggleViewMode"
              class="inline-flex items-center gap-x-1 rounded-md bg-white dark:bg-gray-800 px-2.5 py-1.5 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <component :is="viewMode === 'table' ? ListBulletIcon : TableCellsIcon" 
                        class="h-4 w-4 text-gray-400" 
                        aria-hidden="true" />
              {{ viewMode === 'table' ? 'List' : 'Table' }}
            </button>

            <button
              @click="printResults"
              class="inline-flex items-center gap-x-1 rounded-md bg-white dark:bg-gray-800 px-2.5 py-1.5 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <PrinterIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
              Print
            </button>
          </div>
        </div>

        <!-- Quick Stats Bar -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg shadow px-4 py-2 text-sm">
          <div class="flex items-center space-x-6">
            <div>
              <span class="text-gray-500 dark:text-gray-400">Students:</span>
              <span class="ml-1 font-medium text-gray-900 dark:text-gray-100">{{ stats.total_students }}</span>
            </div>
            <div>
              <span class="text-gray-500 dark:text-gray-400">Submitted:</span>
              <span class="ml-1 font-medium text-gray-900 dark:text-gray-100">{{ stats.submitted_scores }}</span>
            </div>
            <div>
              <span class="text-gray-500 dark:text-gray-400">Average:</span>
              <span class="ml-1 font-medium text-gray-900 dark:text-gray-100">{{ stats.class_average.toFixed(1) }}%</span>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                EE: {{ gradeDistribution.EE }}
              </span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                ME: {{ gradeDistribution.ME }}
              </span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                AE: {{ gradeDistribution.AE }}
              </span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                BE: {{ gradeDistribution.BE }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div class="max-w-7xl mx-auto py-4 sm:px-6 lg:px-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-8">
        <svg class="animate-spin h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
        <p class="text-red-600 dark:text-red-400">{{ error }}</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="!results.length && form.class_id && form.exam_id && form.subject_id" 
           class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
        <div class="text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No results found</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Start by entering scores for this class.
          </p>
        </div>
      </div>

      <!-- Results Table -->
      <div v-if="results.length > 0" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Student ID
                </th>
                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Student Name
                </th>
                <!-- Single Subject View -->
                <template v-if="form.subject_id">
                  <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Score
                  </th>
                  <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Grade
                  </th>
                  <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Remarks
                  </th>
                </template>
                <!-- All Subjects View -->
                <template v-else>
                  <th v-for="subject in availableSubjects" 
                      :key="subject.id" 
                      scope="col" 
                      class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    {{ subject.name }}
                  </th>
                </template>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(result, index) in results" 
                  :key="result.student_id"
                  :class="index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700/50'"
              >
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ result.student_id }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ result.last_name }}, {{ result.first_name }}
                </td>
                <!-- Single Subject View -->
                <template v-if="form.subject_id">
                  <td class="px-3 py-2 whitespace-nowrap text-sm">
                    <input
                      type="number"
                      v-model="result.score"
                      class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500"
                      min="0"
                      max="100"
                      @input="updateScore(index, $event.target.value)"
                    />
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm">
                    <span :class="getGradeClasses(result.grade)">
                      {{ result.grade || '-' }}
                    </span>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      result.status === 'submitted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                    ]">
                      {{ result.status }}
                    </span>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm">
                    <Popover v-if="result.remarks" class="relative">
                      <PopoverButton
                        class="inline-flex items-center text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                      >
                        <InformationCircleIcon class="h-5 w-5" />
                        <span class="ml-1 text-xs">View Remarks</span>
                      </PopoverButton>

                      <PopoverPanel class="absolute z-10 w-72 px-4 mt-1 transform -translate-x-1/2 left-1/2">
                        <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                          <div class="relative bg-white dark:bg-gray-800 p-3">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                              {{ result.remarks }}
                            </p>
                          </div>
                        </div>
                      </PopoverPanel>
                    </Popover>
                    <span v-else class="text-gray-400 dark:text-gray-500">-</span>
                  </td>
                </template>
                <!-- All Subjects View -->
                <template v-else>
                  <td v-for="subject in availableSubjects" 
                      :key="subject.id" 
                      class="px-3 py-2 whitespace-nowrap text-sm">
                    <div class="flex items-center space-x-2">
                      <input
                        type="number"
                        :value="getSubjectScore(result, subject.id)"
                        class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500"
                        min="0"
                        max="100"
                        @input="updateSubjectScore(result, subject.id, $event.target.value)"
                      />
                      <span v-if="getSubjectGrade(result, subject.id)" 
                            :class="getGradeClasses(getSubjectGrade(result, subject.id))"
                      >
                        {{ getSubjectGrade(result, subject.id) }}
                      </span>
                    </div>
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Save Button -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right sm:px-6">
          <PrimaryButton
            @click="saveResults"
            :disabled="saving"
            :class="{ 'opacity-75 cursor-not-allowed': saving }"
          >
            <template v-if="saving">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Saving...
            </template>
            <template v-else>
              Save Results
            </template>
          </PrimaryButton>
        </div>
      </div>

      <!-- Initial State -->
      <div v-else class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Select filters</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Select a class, exam, and subject to manage results.
        </p>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@media print {
  .no-print {
    display: none;
  }
}

.popover-panel {
  @apply absolute z-10 w-72 px-4 mt-1 transform -translate-x-1/2 left-1/2;
}
</style> 