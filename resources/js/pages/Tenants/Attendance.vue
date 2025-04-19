<template>
    <AppLayout :breadcrumbs="breadcrumbs">
      <Head title="Daily Attendance" />
      <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="sm:flex sm:items-center mb-8">
          <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold text-gray-900">Daily Attendance</h1>
            <p class="mt-2 text-sm text-gray-600">Track and manage daily attendance records for {{ tenant.name }}</p>
          </div>
          <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              @click="openNewAttendance"
            >
              Record Attendance
            </button>
          </div>
        </div>
  
        <!-- Filters and Search -->
        <div class="mb-8 flex flex-col sm:flex-row gap-4">
          <input
            type="date"
            class="w-full sm:w-48 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            v-model="filters.selectedDate"
          />
          <select
            class="w-full sm:w-48 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            v-model="filters.dayOfWeek"
          >
            <option value="">All Days</option>
            <option v-for="day in daysOfWeek" :key="day" :value="day">{{ day }}</option>
          </select>
          <button
            class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
            @click="clearFilters"
          >
            Clear Filters
          </button>
        </div>
  
        <!-- Attendance Table -->
        <div v-if="loading.students || loading.classes || loading.attendances" class="text-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto mb-4"></div>
          <p class="text-gray-600">Loading attendance data...</p>
        </div>

        <div v-else-if="error.students || error.classes || error.attendances" class="text-center py-8">
          <div class="text-red-600 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-red-600">Error loading data:</p>
          <p class="text-red-600 text-sm">{{ error.students || error.classes || error.attendances }}</p>
          <button
            class="mt-4 px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
            @click="onMounted()"
          >
            Retry
          </button>
        </div>

        <div v-else class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Day
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Student
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="record in paginatedRecords" :key="record.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(record.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ getDayOfWeek(record.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <template v-if="record.student">
                    {{ record.student.first_name }} {{ record.student.last_name }}
                  </template>
                  <template v-else>
                    <span class="text-red-500">Student data missing</span>
                  </template>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <span 
                    :class="{
                      'text-green-600': record.status === 'present',
                      'text-red-600': record.status === 'absent',
                      'text-yellow-600': record.status === 'late'
                    }"
                  >
                    {{ record.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-4">
                    <button
                      class="text-indigo-600 hover:text-indigo-900"
                      @click="openEditModal(record)"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                      </svg>
                    </button>
                    
                    <button
                      class="text-red-600 hover:text-red-900"
                      @click="openDeleteModal(record)"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <!-- Empty State -->
        <div v-if="paginatedRecords.length === 0" class="mt-8 text-center text-gray-500">
          No attendance records found for selected filters
        </div>
  
        <!-- Pagination -->
        <div v-else class="mt-8 flex justify-between items-center">
          <span class="text-sm text-gray-700">
            Showing {{ paginationStart }} to {{ paginationEnd }} of {{ paginatedRecords.length }} records
          </span>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm">
            <button
              class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="previousPage"
            >
              Previous
            </button>
            <button
              v-for="page in totalPages" :key="page"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
              :class="{ 'bg-indigo-50 border-indigo-500 text-indigo-600': currentPage === page }"
              @click="currentPage = page"
            >
              {{ page }}
            </button>
            <button
              class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="nextPage"
            >
              Next
            </button>
          </nav>
        </div>
      </div>

      <!-- New Attendance Modal -->
      <div v-if="showAttendanceForm" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
              <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Record New Attendance</h3>
                    <div class="mt-2">
                      <form @submit.prevent="submitAttendance">
                        <div class="mb-4">
                          <label class="block text-sm font-medium text-gray-700" for="date">Date</label>
                          <input
                            type="date"
                            id="date"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            v-model="newAttendance.date"
                          />
                        </div>
                        <div class="mb-4">
                          <label class="block text-sm font-medium text-gray-700" for="classId">Class</label>
                          <div v-if="loading.classes" class="text-gray-500 text-sm">
                            Loading classes...
                          </div>
                          <select
                            id="classId"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            v-model="newAttendance.classId"
                            :disabled="loading.classes"
                          >
                            <option value="" disabled>Select a class</option>
                            <option v-for="classItem in safeClasses" :key="classItem.id" :value="classItem.id">
                              {{ classItem.name }}
                            </option>
                          </select>
                        </div>
                        <div class="mb-4">
                          <label class="block text-sm font-medium text-gray-700" for="studentId">Student</label>
                          <div v-if="loading.students" class="text-gray-500 text-sm">
                            Loading students...
                          </div>
                          <select
                            id="studentId"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            v-model="newAttendance.studentId"
                          >
                            <option v-for="student in students.filter(s => s)" :key="student.id" :value="student.id">{{ student.first_name + ' ' + student.last_name }}</option>
                          </select>
                        </div>
                        <div class="mb-4">
                          <label class="block text-sm font-medium text-gray-700" for="status">Status</label>
                          <select
                            id="status"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            v-model="newAttendance.status"
                          >
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                          </select>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                          <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                          >
                            Record Attendance
                          </button>
                          <button
                            type="button"
                            class="mt-3 inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3"
                            @click="showAttendanceForm = false"
                          >
                            Cancel
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="!safeClasses.length" class="text-gray-500">No classes available</div>
      <!-- Computed Values Table -->
      <div class="mt-8 overflow-hidden shadow-lg rounded-xl border border-gray-100">
        <div class="px-6 py-4 bg-indigo-50 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-indigo-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
            </svg>
            Attendance Summary
          </h2>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
                  Present (%)
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Absent
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <template v-if="filteredAttendance.length > 0">
                <tr 
                  v-for="record in filteredAttendance" 
                  :key="record.id"
                  class="hover:bg-gray-50 transition-colors duration-150"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                    <span 
                      class="w-2 h-2 rounded-full mr-3"
                      :class="{
                        'bg-green-500': attendancePercentage(record.present, record.totalStudents) >= 70,
                        'bg-yellow-500': attendancePercentage(record.present, record.totalStudents) >= 50 && attendancePercentage(record.present, record.totalStudents) < 70,
                        'bg-red-500': attendancePercentage(record.present, record.totalStudents) < 50
                      }"
                    ></span>
                    {{ formatDate(record.date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                    <span class="font-medium text-gray-900">
                      {{ record.totalStudents || 0 }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                    <div class="flex items-center justify-end">
                      <div class="w-24 mr-3">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                          <div 
                            class="h-full bg-green-500 rounded-full transition-all duration-500"
                            :style="{ width: `${attendancePercentage(record.present, record.totalStudents)}%` }"
                          ></div>
                        </div>
                      </div>
                      <span class="text-green-600 font-medium">
                        {{ record.present || 0 }}
                        <span class="text-gray-500 text-xs">
                          ({{ attendancePercentage(record.present, record.totalStudents) }}%)
                        </span>
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                    <div class="flex items-center justify-end">
                      <span class="text-red-600 font-medium mr-2">
                        {{ record.absent || 0 }}
                      </span>
                      <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </div>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                      <p class="text-sm">No attendance records found</p>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Edit Modal -->
      <div v-if="showEditModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
              <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-semibold leading-6 text-gray-900">Edit Attendance Record</h3>
                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input
                      type="date"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      v-model="selectedRecord.date"
                      required
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Class</label>
                    <select
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      v-model="selectedRecord.classId"
                      required
                      @change="selectedRecord.studentId = ''"
                    >
                      <option value="" disabled>Select a class</option>
                      <option v-for="classItem in safeClasses" :key="classItem.id" :value="classItem.id">
                        {{ classItem.name }}
                      </option>
                    </select>
                  </div>

                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Student</label>
                    <div v-if="loading.students" class="text-gray-500 text-sm mt-1">
                      Loading students...
                    </div>
                    <select
                      v-else
                      id="studentId"
                      class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      v-model="selectedRecord.studentId"
                      :disabled="loading.students"
                      required
                    >
                      <option value="" disabled>Select a student</option>
                      <option 
                        v-for="student in students.filter(s => s.school_class_id == selectedRecord.classId)" 
                        :key="student.id" 
                        :value="student.id"
                      >
                        {{ student.first_name }} {{ student.last_name }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      v-model="selectedRecord.status"
                      required
                    >
                      <option value="present">Present</option>
                      <option value="absent">Absent</option>
                      <option value="late">Late</option>
                    </select>
                  </div>

                  <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button
                      type="submit"
                      class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto"
                      :disabled="isProcessing"
                    >
                      <span v-if="!isProcessing">Save Changes</span>
                      <span v-else>Saving...</span>
                    </button>
                    <button
                      type="button"
                      class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                      @click="showEditModal = false"
                      :disabled="isProcessing"
                    >
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
              <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Delete attendance record</h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        Are you sure you want to delete the attendance record for 
                        <span class="font-medium">{{ selectedRecord?.student?.first_name }} {{ selectedRecord?.student?.last_name }}</span>
                        on {{ formatDate(selectedRecord?.date) }}?
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                  @click="confirmDelete"
                  :disabled="isProcessing"
                >
                  <span v-if="!isProcessing">Delete</span>
                  <span v-else>Deleting...</span>
                </button>
                <button
                  type="button"
                  class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                  @click="showDeleteModal = false"
                  :disabled="isProcessing"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
</template>
  
  <script setup lang="ts">
  import AppLayout from '@/layouts/AppLayout.vue';
  import { Head } from '@inertiajs/vue3';
  import { ref, computed, onMounted, watch, nextTick } from 'vue';
  import { type BreadcrumbItem } from '@/types';
  import axios from 'axios';
  // import { toast } from '@/plugins/toast';
  import { useNotification } from '@kyvg/vue3-notification';
  import { usePage } from '@inertiajs/vue3';
  
  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Attendance',
      href: '/attendance',
    },
  ];
  
  const students = ref([]);
  const classes = ref([]);
  console.log('Classes:', classes.value);
  const tenant = ref(usePage().props.tenant);
  const loading = ref({
    students: false,
    classes: false,
    attendances: false
  });
  const error = ref({
    students: null,
    classes: null,
    attendances: null
  });
  const attendances = ref([]);
  
  const fetchClasses = async () => {
    try {
      loading.value.classes = true;
      const response = await axios.get('/api/classes');
      classes.value = response.data?.data || [];
      error.value.classes = null;
    } catch (err) {
      console.error('Error fetching classes:', err);
      error.value.classes = err.response?.data?.message || 'Failed to load classes';
      classes.value = [];
    } finally {
      loading.value.classes = false;
    }
  };
  
  const fetchAttendances = async () => {
    try {
      loading.value.attendances = true;
      const response = await axios.get('/api/attendances/detailed');
      console.log('API Response:', response.data);
      attendances.value = response.data || [];
    } catch (err) {
      console.error('Error:', err.response);
      error.value.attendances = err.response?.data?.message || 'Failed to load';
      attendances.value = [];
    } finally {
      loading.value.attendances = false;
    }
  };
  
  // Fetch students, classes, and tenant on component mount
  onMounted(async () => {
    try {
      loading.value = { students: true, classes: true, attendances: true };
      
      // Fetch initial data in parallel
      const [studentsResponse] = await Promise.all([
        axios.get('/api/students'),
        fetchClasses(),
        fetchAttendances()
      ]);
      
      students.value = studentsResponse.data?.data || [];
    } catch (err) {
      console.error('Error fetching initial data:', err);
      error.value = {
        students: err.response?.data?.message || 'Failed to fetch students',
        classes: error.value.classes,  // Preserve existing error
        attendances: err.response?.data?.message || 'Failed to fetch attendances'
      };
    } finally {
      loading.value = { students: false, classes: false, attendances: false };
    }
  });
  
  // Filters
  const filters = ref({
    selectedDate: '',
    dayOfWeek: '',
  });
  
  const daysOfWeek = [
    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
  ];
  
  // Pagination
  const currentPage = ref(1);
  const itemsPerPage = 10;
  
  // Compute attendance records based on fetched data
  const attendanceRecords = computed(() => {
    return attendances.value.map(attendance => ({
      id: attendance.id,
      date: attendance.date,
      class: attendance.class || null,
      student: attendance.student || null,
      status: attendance.status,
      markedBy: attendance.marked_by || null
    }));
  });
  
  const filteredRecords = computed(() => {
    let records = attendanceRecords.value;

    if (filters.value.selectedDate) {
      const selectedDate = new Date(filters.value.selectedDate).toISOString().split('T')[0];
      records = records.filter(record => record.date === selectedDate);
    }

    if (filters.value.dayOfWeek) {
      records = records.filter(record => getDayOfWeek(record.date) === filters.value.dayOfWeek);
    }

    return records;
  });
  
  const filteredAttendance = computed(() => {
    const grouped = filteredRecords.value.reduce((acc, record) => {
      const dateKey = record.date;
      if (!acc[dateKey]) {
        acc[dateKey] = {
          date: record.date,
          present: 0,
          absent: 0,
          late: 0,
          totalStudents: 0
        };
      }
      
      acc[dateKey].totalStudents++;
      if (record.status === 'present') acc[dateKey].present++;
      if (record.status === 'absent') acc[dateKey].absent++;
      if (record.status === 'late') acc[dateKey].late++;
      
      return acc;
    }, {});

    return Object.values(grouped).sort((a, b) => new Date(b.date) - new Date(a.date));
  });
  
  const paginatedRecords = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredRecords.value.slice(start, end);
  });
  
  const paginationStart = computed(() => {
    return (currentPage.value - 1) * itemsPerPage + 1;
  });
  
  const paginationEnd = computed(() => {
    return Math.min(currentPage.value * itemsPerPage, filteredRecords.value.length);
  });
  
  const totalPages = computed(() => {
    return Math.ceil(filteredRecords.value.length / itemsPerPage);
  });
  
  const previousPage = () => {
    if (currentPage.value > 1) {
      currentPage.value--;
    }
  };
  
  const nextPage = () => {
    if (currentPage.value < totalPages.value) {
      currentPage.value++;
    }
  };
  
  const showAttendanceForm = ref(false);
  const newAttendance = ref({
    date: '',
    classId: '',
    studentId: '',
    status: 'present',
  });
  
  const safeClasses = computed(() => {
    if (!Array.isArray(classes.value)) {
      console.warn('Classes is not an array:', classes.value);
      return [];
    }
    return classes.value.filter(c => c);
  });
  
  const openNewAttendance = () => {
    console.log('Opening new attendance form');
    showAttendanceForm.value = true;
    // Reset form when opening
    newAttendance.value = {
      date: new Date().toISOString().split('T')[0],  // Set default to today
      classId: safeClasses.value[0]?.id || '',
      studentId: students.value[0]?.id || '',
      status: 'present'
    };
  };
  
  const submitAttendance = async () => {
    try {
      const response = await axios.post('/api/attendances', {
        tenant_id: tenant.value.id,
        student_id: newAttendance.value.studentId,
        class_id: newAttendance.value.classId,
        date: newAttendance.value.date,
        status: newAttendance.value.status
      });

      if (response.status === 201) {
        await fetchAttendances(); // Refresh the list
        showAttendanceForm.value = false;
      }
    } catch (error) {
      console.error('Error submitting attendance:', error);
    }
  };
  
  const showEditModal = ref(false);
  const showDeleteModal = ref(false);
  const selectedRecord = ref(null);
  const isProcessing = ref(false);
  
  const openEditModal = async (record) => {
    selectedRecord.value = {
      ...record,
      classId: record.class?.id || record.class_id // Handle both formats
    };
    
    if (!students.value.length) {
      await fetchStudents();
    }
    
    // Force Vue to recognize the studentId change
    nextTick(() => {
      showEditModal.value = true;
    });
  };
  
  const submitEdit = async () => {
    try {
      isProcessing.value = true;
      const response = await axios.put(`/api/attendances/${selectedRecord.value.id}`, {
        date: selectedRecord.value.date,
        class_id: selectedRecord.value.classId,
        student_id: selectedRecord.value.studentId,
        status: selectedRecord.value.status
      });

      // Update local state
      const index = attendances.value.findIndex(a => a.id === selectedRecord.value.id);
      attendances.value[index] = response.data;
      showToast('Attendance updated successfully');
      showEditModal.value = false;
    } catch (err) {
      console.error('Update failed:', err);
      showToast('Failed to update record', 'error');
    } finally {
      isProcessing.value = false;
    }
  };
  
  const openDeleteModal = (record) => {
    selectedRecord.value = record;
    showDeleteModal.value = true;
  };
  
  const confirmDelete = async () => {
    try {
      isProcessing.value = true;
      await axios.delete(`/api/attendances/${selectedRecord.value.id}`);
      attendances.value = attendances.value.filter(a => a.id !== selectedRecord.value.id);
      showToast('Attendance record deleted');
      showDeleteModal.value = false;
    } catch (err) {
      console.error('Delete failed:', err);
      showToast('Failed to delete record', 'error');
    } finally {
      isProcessing.value = false;
    }
  };
  
  const clearFilters = () => {
    filters.value.selectedDate = '';
    filters.value.dayOfWeek = '';
  };
  
  const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
  };
  
  const attendancePercentage = (present, total) => {
    return Math.round((present / total) * 100);
  };
  
  const getDayOfWeek = (date) => {
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    return days[new Date(date).getDay()];
  };
  
  // Add a watcher for class selection
  watch(() => newAttendance.value.classId, async (newClassId) => {
    if (newClassId) {
      try {
        loading.value.students = true;
        const response = await axios.get(`/api/classes/${newClassId}/students`);
        students.value = response.data?.data || [];
        if (students.value.length > 0) {
          newAttendance.value.studentId = students.value[0].id;
        }
      } catch (err) {
        console.error('Error fetching class students:', err);
        students.value = [];
      } finally {
        loading.value.students = false;
      }
    }
  });

  const fetchStudents = async () => {
    try {
      loading.value.students = true;
      const response = await axios.get('/api/students');
      console.log('Students API Response:', response.data);
      
      // API returns { data: [...] } structure
      students.value = response.data.data || []; // Keep .data.data
      
      // Map students to include both class_id and school_class_id
      students.value = students.value.map(s => ({
        ...s,
        class_id: s.school_class_id // Add alias for compatibility
      }));
    } catch (err) {
      console.error('Error fetching students:', err);
      error.value.students = err.response?.data?.message || 'Failed to load students';
    } finally {
      loading.value.students = false;
    }
  };
  </script>
  
  <style scoped>
  /* Add any custom styles if needed */
  </style>