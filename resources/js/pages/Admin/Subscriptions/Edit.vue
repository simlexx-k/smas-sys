<template>
  <AppLayout title="Edit Subscription">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Edit Subscription for {{ subscription.tenant?.name }}
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <SubscriptionForm 
              :subscription="subscription"
              :plans="plans"
              :features="features"
              @submitted="updateSubscription"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SubscriptionForm from '@/Components/Admin/SubscriptionForm.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  subscription: Object,
  plans: Array,
  features: Array
});

const updateSubscription = (formData) => {
  router.put(`/admin/subscriptions/${props.subscription.id}`, formData);
};
</script> 