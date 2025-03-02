<template>
  <div class="tenant-admin-dashboard p-6 bg-gray-50 min-h-screen">
    <header class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Dashboard Overview</h1>
      <p class="text-gray-500 mt-2">Welcome back, {{ userName }}. Here's your daily summary</p>
    </header>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
      <div class="bg-white p-6 rounded-xl shadow-sm lg:col-span-2 xl:col-span-1">
          <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
          <div class="flex gap-3">
            <button class="action-btn bg-blue-100 text-blue-600 hover:bg-blue-200 p-4 rounded-lg text-center flex-1">
              <i class="fas fa-plus text-lg mb-2"></i>
              <p class="text-sm font-medium">New Student</p>
            </button>
            <button class="action-btn bg-green-100 text-green-600 hover:bg-green-200 p-4 rounded-lg text-center flex-1">
              <i class="fas fa-user-plus text-lg mb-2"></i>
              <p class="text-sm font-medium">New Staff</p>
            </button>
            <button class="action-btn bg-purple-100 text-purple-600 hover:bg-purple-200 p-4 rounded-lg text-center flex-1">
              <i class="fas fa-bullhorn text-lg mb-2"></i>
              <p class="text-sm font-medium">Send Notice</p>
            </button>
            <button class="action-btn bg-yellow-100 text-yellow-600 hover:bg-yellow-200 p-4 rounded-lg text-center flex-1">
              <i class="fas fa-file-alt text-lg mb-2"></i>
              <p class="text-sm font-medium">Generate Report</p>
            </button>
            <button class="action-btn bg-red-100 text-red-600 hover:bg-red-200 p-4 rounded-lg text-center flex-1">
              <i class="fas fa-shield-alt text-lg mb-2"></i>
              <p class="text-sm font-medium">Manage Permissions</p>
            </button>
          </div>
        </div>

      <!-- Repeat similar cards for Staff, Attendance, etc. -->
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column -->
      <div class="lg:col-span-2">
        <!-- Attendance Chart -->
        <div class="bg-white p-6 rounded-xl shadow-sm mb-6">
          <h3 class="text-lg font-semibold mb-4">Attendance Trend</h3>
          <div class="h-64 chart-container">
            <apexchart
              v-if="attendanceData.length > 0"
              type="bar"
              height="100%"
              :options="chartOptions"
              :series="series"
            ></apexchart>
            <div v-else class="flex items-center justify-center text-gray-500">
              Loading attendance data...
            </div>
          </div>
        </div>

        <!-- Recent Notices -->
        <div class="bg-white p-6 rounded-xl shadow-sm">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Recent Notices</h3>
            <button class="text-blue-600 hover:text-blue-800 text-sm">
              View All <i class="fas fa-arrow-right ml-1"></i>
            </button>
          </div>
          <div class="space-y-4">
            <div v-for="notice in notices" :key="notice.id" class="notice-item p-4 hover:bg-gray-50 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-800">{{ notice.title }}</p>
                  <p class="text-sm text-gray-500">{{ notice.date }}</p>
                </div>
                <span :class="`badge ${notice.priority} px-3 py-1 rounded-full text-sm`">
                  {{ notice.category }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="lg:col-span-1">
        <!-- Quick Stats Grid -->
          <div class="stats-card bg-white p-6 rounded-xl shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-sm">Active Students</p>
              <p class="text-3xl font-bold text-gray-800">1,432</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
              <i class="fas fa-users text-blue-600 text-2xl"></i>
            </div>
          </div>
          <div class="mt-4 text-sm text-green-600">
            <i class="fas fa-arrow-up"></i> 12% from last month
          </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white p-6 rounded-xl shadow-sm">
          <h3 class="text-lg font-semibold mb-4">Upcoming Events</h3>
          <div class="space-y-4">
            <div v-for="event in events" :key="event.id" class="event-item flex items-center">
              <div class="mr-4 text-center">
                <p class="text-xl font-bold text-blue-600">{{ event.date.day }}</p>
                <p class="text-sm text-gray-500">{{ event.date.month }}</p>
              </div>
              <div>
                <p class="font-medium text-gray-800">{{ event.title }}</p>
                <p class="text-sm text-gray-500">{{ event.time }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { User } from '@/types';

interface Props {
    user: User;
    authToken: string;
    showEmail?: boolean;
}

const toast = useToast();
const props = withDefaults(defineProps<Props>(), {
    showEmail: true
});

const tenantId = usePage().props.auth.user.tenant_id;

const tenant = ref(usePage().props.tenant);

console.log('Tenant:', tenant.value);
// Attendance Chart Data
const attendanceData = reactive([]);

const userName = computed(() => props.user?.name || 'User');

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
        download: false
      }
    }
  },
  colors: ['#10B981', '#EF4444'],
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '55%',
      borderRadius: 4
    }
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
    type: 'datetime',
    categories: [],
    labels: {
      style: {
        colors: '#6B7280',
        fontSize: '12px'
      },
      formatter: function(value) {
        return value ? new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';
      }
    },
    tooltip: {
      formatter: function(value) {
        return value ? new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
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
  noData: {
    text: 'No attendance data available',
    align: 'center',
    verticalAlign: 'middle',
    offsetX: 0,
    offsetY: 0,
    style: {
      color: '#6B7280',
      fontSize: '14px',
      fontFamily: undefined
    }
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

const fetchAttendanceData = async () => {
  try {
    let allData = [];
    let currentPage = 1;
    let lastPage = 1;

    do {
      const response = await axios.get(`/api/attendances?page=${currentPage}`);
      console.log('API Response:', response.data);
      // Transform data to include present and absent fields
      const transformedData = response.data.data.map(item => ({
        date: item.date,
        present: item.status === 'present' ? 1 : 0,
        absent: item.status === 'absent' ? 1 : 0
      }));
      allData = [...allData, ...transformedData];
      lastPage = response.data.last_page;
      currentPage++;
    } while (currentPage <= lastPage);

    // Validate data structure
    if (!allData.every(d => d.date && d.present !== undefined && d.absent !== undefined)) {
      throw new Error('Invalid data structure received from API');
    }

    attendanceData.splice(0, attendanceData.length, ...allData);

    series.value = [{
      name: 'Present',
      data: allData.map(d => d.present)
    }, {
      name: 'Absent',
      data: allData.map(d => d.absent)
    }];

    chartOptions.xaxis.categories = allData.map(d => new Date(d.date).getTime());
  } catch (error) {
    console.error('Error fetching attendance data:', error);
    toast.error('Failed to fetch attendance data');
  }
};

const checkTokenValidity = async () => {
  const token = localStorage.getItem('authToken');
  if (!token) {
    console.warn('Authentication token not found in localStorage');
    return false;
  }

  try {
    const response = await axios.get('/api/tenants', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    console.log('Token validation successful');
    return true;
  } catch (error) {
    console.error('Token validation failed:', error);
    return false;
  }
};

onMounted(async () => {
  try {
    await checkTokenValidity();
    fetchAttendanceData();
  } catch (error) {
    console.error('Error occurred during onMounted:', error);
  }
});

// Sample data - replace with real data from API
const notices = ref([
  { id: 1, title: 'Exam Schedule Update', date: '2023-08-15', category: 'Academic', priority: 'bg-red-100 text-red-600' },
  // ... more notices
]);

const events = ref([
  { id: 1, title: 'Parent-Teacher Meeting', date: { day: '24', month: 'Aug' }, time: '2:00 PM' },
  // ... more events
]);
</script>

<style scoped>
.stats-card:hover {
  transform: translateY(-2px);
  transition: transform 0.2s ease;
}

.notice-item:hover {
  @apply cursor-pointer;
  transform: translateX(4px);
  transition: all 0.2s ease;
}

.action-btn:hover {
  transform: scale(1.05);
  transition: transform 0.2s ease;
}

.badge {
  @apply bg-gray-100 text-gray-600;
}

.chart-container {
  width: 100%;
  height: 100%;
  overflow: hidden;
}
</style>