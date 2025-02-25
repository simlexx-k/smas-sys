<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Tenant
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 gap-6">
                <div>
                  <label for="domain" class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2">
                    <Globe class="w-4 h-4" />
                    Domain
                  </label>
                  <TextInput
                    id="domain"
                    v-model="form.domain"
                    type="text"
                    class="mt-1 block w-full pl-10"
                    required
                    autofocus
                  />
                  <InputError class="mt-2" :message="form.errors.domain" />
                </div>

                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2">
                    <User class="w-4 h-4" />
                    Name
                  </label>
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full pl-10"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                  <label for="admin_id" class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2">
                    <Key class="w-4 h-4" />
                    Admin
                  </label>
                  <select
                    id="admin_id"
                    v-model="form.admin_id"
                    class="mt-1 block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="">{{ props.tenant.admin ? props.tenant.admin.name : 'Select Admin' }}</option>
                    <option
                      v-for="user in props.users"
                      :key="user.id"
                      :value="user.id"
                    >
                      {{ user.name }}
                    </option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.admin_id" />
                </div>
              </div>

              <div class="flex items-center justify-end mt-6">
                <Link
                  :href="route('tenants.index')"
                  class="text-gray-600 hover:text-gray-900 mr-4"
                >
                  Cancel
                </Link>
                <PrimaryButton
                  type="submit"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                >
                  Save Changes
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Globe, User, Key } from 'lucide-vue-next';

const props = defineProps({
  tenant: Object,
  users: Array,
});

const form = useForm({
  domain: props.tenant.domain,
  name: props.tenant.name,
  admin_id: props.tenant.admin_id,
});

const submit = () => {
  form.put(route('tenants.update', props.tenant.hashed_id));
};
</script>
