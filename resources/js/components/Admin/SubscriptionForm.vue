<template>
  <form @submit.prevent="$emit('submitted', form)">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <!-- Plan Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Plan</label>
        <select
          v-model="form.plan_id"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option v-for="plan in plans" :key="plan.id" :value="plan.id">
            {{ plan.name }} - {{ formatCurrency(plan.price) }}
          </option>
        </select>
        <p v-if="form.errors.plan_id" class="mt-2 text-sm text-red-600">
          {{ form.errors.plan_id }}
        </p>
      </div>

      <!-- Price -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Price</label>
        <input
          type="number"
          step="0.01"
          v-model="form.price"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
        <p v-if="form.errors.price" class="mt-2 text-sm text-red-600">
          {{ form.errors.price }}
        </p>
      </div>

      <!-- End Date -->
      <div>
        <label class="block text-sm font-medium text-gray-700">End Date</label>
        <input
          type="date"
          v-model="form.ends_at"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
        <p v-if="form.errors.ends_at" class="mt-2 text-sm text-red-600">
          {{ form.errors.ends_at }}
        </p>
      </div>

      <!-- Status -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select
          v-model="form.status"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="active">Active</option>
          <option value="canceled">Canceled</option>
        </select>
        <p v-if="form.errors.status" class="mt-2 text-sm text-red-600">
          {{ form.errors.status }}
        </p>
      </div>

      <!-- Features -->
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Features</label>
        <div class="grid grid-cols-2 gap-4 mt-1 sm:grid-cols-3">
          <div
            v-for="feature in features"
            :key="feature"
            class="flex items-start"
          >
            <div class="flex items-center h-5">
              <input
                type="checkbox"
                :value="feature"
                v-model="form.features"
                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              />
            </div>
            <div class="ml-3 text-sm">
              <label class="font-medium text-gray-700">{{ feature }}</label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-end mt-6">
      <button
        type="submit"
        :disabled="form.processing"
        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        Update Subscription
      </button>
    </div>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  subscription: Object,
  plans: Array,
  features: Array
})

const form = useForm({
  plan_id: props.subscription.plan_id,
  price: props.subscription.price,
  ends_at: props.subscription.ends_at?.split('T')[0], // Format for date input
  status: props.subscription.status,
  features: props.subscription.features || []
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value);
}
</script> 