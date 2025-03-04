<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    school?: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        address: string | null;
        logo_url: string | null;
        is_active: boolean;
    };
    mode: 'create' | 'edit';
}

const props = withDefaults(defineProps<Props>(), {
    school: undefined,
    mode: 'create'
});

const form = useForm({
    name: props.school?.name ?? '',
    email: props.school?.email ?? '',
    phone: props.school?.phone ?? '',
    address: props.school?.address ?? '',
    is_active: props.school?.is_active ?? true,
    logo: null as File | null
});

const logoPreview = ref<string | null>(props.school?.logo_url ?? null);
const logoInput = ref<HTMLInputElement | null>(null);

const handleLogoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    
    if (file) {
        form.logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    logoPreview.value = null;
    if (logoInput.value) {
        logoInput.value.value = '';
    }
};

const submit = () => {
    if (props.mode === 'create') {
        form.post(route('admin.tenants.store'));
    } else if (props.school) {
        form.put(route('admin.tenants.update', props.school.id));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Logo Upload -->
        <div>
            <label class="block text-sm font-medium text-gray-700">School Logo</label>
            <div class="mt-1 flex items-center space-x-4">
                <div v-if="logoPreview" class="relative">
                    <img :src="logoPreview" class="h-16 w-16 rounded-full object-cover">
                    <button
                        type="button"
                        @click="removeLogo"
                        class="absolute -top-2 -right-2 rounded-full bg-red-100 p-1 text-red-600 hover:bg-red-200"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div v-else class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input
                    ref="logoInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleLogoChange"
                >
                <button
                    type="button"
                    @click="logoInput?.click()"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                    Change Logo
                </button>
            </div>
        </div>

        <!-- School Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700">School Name</label>
            <input
                type="text"
                v-model="form.name"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                required
            >
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
                type="email"
                v-model="form.email"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>

        <!-- Phone -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input
                type="tel"
                v-model="form.phone"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
        </div>

        <!-- Address -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea
                v-model="form.address"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
            <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
        </div>

        <!-- Status -->
        <div>
            <div class="flex items-center">
                <input
                    type="checkbox"
                    v-model="form.is_active"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                >
                <label class="ml-2 block text-sm text-gray-900">Active</label>
            </div>
            <p v-if="form.errors.is_active" class="mt-1 text-sm text-red-600">{{ form.errors.is_active }}</p>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                {{ mode === 'create' ? 'Create School' : 'Update School' }}
            </button>
        </div>
    </form>
</template> 