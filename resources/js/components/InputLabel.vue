<template>
  <label :for="for" class="block text-sm font-medium text-gray-700 mb-1">
    <slot />
  </label>
</template>

<script setup lang="ts">
defineProps({
  for: {
    type: String,
    required: true,
  },
});
</script>
