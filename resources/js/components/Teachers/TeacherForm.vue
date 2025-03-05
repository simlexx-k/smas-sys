<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

interface Subject {
    id: number;
    name: string;
}

interface Props {
    departments?: string[];
    subjects: Subject[];
}

const props = withDefaults(defineProps<Props>(), {
    departments: () => [],
    subjects: () => []
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    email: '',
    password: '',
    employee_id: '',
    phone: '',
    department: '',
    joining_date: '',
    qualification: '',
    subjects: [] as number[],
    status: 'Active'
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post('/teachers', form.data(), {
        onSuccess: () => {
            emit('close');
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
        preserveScroll: true
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Basic Information -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input 
                    v-model="form.name" 
                    type="text" 
                    id="name" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                    required 
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    v-model="form.email" 
                    type="email" 
                    id="email" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                    required 
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input 
                    v-model="form.password" 
                    type="password" 
                    id="password" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                    required 
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                <input 
                    v-model="form.employee_id" 
                    type="text" 
                    id="employee_id" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                    required 
                />
                <p v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600">{{ form.errors.employee_id }}</p>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input 
                    v-model="form.phone" 
                    type="tel" 
                    id="phone" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                    required 
                />
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
            </div>

            <div>
                <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                <select 
                    v-model="form.department" 
                    id="department" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select Department</option>
                    <option v-for="dept in props.departments" :key="dept" :value="dept">
                        {{ dept }}
                    </option>
                </select>
                <p v-if="form.errors.department" class="mt-1 text-sm text-red-600">{{ form.errors.department }}</p>
            </div>

            <div>
                <label for="joining_date" class="block text-sm font-medium text-gray-700">Joining Date</label>
                <input 
                    v-model="form.joining_date" 
                    type="date" 
                    id="joining_date" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                    required 
                />
                <p v-if="form.errors.joining_date" class="mt-1 text-sm text-red-600">{{ form.errors.joining_date }}</p>
            </div>

            <div>
                <label for="qualification" class="block text-sm font-medium text-gray-700">Qualification</label>
                <input 
                    v-model="form.qualification" 
                    type="text" 
                    id="qualification" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                    required 
                />
                <p v-if="form.errors.qualification" class="mt-1 text-sm text-red-600">{{ form.errors.qualification }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Subjects</label>
                <select 
                    v-model="form.subjects" 
                    multiple 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                >
                    <option v-for="subject in props.subjects" :key="subject.id" :value="subject.id">
                        {{ subject.name }}
                    </option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple subjects</p>
                <p v-if="form.errors.subjects" class="mt-1 text-sm text-red-600">{{ form.errors.subjects }}</p>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
            <button 
                type="button" 
                @click="emit('close')"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Cancel
            </button>
            <button 
                type="submit" 
                :disabled="processing || form.processing"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
                <span v-if="processing || form.processing">Saving...</span>
                <span v-else>Save Teacher</span>
            </button>
        </div>
    </form>
</template>
