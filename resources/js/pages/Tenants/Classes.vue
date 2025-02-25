<template>
    <TenantLayout :breadcrumbs="breadcrumbs">
      <Head title="Classes" />
      <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="sm:flex sm:items-center mb-8">
          <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold text-gray-900">Classes</h1>
            <p class="mt-2 text-sm text-gray-600">Manage classes for {{ tenant.name }}</p>
          </div>
          <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              Create New Class
            </button>
          </div>
        </div>
  
        <!-- Filters and Search -->
        <div class="mb-8 flex flex-col sm:flex-row gap-4">
          <input
            type="text"
            placeholder="Search classes..."
            class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          />
          <select
            class="w-full sm:w-48 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Filter by Grade</option>
            <option value="grade-1">Grade 1</option>
            <option value="grade-2">Grade 2</option>
            <option value="grade-3">Grade 3</option>
          </select>
          <select
            class="w-full sm:w-48 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Filter by Subject</option>
            <option value="math">Mathematics</option>
            <option value="science">Science</option>
            <option value="english">English</option>
          </select>
          <button
            class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
          >
            Clear Filters
          </button>
        </div>
  
        <!-- Classes Table -->
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Class Name
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Grade
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Subject
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Teacher
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Students
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="classItem in classes" :key="classItem.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ classItem.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ classItem.grade }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ classItem.subject }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ classItem.teacher }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ classItem.students }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    class="text-indigo-600 hover:text-indigo-900 mr-4"
                    @click="editClass(classItem)"
                  >
                    Edit
                  </button>
                  <button
                    class="text-red-600 hover:text-red-900"
                    @click="deleteClass(classItem)"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <!-- Pagination -->
        <div class="mt-8 flex justify-between items-center">
          <span class="text-sm text-gray-700">
            Showing 1 to 10 of 50 classes
          </span>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm">
            <button
              class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              1
            </button>
            <button
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              2
            </button>
            <button
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              3
            </button>
            <button
              class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Next
            </button>
          </nav>
        </div>
      </div>
    </TenantLayout>
  </template>
  
  <script setup lang="ts">
  import TenantLayout from '@/layouts/TenantLayout.vue';
  import { type BreadcrumbItem } from '@/types';
  import { Head } from '@inertiajs/vue3';
  import { ref } from 'vue';
  
  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Classes',
      href: '/classes',
    },
  ];
  
  defineProps<{
    tenant: any;
  }>();
  
  // Dummy data for classes
  const classes = ref([
    {
      id: 1,
      name: 'Class 1A',
      grade: 'Grade 1',
      subject: 'Mathematics',
      teacher: 'John Doe',
      students: 25,
    },
    {
      id: 2,
      name: 'Class 2B',
      grade: 'Grade 2',
      subject: 'Science',
      teacher: 'Jane Smith',
      students: 30,
    },
    {
      id: 3,
      name: 'Class 3C',
      grade: 'Grade 3',
      subject: 'English',
      teacher: 'Alice Johnson',
      students: 28,
    },
  ]);
  
  // Functions for actions
  const editClass = (classItem: any) => {
    console.log('Edit class:', classItem);
    // Add logic to open edit modal or navigate to edit page
  };
  
  const deleteClass = (classItem: any) => {
    console.log('Delete class:', classItem);
    // Add logic to confirm and delete class
  };
  </script>