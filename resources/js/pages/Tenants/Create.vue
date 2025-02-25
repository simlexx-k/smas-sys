<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Create New Tenant</h2>
            <form @submit.prevent="submit">
              <div class="space-y-6">
                <div>
                  <label for="domain" class="block text-sm font-medium text-gray-700 mb-1">Domain</label>
                  <TextInput
                    id="domain"
                    v-model="form.domain"
                    type="text"
                    class="mt-1 block w-full"
                    :class="{ 'border-red-500': errors.domain }"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.domain" />
                </div>

                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tenant Name</label>
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    :class="{ 'border-red-500': errors.name }"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Admin User Details</h3>
                  <div class="space-y-4">
                    <div>
                      <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">Admin Name</label>
                      <TextInput
                        id="admin_name"
                        v-model="form.admin_name"
                        type="text"
                        class="mt-1 block w-full"
                        :class="{ 'border-red-500': errors.admin_name }"
                        required
                      />
                      <InputError class="mt-2" :message="form.errors.admin_name" />
                    </div>

                    <div>
                      <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                      <TextInput
                        id="admin_email"
                        v-model="form.admin_email"
                        type="email"
                        class="mt-1 block w-full"
                        :class="{ 'border-red-500': errors.admin_email }"
                        required
                      />
                      <InputError class="mt-2" :message="form.errors.admin_email" />
                    </div>

                    <div>
                      <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">Admin Password</label>
                      <TextInput
                        id="admin_password"
                        v-model="form.admin_password"
                        type="password"
                        class="mt-1 block w-full"
                        :class="{ 'border-red-500': errors.admin_password }"
                        required
                      />
                      <InputError class="mt-2" :message="form.errors.admin_password" />
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between">
                  <button
                    type="button"
                    @click="goBack"
                    class="text-gray-600 hover:text-gray-800 font-medium"
                  >
                    &larr; Back
                  </button>
                  <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                    :disabled="form.processing"
                  >
                    Create Tenant
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TextInput from '@/components/TextInput.vue';
import InputError from '@/components/InputError.vue';

interface TenantForm {
  domain: string;
  name: string;
  admin_name: string;
  admin_email: string;
  admin_password: string;
}

const form = useForm<TenantForm>({
  domain: '',
  name: '',
  admin_name: '',
  admin_email: '',
  admin_password: '',
});

const errors = ref({});
const loading = ref(false);

const fullDomain = computed(() => {
  return form.domain ? `${form.domain}.example.com` : 'example.com';
});

function validateForm() {
  errors.value = {};
  if (!form.domain) {
    errors.value.domain = 'Subdomain is required.';
  }
  if (!form.name) {
    errors.value.name = 'Name is required.';
  }
  if (!form.admin_name) {
    errors.value.admin_name = 'Admin name is required.';
  }
  if (!form.admin_email) {
    errors.value.admin_email = 'Admin email is required.';
  }
  if (!form.admin_password) {
    errors.value.admin_password = 'Admin password is required.';
  }
  return Object.keys(errors.value).length === 0;
}

function submit() {
  if (!validateForm()) return;

  loading.value = true;
  form.post('/tenants', {
    onSuccess: () => {
      form.reset();
      router.visit('/tenants');
    },
    onError: (err) => {
      errors.value = err;
    },
    onFinish: () => {
      loading.value = false;
    },
  });
}

function goBack() {
  router.visit('/tenants');
}
</script>
