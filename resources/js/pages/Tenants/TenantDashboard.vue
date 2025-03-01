<script setup lang="ts">
import TenantLayout from '@/layouts/AppLayout.vue';
import TenantSidebar from '@/components/TenantSidebar.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import VueApexCharts from 'vue3-apexcharts';
import { router } from '@inertiajs/vue3';
import { onMounted, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'School Dashboard',
        href: '/',
    },
];

defineProps<{
    tenant: any;
}>();

const hasTenant = computed(() => !!props.tenant);
const tenantExists = computed(() => !!props.tenant);

// Chart configurations with improved aesthetics
const barChartOptions = {
    chart: {
        type: 'bar',
        height: '100%',
        toolbar: {
            show: false,
        },
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded',
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent'],
    },
    xaxis: {
        categories: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
            },
        },
    },
    yaxis: {
        title: {
            text: 'Students',
            style: {
                color: '#6b7280',
                fontSize: '12px',
            },
        },
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
            },
        },
    },
    fill: {
        opacity: 1,
    },
    colors: ['#4f46e5'],
    grid: {
        borderColor: '#e5e7eb',
    },
};

const barChartSeries = [
    {
        name: 'Students',
        data: [40, 20, 12, 39, 10, 40, 39],
    },
];

const lineChartOptions = {
    chart: {
        type: 'line',
        height: '100%',
        toolbar: {
            show: false,
        },
    },
    stroke: {
        curve: 'smooth',
        width: 2,
    },
    xaxis: {
        categories: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
            },
        },
    },
    yaxis: {
        title: {
            text: 'Attendance',
            style: {
                color: '#6b7280',
                fontSize: '12px',
            },
        },
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
            },
        },
    },
    colors: ['#10b981'],
    grid: {
        borderColor: '#e5e7eb',
    },
};

const lineChartSeries = [
    {
        name: 'Attendance',
        data: [65, 59, 80, 81, 56, 55, 40],
    },
];

const pieChartOptions = {
    chart: {
        type: 'pie',
        height: '100%',
        toolbar: {
            show: false,
        },
    },
    labels: ['Math', 'Science', 'English', 'History'],
    colors: ['#3b82f6', '#ef4444', '#f59e0b', '#10b981'],
    responsive: [
        {
            breakpoint: 480,
            options: {
                chart: {
                    width: 200,
                },
                legend: {
                    position: 'bottom',
                },
            },
        },
    ],
    dataLabels: {
        style: {
            fontSize: '12px',
            colors: ['#fff'],
        },
    },
};

const pieChartSeries = [30, 20, 25, 25];

const checkAuth = () => {
  if (!route().current('login') && !$page.props.auth.user) {
    router.visit(route('login', { redirect: route().current() }));
  }
};

onMounted(() => {
  checkAuth();
});
</script>

<script lang="ts">
export default {
  mounted() {
    if (this.$page.props.auth.user.role !== 'tenant' && this.$page.url !== '/') {
      this.$inertia.visit('/');
    }
  }
}
</script>

<template>
    <Head title="Tenant Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs" :sidebar="AppSidebar">
        <div v-if="!tenantExists" class="flex flex-col items-center justify-center gap-4">
            <p class="text-xl text-gray-900 dark:text-gray-100">Tenant does not exist yet</p>
            <Link :href="route('tenants.create')" class="rounded-md bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                Sign Up
            </Link>
        </div>
        <div v-else class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <h1 v-if="hasTenant" class="text-3xl font-bold text-gray-900 dark:text-gray-100">Welcome, {{ tenant.name }}</h1>
            <div class="grid auto-rows-min gap-6 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <VueApexCharts type="bar" :options="barChartOptions" :series="barChartSeries" />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <VueApexCharts type="line" :options="lineChartOptions" :series="lineChartSeries" />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <VueApexCharts type="pie" :options="pieChartOptions" :series="pieChartSeries" />
                </div>
            </div>
            <div class="relative flex-1 rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Quick Stats</h2>
                    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div class="rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 p-6 shadow-lg transition-transform hover:scale-105 dark:from-blue-900 dark:to-blue-800">
                            <p class="text-sm font-medium text-blue-700 dark:text-blue-200">Total Students</p>
                            <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">1,234</p>
                        </div>
                        <div class="rounded-xl bg-gradient-to-r from-green-50 to-green-100 p-6 shadow-lg transition-transform hover:scale-105 dark:from-green-900 dark:to-green-800">
                            <p class="text-sm font-medium text-green-700 dark:text-green-200">Attendance Rate</p>
                            <p class="text-3xl font-bold text-green-900 dark:text-green-100">95%</p>
                        </div>
                        <div class="rounded-xl bg-gradient-to-r from-yellow-50 to-yellow-100 p-6 shadow-lg transition-transform hover:scale-105 dark:from-yellow-900 dark:to-yellow-800">
                            <p class="text-sm font-medium text-yellow-700 dark:text-yellow-200">Pending Assignments</p>
                            <p class="text-3xl font-bold text-yellow-900 dark:text-yellow-100">23</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>