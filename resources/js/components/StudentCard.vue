<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
  student: {
    id: number;
    student_id: string;
    first_name: string;
    last_name: string;
    email: string;
    gender: string;
    date_of_birth: string;
    avatar_url: string;
    status: string;
    enrollment_date: string;
  };
}>();

const isCollapsed = ref(true);

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value;
};
</script>

<template>
  <div class="bg-white overflow-hidden shadow rounded-lg">
    <!-- Basic Info - Always Visible -->
    <div 
      class="px-4 py-5 sm:p-6 cursor-pointer"
      @click="toggleCollapse"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="flex-shrink-0 h-12 w-12">
            <img
              :src="student.avatar_url || '/default-avatar.png'"
              :alt="`${student.first_name} ${student.last_name}`"
              class="h-12 w-12 rounded-full object-cover"
            >
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">
              {{ student.first_name }} {{ student.last_name }}
            </h3>
            <p class="text-sm text-gray-500">ID: {{ student.student_id }}</p>
          </div>
        </div>
        <button 
          class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none"
          :aria-label="isCollapsed ? 'Show details' : 'Hide details'"
        >
          <svg 
            class="w-5 h-5 transform transition-transform duration-200"
            :class="{ 'rotate-180': !isCollapsed }"
            xmlns="http://www.w3.org/2000/svg" 
            viewBox="0 0 20 20" 
            fill="currentColor"
          >
            <path 
              fill-rule="evenodd" 
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" 
              clip-rule="evenodd" 
            />
          </svg>
        </button>
      </div>

      <!-- Status Badge -->
      <div class="mt-2">
        <span 
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
          :class="{
            'bg-green-100 text-green-800': student.status === 'active',
            'bg-red-100 text-red-800': student.status === 'inactive'
          }"
        >
          {{ student.status }}
        </span>
      </div>
    </div>

    <!-- Detailed Info - Collapsible -->
    <div 
      v-show="!isCollapsed"
      class="px-4 pb-5 sm:px-6 border-t border-gray-200 transition-all duration-200"
    >
      <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
        <div class="sm:col-span-1">
          <dt class="text-sm font-medium text-gray-500">Email</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ student.email }}</dd>
        </div>
        <div class="sm:col-span-1">
          <dt class="text-sm font-medium text-gray-500">Gender</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ student.gender }}</dd>
        </div>
        <div class="sm:col-span-1">
          <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ new Date(student.date_of_birth).toLocaleDateString() }}
          </dd>
        </div>
        <div class="sm:col-span-1">
          <dt class="text-sm font-medium text-gray-500">Enrollment Date</dt>
          <dd class="mt-1 text-sm text-gray-900">
            {{ new Date(student.enrollment_date).toLocaleDateString() }}
          </dd>
        </div>
      </dl>
    </div>
  </div>
</template>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.duration-200 {
  transition-duration: 200ms;
}
</style> 