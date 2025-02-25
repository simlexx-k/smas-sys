<script setup lang="ts">
import { ref } from 'vue';

const form = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    dateOfBirth: '',
    qualifications: '',
    subjects: [],
});

const submit = () => {
    // Handle form submission
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input v-model="form.name" type="text" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input v-model="form.email" type="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
            <input v-model="form.phone" type="tel" id="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
        </div>
        <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
            <textarea v-model="form.address" id="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
        </div>
        <div>
            <label for="dateOfBirth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
            <input v-model="form.dateOfBirth" type="date" id="dateOfBirth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
        </div>
        <div>
            <label for="qualifications" class="block text-sm font-medium text-gray-700">Qualifications</label>
            <textarea v-model="form.qualifications" id="qualifications" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
        </div>
        <div>
            <label for="subjects" class="block text-sm font-medium text-gray-700">Subjects</label>
            <input v-model="form.subjects" type="text" id="subjects" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
        </div>
        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Submit
        </button>
    </form>
</template>
