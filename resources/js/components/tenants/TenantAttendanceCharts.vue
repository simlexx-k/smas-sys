<template>
  <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-xl font-semibold text-gray-800">Attendance Overview</h3>
        <p class="text-sm text-gray-500">Last 30 days attendance pattern</p>
      </div>
      <div class="flex gap-2">
        <button
          @click="exportAsImage"
          class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 rounded-lg border border-gray-200"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Image
        </button>
        <button
          @click="exportAsCSV"
          class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 rounded-lg border border-gray-200"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          CSV
        </button>
      </div>
    </div>

    <!-- Chart Container -->
    <div class="relative h-96">
      <apexchart
        type="bar"
        height="100%"
        :options="chartOptions"
        :series="series"
      ></apexchart>
    </div>

    <!-- CSV Options Modal -->
    <div v-if="showCSVOptions" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center">
      <div class="bg-white p-6 rounded-xl w-96">
        <h4 class="text-lg font-semibold mb-4">Export Options</h4>
        <div class="space-y-4">
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="exportHeaders" class="rounded border-gray-300">
            Include Headers
          </label>
          <div class="flex gap-2 justify-end">
            <button @click="showCSVOptions = false" class="px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">
              Cancel
            </button>
            <button @click="confirmCSVExport" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
              Export
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
// import VueApexCharts from 'vue3-apexcharts';
import { useToast } from 'vue-toastification';
import { usePage } from '@inertiajs/vue3';

const toast = useToast();
const tenantId = usePage().props.auth.user.tenant_id;
console.log('Tenant ID:', tenantId);
console.log('Page props:', usePage().props);

// Sample Data
const attendanceData = reactive([
  // Data will be fetched from backend
]);

// Chart Configuration
const series = ref([{
  name: 'Present',
  data: attendanceData.map(d => d.present)
}, {
  name: 'Absent',
  data: attendanceData.map(d => d.absent)
}]);

const chartOptions = reactive({
  chart: {
    type: 'bar',
    stacked: false,
    toolbar: {
      show: true,
      tools: {
        download: false // Disable default download
      }
    }
  },
  colors: ['#10B981', '#EF4444'],
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '55%',
      borderRadius: 4
    },
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    show: true,
    width: 2,
    colors: ['transparent']
  },
  xaxis: {
    categories: attendanceData.map(d => new Date(d.date).toLocaleDateString()),
    labels: {
      style: {
        colors: '#6B7280',
        fontSize: '12px'
      }
    }
  },
  yaxis: {
    title: {
      text: 'Students',
      style: {
        color: '#6B7280',
        fontSize: '12px'
      }
    }
  },
  fill: {
    opacity: 1
  },
  tooltip: {
    y: {
      formatter: (val: number) => `${val} students`
    }
  },
  grid: {
    borderColor: '#F3F4F6',
    strokeDashArray: 4
  },
  responsive: [{
    breakpoint: 640,
    options: {
      chart: {
        height: 300
      }
    }
  }]
});

// Export Functionality
const showCSVOptions = ref(false);
const exportHeaders = ref(true);

const processAttendanceData = (rawData) => {
  const dateMap = new Map();

  rawData.forEach(record => {
    const date = record.date;
    if (!dateMap.has(date)) {
      dateMap.set(date, { present: 0, absent: 0 });
    }
    // Assuming status is stored in record.status
    if (record.status === 'present') {
      dateMap.get(date).present++;
    } else {
      dateMap.get(date).absent++;
    }
  });

  return Array.from(dateMap.entries()).map(([date, counts]) => ({
    date,
    ...counts
  }));
};

const fetchAttendanceData = async () => {
  try {
    const response = await axios.get('/api/attendances');
    console.log('Fetched attendance data:', response.data);

    const processedData = processAttendanceData(response.data.data);
    attendanceData.splice(0, attendanceData.length, ...processedData);

    series.value = [{
      name: 'Present',
      data: processedData.map(d => d.present)
    }, {
      name: 'Absent',
      data: processedData.map(d => d.absent)
    }];

    chartOptions.xaxis.categories = processedData.map(d => new Date(d.date).toLocaleDateString());
  } catch (error) {
    toast.error('Failed to fetch attendance data');
  }
};

onMounted(() => {
  fetchAttendanceData();
});

const exportAsImage = () => {
  const chartElement = document.querySelector('.apexcharts-canvas');
  if (chartElement) {
    const chartImage = chartElement as HTMLCanvasElement;
    const link = document.createElement('a');
    link.download = 'attendance-chart.png';
    link.href = chartImage.toDataURL();
    link.click();
    toast.success('Chart exported as image');
  }
};

const exportAsCSV = () => {
  showCSVOptions.value = true;
};

const confirmCSVExport = () => {
  const csvContent = [
    exportHeaders.value ? 'Date,Present,Absent' : '',
    ...attendanceData.map(d => 
      `${d.date},${d.present},${d.absent}`
    )
  ].join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv' });
  const link = document.createElement('a');
  link.download = 'attendance-records.csv';
  link.href = URL.createObjectURL(blob);
  link.click();
  showCSVOptions.value = false;
  toast.success('CSV exported successfully');
};

// For PDF export you would need a library like jspdf
// This is a placeholder for PDF export functionality
const exportAsPDF = () => {
  toast.info('PDF export requires additional setup');
};
</script>

<style scoped>
/* Add custom styles if needed */
</style>