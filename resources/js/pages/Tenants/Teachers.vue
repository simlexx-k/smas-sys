<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TeacherForm from '@/components/Teachers/TeacherForm.vue';
import { ref, computed } from 'vue';
import { Search, Download, PlusCircle, ChevronFirst, ChevronLeft, ChevronRight, ChevronLast, X } from 'lucide-vue-next';
import jsPDF from 'jspdf';
import 'jspdf-autotable';

interface Teacher {
    id: number;
    name: string;
    email: string;
    status: string;
    employee_id: string;
    department: string;
    subjects: string[];
    joining_date: string;
}

interface Props {
    tenant: {
        id: number;
        name: string;
    };
    teachers: Teacher[];
    departments: string[];
    subjects: Array<{
        id: number;
        name: string;
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    departments: () => [],
    subjects: () => []
});

const showForm = ref(false);
const showExportMenu = ref(false);

const breadcrumbs = [
    {
        title: 'Teachers',
        href: '/teachers',
    },
];

// Filters
const searchQuery = ref('');
const selectedDepartment = ref('All');
const statusFilter = ref('All');

// Pagination
const itemsPerPage = ref(10);
const currentPage = ref(1);

// Computed properties for filtered and paginated data
const filteredTeachers = computed(() => {
    return props.teachers.filter(teacher => {
        const matchesSearch = teacher.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                          teacher.email.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesDepartment = selectedDepartment.value === 'All' || teacher.department === selectedDepartment.value;
        const matchesStatus = statusFilter.value === 'All' || teacher.status === statusFilter.value;
        return matchesSearch && matchesDepartment && matchesStatus;
    });
});

const paginatedTeachers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredTeachers.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredTeachers.value.length / itemsPerPage.value);
});

// Export functionality
const exportToCSV = () => {
    const headers = ['ID', 'Name', 'Email', 'Department', 'Subjects', 'Status', 'Joining Date'];
    const rows = filteredTeachers.value.map(teacher => [
        teacher.id,
        teacher.name,
        teacher.email,
        teacher.department,
        teacher.subjects.join(', '),
        teacher.status,
        teacher.joining_date
    ]);
    const csvContent = [headers, ...rows].map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'teachers.csv';
    link.click();
};

const exportToPDF = () => {
    const doc = new jsPDF();
    
    // Add title
    doc.setFontSize(18);
    doc.text(`${props.tenant.name} - Teacher List`, 14, 22);
    
    // Prepare data for the table
    const headers = [['ID', 'Name', 'Department', 'Status', 'Joining Date']];
    const data = filteredTeachers.value.map(teacher => [
        teacher.id,
        teacher.name,
        teacher.department,
        teacher.status,
        teacher.joining_date
    ]);
    
    // Add table
    doc.autoTable({
        head: headers,
        body: data,
        startY: 30,
        theme: 'striped',
        styles: {
            fontSize: 10,
            cellPadding: 2
        },
        headStyles: {
            fillColor: [41, 128, 185],
            textColor: 255
        }
    });
    
    // Save the PDF
    doc.save('teachers.pdf');
};

// Department options for filter
const departmentOptions = computed(() => {
    return ['All', ...(props.departments || [])];
});

// Status options for filter
const statusOptions = ['All', 'Active', 'Inactive'];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Teachers" />
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Teacher Management</h1>
                <div class="flex gap-2">
                    <button @click="showForm = true" class="flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        <PlusCircle class="w-5 h-5" />
                        Add New Teacher
                    </button>
                    <div class="relative">
                        <button @click="showExportMenu = !showExportMenu" class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <Download class="w-5 h-5" />
                            Export
                        </button>
                        <div v-if="showExportMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10">
                            <button @click="exportToCSV" class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Export to CSV
                            </button>
                            <button @click="exportToPDF" class="block w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Export to PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name or email"
                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <select v-model="selectedDepartment" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option v-for="option in departmentOptions" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
                <select v-model="statusFilter" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option v-for="option in statusOptions" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
                <div class="flex gap-2">
                </div>
            </div>

            <!-- Teacher Table -->
            <div class="overflow-x-auto rounded-lg shadow-sm">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left text-sm font-semibold text-gray-600">ID</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-600">Name</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-600">Department</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-600">Subjects</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-600">Email</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-600">Status</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-600">Joining Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="teacher in paginatedTeachers" :key="teacher.id" class="hover:bg-gray-50 transition-colors">
                            <td class="p-3 text-sm text-gray-700">{{ teacher.id }}</td>
                            <td class="p-3 text-sm text-gray-700">{{ teacher.name }}</td>
                            <td class="p-3 text-sm text-gray-700">{{ teacher.department }}</td>
                            <td class="p-3 text-sm text-gray-700">{{ teacher.subjects.join(', ') }}</td>
                            <td class="p-3 text-sm text-gray-700">{{ teacher.email }}</td>
                            <td class="p-3">
                                <span :class="{
                                    'px-2 py-1 text-xs font-medium rounded-full': true,
                                    'bg-green-100 text-green-700': teacher.status === 'Active',
                                    'bg-red-100 text-red-700': teacher.status === 'Inactive'
                                }">
                                    {{ teacher.status }}
                                </span>
                            </td>
                            <td class="p-3 text-sm text-gray-700">{{ teacher.joining_date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Showing {{ paginatedTeachers.length }} of {{ filteredTeachers.length }} teachers
                </div>
                <div class="flex gap-2">
                    <button
                        @click="currentPage = 1"
                        :disabled="currentPage === 1"
                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronFirst class="w-5 h-5" />
                    </button>
                    <button
                        @click="currentPage--"
                        :disabled="currentPage === 1"
                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                    <span class="px-4 py-2 text-sm text-gray-600">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <button
                        @click="currentPage++"
                        :disabled="currentPage === totalPages"
                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronRight class="w-5 h-5" />
                    </button>
                    <button
                        @click="currentPage = totalPages"
                        :disabled="currentPage === totalPages"
                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronLast class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Teacher Form Modal -->
            <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                <div class="bg-white p-8 rounded-lg w-full max-w-2xl relative">
                    <button @click="showForm = false" class="absolute right-6 top-6 p-2 text-gray-500 hover:text-gray-700">
                        <X class="w-6 h-6" />
                    </button>
                    <h2 class="text-xl font-bold mb-6">Add New Teacher</h2>
                    <TeacherForm 
                        :departments="departments"
                        :subjects="subjects"
                        @close="showForm = false" 
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>