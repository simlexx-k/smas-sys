<script setup lang="ts">
import { ref, watch, onBeforeUnmount } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import SelectInput from '@/components/SelectInput.vue';
import StudentCard from '@/components/StudentCard.vue';
import debounce from 'lodash/debounce';

interface Props {
  classes: {
    id: number;
    name: string;
    grade: string;
    students_count: number;
    students: Array<{
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
    }>;
  }[];
  filters: {
    search: string;
    class_id: number | null;
    view_all: boolean;
    sort_by: string;
    sort_order: string;
    per_page: number;
  };
  total_students: number;
}

const props = defineProps<Props>();

const form = useForm({
  search: props.filters.search || '',
  class_id: props.filters.class_id || '',
  view_all: props.filters.view_all || false,
  sort_by: props.filters.sort_by || 'name',
  sort_order: props.filters.sort_order || 'asc',
  per_page: props.filters.per_page || 10,
});

const sortOptions = [
  { value: 'first_name', label: 'First Name' },
  { value: 'last_name', label: 'Last Name' },
  { value: 'student_id', label: 'Student ID' },
  { value: 'enrollment_date', label: 'Enrollment Date' },
];

// Debounced search handler
const debouncedSearch = debounce(() => {
  submitForm();
}, 300);

// Immediate submit handler for non-search fields
const submitForm = () => {
  form.get(route('teacher.students'), {
    preserveState: true,
    preserveScroll: true,
  });
};

// Watch search field with debounce
watch(() => form.search, () => {
  debouncedSearch();
});

// Watch other fields immediately
watch([
  () => form.class_id,
  () => form.sort_by,
  () => form.sort_order,
  () => form.per_page
], () => {
  submitForm();
});

// Separate watch for view_all to prevent recursion
watch(() => form.view_all, (newValue) => {
  // Reset class_id when toggling view_all
  form.class_id = '';
  submitForm();
});

// Clean up debounce on component unmount
onBeforeUnmount(() => {
  debouncedSearch.cancel();
});

// Add a Map to track collapse state of each class
const collapsedClasses = ref(new Map());

// Function to toggle collapse state
const toggleClass = (classId: number) => {
  collapsedClasses.value.set(classId, !collapsedClasses.value.get(classId));
};

// Initialize all classes as expanded
props.classes.forEach(class_ => {
  collapsedClasses.value.set(class_.id, false);
});
</script>

<template>
  <AppLayout>
    <Head title="Students" />

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Filters -->
      <div class="bg-white p-6 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
          <!-- View All Toggle -->
          <div class="flex items-center">
            <label class="inline-flex items-center">
              <input
                type="checkbox"
                v-model="form.view_all"
                class="form-checkbox h-5 w-5 text-indigo-600"
              >
              <span class="ml-2 text-gray-700">View All Students</span>
            </label>
          </div>

          <div>
            <InputLabel for="search" value="Search" />
            <TextInput
              id="search"
              v-model="form.search"
              type="text"
              class="mt-1 block w-full"
              placeholder="Search students..."
            />
          </div>

          <div>
            <InputLabel for="class" value="Class" />
            <SelectInput
              id="class"
              v-model="form.class_id"
              class="mt-1 block w-full"
            >
              <option value="">All Classes</option>
              <option
                v-for="class_ in classes"
                :key="class_.id"
                :value="class_.id"
              >
                {{ class_.name }}
              </option>
            </SelectInput>
          </div>

          <div>
            <InputLabel for="sort_by" value="Sort By" />
            <SelectInput
              id="sort_by"
              v-model="form.sort_by"
              class="mt-1 block w-full"
            >
              <option
                v-for="option in sortOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </SelectInput>
          </div>

          <div>
            <InputLabel for="sort_order" value="Sort Order" />
            <SelectInput
              id="sort_order"
              v-model="form.sort_order"
              class="mt-1 block w-full"
            >
              <option value="asc">Ascending</option>
              <option value="desc">Descending</option>
            </SelectInput>
          </div>
        </div>
      </div>

      <!-- Classes and Students -->
      <div v-for="class_ in classes" :key="class_.id" class="mb-8">
        <div class="bg-white rounded-lg shadow">
          <div 
            class="px-6 py-4 border-b border-gray-200 flex justify-between items-center cursor-pointer"
            @click="toggleClass(class_.id)"
          >
            <h2 class="text-xl font-semibold text-gray-800">
              {{ class_.name }}
              <span class="text-sm text-gray-500 ml-2">
                ({{ class_.students_count }} students)
              </span>
            </h2>
            <button 
              class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none"
              :aria-label="collapsedClasses.get(class_.id) ? 'Expand class' : 'Collapse class'"
            >
              <svg 
                class="w-5 h-5 transform transition-transform duration-200"
                :class="{ 'rotate-180': !collapsedClasses.get(class_.id) }"
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

          <!-- Collapsible content -->
          <div 
            v-show="!collapsedClasses.get(class_.id)"
            class="p-6 transition-all duration-200"
          >
            <div v-if="class_.students.length === 0" class="text-gray-500">
              No students found
            </div>
            <div
              v-else
              class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
              <StudentCard
                v-for="student in class_.students"
                :key="student.id"
                :student="student"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
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