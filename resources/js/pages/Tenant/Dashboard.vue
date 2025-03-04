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
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="School Dashboard" />
        <div class="py-12">
                <div class="tenant-admin-dashboard">
                    <header class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Dashboard Overview</h1>
                        <p class="text-gray-500 mt-2">Welcome back, {{ props.tenant.name }}. Here's your daily summary</p>
                    </header>

                    <!-- Quick Actions -->
                    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
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
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Charts Section -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Attendance Chart -->
                            <div class="bg-white p-6 rounded-xl shadow-sm">
                                <h3 class="text-lg font-semibold mb-4">Attendance Trend</h3>
                                <div class="h-64">
                                    <VueApexCharts
                                        type="bar"
                                        height="100%"
                                        :options="chartOptions"
                                        :series="series"
                                    />
                                </div>
                            </div>

                            <!-- Recent Notices -->
                            <div class="bg-white p-6 rounded-xl shadow-sm">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Recent Notices</h3>
                                    <button class="text-blue-600 hover:text-blue-800 text-sm">
                                        View All
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    <div v-for="notice in notices" :key="notice.id" 
                                        class="notice-item p-4 hover:bg-gray-50 rounded-lg">
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
                        <div class="space-y-6">
                            <!-- Quick Stats -->
                            <div class="bg-white p-6 rounded-xl shadow-sm">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <p class="text-gray-500 text-sm">Active Students</p>
                                        <p class="text-3xl font-bold text-gray-800">1,432</p>
                                    </div>
                                    <div class="p-3 bg-blue-100 rounded-lg">
                                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                                    </div>
                                </div>
                                <div class="text-sm text-green-600">
                                    <i class="fas fa-arrow-up"></i> 12% from last month
                                </div>
                            </div>

                            <!-- Upcoming Events -->
                            <div class="bg-white p-6 rounded-xl shadow-sm">
                                <h3 class="text-lg font-semibold mb-4">Upcoming Events</h3>
                                <div class="space-y-4">
                                    <div v-for="event in events" :key="event.id" 
                                        class="event-item flex items-center p-3 hover:bg-gray-50 rounded-lg">
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
            
        </div>
    </AppLayout>
</template>

<style scoped>
.action-btn:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.notice-item:hover {
    transform: translateX(4px);
    transition: all 0.2s ease;
    cursor: pointer;
}

.event-item:hover {
    cursor: pointer;
}
</style> 
