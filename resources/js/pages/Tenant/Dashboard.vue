<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed, reactive, onMounted } from 'vue';
import type { DebounceSettings } from 'lodash';
import debounce from 'lodash/debounce';
import Pagination from '@/components/Pagination.vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import VueApexCharts from 'vue3-apexcharts';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ChatWidget from '@/components/ChatWidget.vue';

const props = defineProps<{
    tenant: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        logo_url: string | null;
        created_at: string;
    }
}>();

const toast = useToast();
const attendanceData = reactive([]);

// Chart configurations
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
    dataLabels: { enabled: false },
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
    grid: {
        borderColor: '#F3F4F6',
        strokeDashArray: 4
    }
});

const series = ref([{
    name: 'Present',
    data: []
}, {
    name: 'Absent',
    data: []
}]);

// Sample data for notices and events
const notices = ref([
    { 
        id: 1, 
        title: 'Exam Schedule Update', 
        date: '2024-03-15', 
        category: 'Academic', 
        priority: 'bg-red-100 text-red-600' 
    },
    { 
        id: 2, 
        title: 'Parent-Teacher Meeting', 
        date: '2024-03-20', 
        category: 'Event', 
        priority: 'bg-blue-100 text-blue-600' 
    },
    { 
        id: 3, 
        title: 'Sports Day Announcement', 
        date: '2024-03-25', 
        category: 'Event', 
        priority: 'bg-green-100 text-green-600' 
    }
]);

const events = ref([
    { id: 1, title: 'Parent-Teacher Meeting', date: { day: '20', month: 'Mar' }, time: '2:00 PM' },
    { id: 2, title: 'Sports Day', date: { day: '25', month: 'Mar' }, time: '9:00 AM' },
    { id: 3, title: 'Final Exams Begin', date: { day: '01', month: 'Apr' }, time: '8:30 AM' }
]);

const fetchAttendanceData = async () => {
    try {
        const response = await axios.get(`/api/attendances`);
        // Transform and update chart data
        series.value[0].data = response.data.present;
        series.value[1].data = response.data.absent;
        chartOptions.xaxis.categories = response.data.dates;
    } catch (error) {
        console.error('Error fetching attendance data:', error);
        toast.error('Failed to fetch attendance data');
    }
};

onMounted(() => {
    fetchAttendanceData();
    console.log('Tenant logo URL:', props.tenant.logo_url);
    console.log('Tenant data:', props.tenant);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="School Dashboard" />
        
        <!-- Header Slot Content -->
        <template #header>
            <div class="dashboard-header">
                <div class="header-content">
                    <h1 class="header-title">Welcome back, {{ props.tenant.name }}</h1>
                    <p class="header-subtitle">Here's your daily summary at a glance</p>
                </div>
                <div class="header-logo">
                    <img v-if="props.tenant.logo_url" :src="props.tenant.logo_url" alt="School Logo" class="logo-image">
                    <div v-else class="logo-placeholder">
                        <svg class="school-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 3L1 9L5 11.18V17.18L12 21L19 17.18V11.18L23 9L12 3ZM12 7.72L17.38 10.5L12 13.22L6.62 10.5L12 7.72ZM7 12.14L11 14.28V18.18L7 16.08V12.14ZM17 16.18L13 18.18V14.28L17 12.14V16.18Z" 
                                  class="fill-current text-blue-600"/>
                            <path d="M12 3L1 9L5 11.18V17.18L12 21L19 17.18V11.18L23 9L12 3ZM12 7.72L17.38 10.5L12 13.22L6.62 10.5L12 7.72ZM7 12.14L11 14.28V18.18L7 16.08V12.14ZM17 16.18L13 18.18V14.28L17 12.14V16.18Z" 
                                  class="fill-current text-blue-400 opacity-50" 
                                  transform="translate(2 2)"/>
                        </svg>
                    </div>
                </div>
            </div>
        </template>

        <!-- Main Content -->
        <div class="dashboard-container">
            <!-- Quick Actions Grid -->
            <div class="quick-actions-grid">
                <button v-for="(action, index) in quickActions" :key="index" 
                        class="action-card group relative overflow-hidden">
                    <div class="action-card__bg"></div>
                    <i :class="action.icon" class="action-icon"></i>
                    <div class="action-content">
                        <span class="action-text">{{ action.title }}</span>
                        <span class="action-badge">{{ action.stats }}</span>
                    </div>
                </button>
            </div>

            <!-- Main Content Grid -->
            <div class="main-content-grid">
                <!-- Attendance Chart Section -->
                <div class="chart-section">
                    <div class="chart-card bg-white rounded-2xl shadow-lg">
                        <div class="chart-header px-6 pt-6 pb-4 border-b border-gray-100">
                            <h3 class="chart-title text-lg font-semibold text-gray-900">Attendance Overview</h3>
                            <div class="chart-legend flex gap-4 mt-2">
                                <div class="legend-item flex items-center gap-2 text-sm text-gray-600">
                                    <span class="present-dot w-3 h-3 rounded-full bg-emerald-500"></span>
                                    Present Students
                                </div>
                                <div class="legend-item flex items-center gap-2 text-sm text-gray-600">
                                    <span class="absent-dot w-3 h-3 rounded-full bg-red-500"></span>
                                    Absent Students
                                </div>
                            </div>
                        </div>
                        <div class="chart-container p-6">
                            <VueApexCharts
                                type="bar"
                                height="100%"
                                :options="chartOptions"
                                :series="series"
                            />
                        </div>
                    </div>
                </div>

                <!-- Notices Section -->
                <div class="notices-section">
                    <div class="notices-card bg-white rounded-2xl shadow-lg">
                        <div class="notices-header px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="notices-title text-lg font-semibold text-gray-900">Recent Announcements</h3>
                            <button class="view-all-btn text-blue-600 hover:text-blue-800 flex items-center text-sm">
                                View All <i class="fas fa-chevron-right ml-2 text-xs"></i>
                            </button>
                        </div>
                        <div class="notices-list p-6 grid gap-4">
                            <div v-for="notice in notices" :key="notice.id" 
                                 class="notice-item p-4 rounded-lg border-l-4 transition-all duration-200 hover:shadow-sm"
                                 :class="[notice.priority, notice.category === 'Academic' ? 'border-blue-500' : 'border-emerald-500']">
                                <div class="flex items-start gap-4">
                                    <i :class="notice.icon" class="text-2xl text-gray-500 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="notice-title font-medium text-gray-800">{{ notice.title }}</p>
                                        <div class="mt-2 flex items-center gap-3 text-sm">
                                            <span class="notice-date text-gray-500">{{ notice.date }}</span>
                                            <span class="notice-category px-2 py-1 rounded-full text-xs font-medium bg-gray-100">
                                                {{ notice.category }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats & Events Sidebar -->
                <div class="sidebar-section space-y-6">
                    <!-- Active Students Card -->
                    <div class="stats-card bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="stats-label text-sm opacity-90">Active Students</p>
                                <p class="stats-value text-3xl font-bold">1,432</p>
                            </div>
                            <i class="fas fa-users-class text-4xl opacity-90"></i>
                        </div>
                        <div class="stats-trend flex items-center gap-2 text-sm">
                            <i class="fas fa-arrow-up text-green-400"></i>
                            <span class="opacity-90">12% from last month</span>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="events-card bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="events-title text-lg font-semibold text-gray-900 mb-4">Upcoming Schedule</h3>
                        <div class="events-list space-y-4">
                            <div v-for="event in events" :key="event.id" 
                                 class="event-item p-4 rounded-lg bg-gray-50 hover:bg-white transition-all duration-200">
                                <div class="flex items-center gap-4">
                                    <div class="event-date shrink-0 w-14 text-center">
                                        <span class="date-day block text-xl font-bold text-blue-600">{{ event.date.day }}</span>
                                        <span class="date-month text-xs text-gray-500 uppercase">{{ event.date.month }}</span>
                                    </div>
                                    <div class="event-details flex-1">
                                        <p class="event-name font-medium text-gray-800">{{ event.title }}</p>
                                        <p class="event-time text-sm text-gray-500 mt-1">
                                            <i class="fas fa-clock mr-2 opacity-70"></i>{{ event.time }}
                                        </p>
                                    </div>
                                    <button class="event-reminder text-gray-400 hover:text-blue-600 p-2 rounded-full">
                                        <i class="fas fa-bell"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ChatWidget />
    </AppLayout>
</template>

<style scoped>
.dashboard-container {
    @apply px-4 py-8 sm:px-6 lg:px-8 mx-auto;
    max-width: 95rem; /* 1520px */
}

.dashboard-header {
    @apply flex items-center justify-between px-6 pb-6;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.header-content {
    @apply space-y-2;
}

.header-title {
    @apply text-2xl font-semibold text-gray-900;
}

.header-subtitle {
    @apply text-base text-gray-500;
}

.header-logo {
    @apply w-16 h-16 rounded-xl bg-white p-2 shadow-sm flex items-center justify-center;
}

.logo-image {
    @apply w-full h-full object-contain rounded-lg;
}

.logo-placeholder {
    @apply w-full h-full rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center p-2;
}

.school-icon {
    @apply w-10 h-10;
}

.quick-actions-grid {
    @apply grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-6;
}

.action-card {
    @apply p-4;
    min-height: 100px;
}

.action-card__bg {
    @apply absolute inset-0 bg-gradient-to-br opacity-90 transition-opacity duration-300;
}

.action-card:hover .action-card__bg {
    @apply opacity-100;
}

.action-icon {
    @apply text-2xl mb-2;
}

.action-content {
    @apply relative z-10;
}

.action-text {
    @apply text-base;
}

.action-badge {
    @apply text-sm bg-white bg-opacity-20 px-3 py-1 rounded-full;
}

.main-content-grid {
    @apply grid grid-cols-1 lg:grid-cols-12 gap-4;
}

.chart-section {
    @apply lg:col-span-8 xl:col-span-9;
}

.chart-card {
    @apply bg-white rounded-2xl shadow-lg;
}

.chart-header {
    @apply px-4 pt-4 pb-3;
}

.chart-title {
    @apply text-lg font-semibold text-gray-900;
}

.chart-legend {
    @apply flex gap-4 mt-2;
}

.legend-item {
    @apply flex items-center gap-2 text-sm text-gray-600;
}

.present-dot {
    @apply w-3 h-3 rounded-full bg-emerald-500;
}

.absent-dot {
    @apply w-3 h-3 rounded-full bg-red-500;
}

.chart-container {
    @apply p-4;
    min-height: 400px;
    @screen xl {
        min-height: 450px;
    }
}

.notices-section {
    @apply lg:col-span-4 xl:col-span-3;
}

.notices-card {
    @apply h-auto;
}

.notices-header {
    @apply px-4 pt-4 pb-3;
}

.notices-title {
    @apply text-lg font-semibold text-gray-900;
}

.view-all-btn {
    @apply text-blue-600 hover:text-blue-800 flex items-center text-sm;
}

.notices-list {
    @apply grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-1 gap-3 p-4;
}

.notice-item {
    @apply p-3;
}

.notice-content {
    @apply flex items-center gap-4;
}

.notice-icon {
    @apply text-2xl w-10 h-10 rounded-lg flex items-center justify-center;
}

.notice-title {
    @apply font-medium text-gray-800;
}

.notice-date {
    @apply text-sm text-gray-500;
}

.notice-category {
    @apply text-xs font-medium px-2 py-1 rounded-full mt-2 inline-block;
}

.sidebar-section {
    @apply lg:col-span-12 xl:col-span-12 2xl:col-span-4;
}

.stats-card {
    @apply p-4;
}

.gradient-blue {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.stats-content {
    @apply flex items-center justify-between mb-4;
}

.stats-label {
    @apply text-sm opacity-90;
}

.stats-value {
    @apply text-2xl;
}

.stats-icon {
    @apply text-4xl opacity-90;
}

.stats-trend {
    @apply flex items-center gap-2 text-sm;
}

.events-card {
    @apply h-auto;
}

.events-title {
    @apply text-lg font-semibold text-gray-900 mb-4;
}

.events-list {
    @apply grid grid-cols-1 gap-2;
}

.event-item {
    @apply p-2;
}

.event-date {
    @apply w-12;
}

.date-day {
    @apply text-lg;
}

.date-month {
    @apply text-xs text-gray-500 uppercase;
}

.event-details {
    @apply flex-1;
}

.event-name {
    @apply font-medium text-gray-800;
}

.event-time {
    @apply text-sm text-gray-500;
}

.event-reminder {
    @apply text-gray-400 hover:text-blue-600 p-2 rounded-full;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        @apply flex-col items-start gap-4;
    }
    
    .header-logo {
        @apply w-12 h-12;
    }
    
    .chart-container {
        @apply min-h-[300px];
    }
    
    .action-card {
        @apply p-4;
    }
    
    .action-icon {
        @apply text-2xl;
    }
}

/* Larger header on big screens */
@screen xl {
    .dashboard-header {
        @apply px-8 pb-8;
    }
    
    .header-title {
        @apply text-3xl;
    }
    
    .header-subtitle {
        @apply text-lg;
    }
    
    .header-logo {
        @apply w-20 h-20;
    }
}

/* Ultra-wide screens adjustment */
@media (min-width: 1920px) {
    .dashboard-container {
        max-width: 120rem; /* 1920px */
    }
    
    .main-content-grid {
        @apply grid-cols-12;
        gap: 2rem;
    }
    
    .chart-section {
        @apply col-span-10;
    }
    
    .notices-section {
        @apply col-span-2;
    }
    
    .sidebar-section {
        @apply col-span-3;
    }
    
    .chart-container {
        min-height: 500px;
    }
    
    .notices-list {
        @apply grid-cols-2;
    }
}

/* 4K screens adjustment */
@media (min-width: 2560px) {
    .dashboard-container {
        max-width: 150rem; /* 2400px */
    }
    
    .main-content-grid {
        gap: 4rem;
    }
    
    .chart-container {
        min-height: 800px;
    }
}
</style> 
