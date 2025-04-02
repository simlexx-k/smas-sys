<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SelectInput from '@/components/SelectInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import {
  DocumentChartBarIcon,
  UserGroupIcon,
  CalendarIcon,
  ArrowDownTrayIcon,
  PrinterIcon
} from '@heroicons/vue/24/outline';

interface Props {
  classes: Array<{
    id: number;
    name: string;
    students_count: number;
  }>;
  exams: Array<{
    id: number;
    name: string;
    term: string;
  }>;
  reportData?: any;
  success?: boolean;
  error?: string;
}

const props = defineProps<Props>();

const reportType = ref('academic'); // academic, attendance
const loading = ref(false);

const academicForm = ref({
  class_id: '',
  exam_id: '',
  subject_id: null
});

const attendanceForm = ref({
  class_id: '',
  start_date: '',
  end_date: ''
});

const generateReport = async () => {
  loading.value = true;

  try {
    const endpoint = reportType.value === 'academic' 
      ? route('teacher.reports.academic')
      : route('teacher.reports.attendance');

    const formData = reportType.value === 'academic'
      ? academicForm.value
      : attendanceForm.value;

    await router.post(endpoint, formData);

  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const downloadPDF = async () => {
  try {
    const params = {
      class_id: reportType.value === 'academic' ? academicForm.value.class_id : attendanceForm.value.class_id,
      exam_id: academicForm.value.exam_id,
      report_type: reportType.value
    };

    // Create URL with query parameters
    const url = route('teacher.reports.download-pdf', params);
    window.open(url, '_blank');
  } catch (e) {
    console.error('Failed to download PDF:', e);
  }
};

const printReport = () => {
  window.print();
};
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
        Reports
      </h2>
    </template>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Report Type Selection -->
      <div class="mb-6">
        <div class="flex space-x-4">
          <button
            @click="reportType = 'academic'"
            :class="[
              'px-4 py-2 rounded-md flex items-center space-x-2',
              reportType === 'academic'
                ? 'bg-primary-500 text-white'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'
            ]"
          >
            <DocumentChartBarIcon class="h-5 w-5" />
            <span>Academic Report</span>
          </button>
          <button
            @click="reportType = 'attendance'"
            :class="[
              'px-4 py-2 rounded-md flex items-center space-x-2',
              reportType === 'attendance'
                ? 'bg-primary-500 text-white'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'
            ]"
          >
            <UserGroupIcon class="h-5 w-5" />
            <span>Attendance Report</span>
          </button>
        </div>
      </div>

      <!-- Report Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Report Filters</h3>
        
        <!-- Academic Report Filters -->
        <div v-if="reportType === 'academic'" class="space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Class
              </label>
              <SelectInput
                v-model="academicForm.class_id"
                class="mt-1 w-full"
              >
                <option value="">Select Class</option>
                <option v-for="class_ in classes" :key="class_.id" :value="class_.id">
                  {{ class_.name }}
                </option>
              </SelectInput>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Exam
              </label>
              <SelectInput
                v-model="academicForm.exam_id"
                class="mt-1 w-full"
              >
                <option value="">Select Exam</option>
                <option v-for="exam in exams" :key="exam.id" :value="exam.id">
                  {{ exam.name }} ({{ exam.term }})
                </option>
              </SelectInput>
            </div>
          </div>
        </div>

        <!-- Attendance Report Filters -->
        <div v-else class="space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Class
              </label>
              <SelectInput
                v-model="attendanceForm.class_id"
                class="mt-1 w-full"
              >
                <option value="">Select Class</option>
                <option v-for="class_ in classes" :key="class_.id" :value="class_.id">
                  {{ class_.name }}
                </option>
              </SelectInput>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Start Date
              </label>
              <input
                type="date"
                v-model="attendanceForm.start_date"
                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                End Date
              </label>
              <input
                type="date"
                v-model="attendanceForm.end_date"
                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
            </div>
          </div>
        </div>

        <div class="mt-4">
          <PrimaryButton
            @click="generateReport"
            :disabled="loading"
          >
            Generate Report
          </PrimaryButton>
        </div>
      </div>

      <!-- Show error if present -->
      <div v-if="props.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
        {{ props.error }}
      </div>

      <!-- Report Results -->
      <div v-if="props.success && props.reportData" class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <!-- Report Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-lg font-medium">
                {{ reportType === 'academic' ? 'Academic Report' : 'Attendance Report' }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ props.reportData.class.name }} - 
                {{ reportType === 'academic' 
                  ? `${props.reportData.exam.name} (${props.reportData.exam.term})`
                  : `${props.reportData.period.start} to ${props.reportData.period.end}`
                }}
              </p>
            </div>
            <div class="flex space-x-2">
              <button
                @click="downloadPDF"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                Download PDF
              </button>
              <button
                @click="printReport"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                <PrinterIcon class="h-4 w-4 mr-2" />
                Print
              </button>
            </div>
          </div>
        </div>

        <!-- Statistics -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Statistics</h4>
          <div class="grid grid-cols-4 gap-4">
            <template v-if="reportType === 'academic'">
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Class Average</div>
                <div class="text-2xl font-semibold">{{ props.reportData.statistics.class_average.toFixed(1) }}%</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Highest Score</div>
                <div class="text-2xl font-semibold">{{ props.reportData.statistics.highest_score }}%</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Lowest Score</div>
                <div class="text-2xl font-semibold">{{ props.reportData.statistics.lowest_score }}%</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Students</div>
                <div class="text-2xl font-semibold">{{ props.reportData.statistics.total_students }}</div>
              </div>
            </template>
            <template v-else>
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Average Attendance</div>
                <div class="text-2xl font-semibold">{{ props.reportData.statistics.average_attendance_rate.toFixed(1) }}%</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Perfect Attendance</div>
                <div class="text-2xl font-semibold">{{ props.reportData.statistics.perfect_attendance }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Days</div>
                <div class="text-2xl font-semibold">{{ props.reportData.period.total_days }}</div>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Students</div>
                <div class="text-2xl font-semibold">{{ props.reportData.statistics.total_students }}</div>
              </div>
            </template>
          </div>
        </div>

        <!-- Results Table -->
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Student Name
                  </th>
                  <template v-if="reportType === 'academic'">
                    <th v-for="score in props.reportData.results[0].scores" 
                        :key="score.subject" 
                        scope="col" 
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                    >
                      {{ score.subject }}
                    </th>
                  </template>
                  <template v-else>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Present Days
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Absent Days
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Late Days
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Attendance Rate
                    </th>
                  </template>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="result in props.reportData.results" :key="result.student_id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ result.student_name }}
                  </td>
                  <template v-if="reportType === 'academic'">
                    <td v-for="score in result.scores" 
                        :key="score.subject" 
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                    >
                      {{ score.score }}% ({{ score.grade }})
                    </td>
                  </template>
                  <template v-else>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ result.present_days }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ result.absent_days }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ result.late_days }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ result.attendance_rate }}%
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 