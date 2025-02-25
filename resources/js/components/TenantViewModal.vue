<template>
  <Modal :show="isOpen" @close="closeModal">
    <div class="p-6">
      <h2 class="text-lg font-medium text-gray-900 mb-4">Tenant Details</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Domain:</label>
          <p class="mt-1 text-sm text-gray-900">{{ tenant.domain }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Name:</label>
          <p class="mt-1 text-sm text-gray-900">{{ tenant.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Admin:</label>
          <p class="mt-1 text-sm text-gray-900">{{ tenant.admin ? tenant.admin.name : 'N/A' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Created At:</label>
          <p class="mt-1 text-sm text-gray-900">{{ formatDate(tenant.created_at) }}</p>
        </div>
      </div>
      <div class="mt-6 flex justify-end">
        <SecondaryButton @click="closeModal">Close</SecondaryButton>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Modal from '@/components/Modal.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';

const props = defineProps({
  tenant: {
    type: Object,
    required: true
  },
  isOpen: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['close']);

const closeModal = () => {
  emit('close');
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};
</script>
