<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Academics" />
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="sm:flex sm:items-center mb-8">
        <div class="sm:flex-auto">
          <h1 class="text-2xl font-bold text-gray-900">Academics</h1>
          <p class="mt-2 text-sm text-gray-600">Manage academic settings for {{ tenant.name }}</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          >
            Add New Course
          </button>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="mb-8 flex flex-col sm:flex-row gap-4">
        <input
          type="text"
          placeholder="Search courses..."
          class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
        />
        <select
          class="w-full sm:w-48 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option value="">Filter by Department</option>
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

      <!-- Courses Table -->
      <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Course Name
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Department
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Instructor
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Students Enrolled
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="course in courses" :key="course.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ course.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ course.department }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ course.instructor }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ course.studentsEnrolled }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  class="text-indigo-600 hover:text-indigo-900 mr-4"
                  @click="editCourse(course)"
                >
                  Edit
                </button>
                <button
                  class="text-red-600 hover:text-red-900"
                  @click="deleteCourse(course)"
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
          Showing 1 to 10 of 50 courses
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
  </AppLayout>
</template>

<script setup lang="ts">
import TenantLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Academics',
    href: '/academics',
  },
];

defineProps<{
  tenant: any;
}>();

// Dummy data for courses
const courses = ref([
  {
    id: 1,
    name: 'Mathematics 101',
    department: 'Mathematics',
    instructor: 'John Doe',
    studentsEnrolled: 120,
  },
  {
    id: 2,
    name: 'Physics 201',
    department: 'Science',
    instructor: 'Jane Smith',
    studentsEnrolled: 90,
  },
  {
    id: 3,
    name: 'English Literature',
    department: 'English',
    instructor: 'Alice Johnson',
    studentsEnrolled: 75,
  },
]);

// Functions for actions
const editCourse = (course: any) => {
  console.log('Edit course:', course);
  // Add logic to open edit modal or navigate to edit page
};

const deleteCourse = (course: any) => {
  console.log('Delete course:', course);
  // Add logic to confirm and delete course
};
</script>