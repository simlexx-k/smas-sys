<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import RegisterSchoolLayout from '@/layouts/RegisterSchoolLayout.vue';
import { ref } from 'vue';
import { School, Phone, Mail, MapPin, Upload } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    domain: '',
    logo: null as File | null,
    school_type: 'primary', // primary, secondary
    admin_name: '',
    admin_email: '',
    admin_password: '',
    admin_password_confirmation: '',
});

const logoPreview = ref<string | null>(null);
const processing = ref(false);

const handleLogoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        form.logo = input.files[0];
        logoPreview.value = URL.createObjectURL(input.files[0]);
    }
};

const submit = () => {
    processing.value = true;
    form.post(route('register.school'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            logoPreview.value = null;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Register Your School" />

    <RegisterSchoolLayout>
        <div class="py-12">
            <div class="sm:mx-auto sm:w-full sm:max-w-4xl">
                <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Register Your School
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Join hundreds of schools managing CBC curriculum effectively
                </p>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-4xl">
                <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- School Information -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                School Information
                            </h3>
                            
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                <!-- School Name -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        School Name
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <School class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            class="block w-full pl-10 py-3 text-gray-900 dark:text-white placeholder-gray-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter school name"
                                        />
                                    </div>
                                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <!-- School Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        School Type
                                    </label>
                                    <select
                                        v-model="form.school_type"
                                        class="mt-1 block w-full py-3 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-white dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="primary">Primary School</option>
                                        <option value="secondary">Secondary School</option>
                                    </select>
                                </div>

                                <!-- Domain/Subdomain -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        School Subdomain
                                    </label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <input
                                            v-model="form.domain"
                                            type="text"
                                            required
                                            class="flex-1 min-w-0 block w-full px-3 py-3 rounded-none rounded-l-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="yourschool"
                                        />
                                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300">
                                            .smas.com
                                        </span>
                                    </div>
                                    <p v-if="form.errors.domain" class="mt-2 text-sm text-red-600">{{ form.errors.domain }}</p>
                                </div>

                                <!-- Contact Information -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        School Email
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <Mail class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            class="block w-full pl-10 py-3 text-gray-900 dark:text-white placeholder-gray-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="school@example.com"
                                        />
                                    </div>
                                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Phone Number
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <Phone class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            v-model="form.phone"
                                            type="tel"
                                            class="block w-full pl-10 py-3 text-gray-900 dark:text-white placeholder-gray-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="+254 XXX XXX XXX"
                                        />
                                    </div>
                                    <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600">{{ form.errors.phone }}</p>
                                </div>

                                <!-- Address -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        School Address
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <MapPin class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            v-model="form.address"
                                            type="text"
                                            class="block w-full pl-10 py-3 text-gray-900 dark:text-white placeholder-gray-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Physical address"
                                        />
                                    </div>
                                    <p v-if="form.errors.address" class="mt-2 text-sm text-red-600">{{ form.errors.address }}</p>
                                </div>

                                <!-- School Logo -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        School Logo
                                    </label>
                                    <div class="mt-1 flex items-center space-x-4">
                                        <div class="flex-shrink-0 h-24 w-24 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                            <img
                                                v-if="logoPreview"
                                                :src="logoPreview"
                                                class="h-full w-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="h-full w-full flex items-center justify-center text-gray-400"
                                            >
                                                <Upload class="h-8 w-8" />
                                            </div>
                                        </div>
                                        <label class="cursor-pointer bg-white dark:bg-gray-700 py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <span>Upload Logo</span>
                                            <input
                                                type="file"
                                                class="sr-only"
                                                accept="image/*"
                                                @change="handleLogoChange"
                                            />
                                        </label>
                                    </div>
                                    <p v-if="form.errors.logo" class="mt-2 text-sm text-red-600">{{ form.errors.logo }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Account -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white border-t pt-6">
                                Administrator Account
                            </h3>
                            
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Admin Name
                                    </label>
                                    <input
                                        v-model="form.admin_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full py-3 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Full name"
                                    />
                                    <p v-if="form.errors.admin_name" class="mt-2 text-sm text-red-600">{{ form.errors.admin_name }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Admin Email
                                    </label>
                                    <input
                                        v-model="form.admin_email"
                                        type="email"
                                        required
                                        class="mt-1 block w-full py-3 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="admin@email.com"
                                    />
                                    <p v-if="form.errors.admin_email" class="mt-2 text-sm text-red-600">{{ form.errors.admin_email }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Password
                                    </label>
                                    <input
                                        v-model="form.admin_password"
                                        type="password"
                                        required
                                        class="mt-1 block w-full py-3 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <p v-if="form.errors.admin_password" class="mt-2 text-sm text-red-600">{{ form.errors.admin_password }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Confirm Password
                                    </label>
                                    <input
                                        v-model="form.admin_password_confirmation"
                                        type="password"
                                        required
                                        class="mt-1 block w-full py-3 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <Link
                                :href="route('login')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                Already registered?
                            </Link>
                            <button
                                type="submit"
                                :disabled="processing"
                                class="inline-flex justify-center py-3 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                <svg
                                    v-if="processing"
                                    class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                Register School
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </RegisterSchoolLayout>
</template> 