<script setup lang="ts">
defineProps<{
  modelValue: string | number;
  class?: string;
}>();
</script>

<template>
  <select
    :value="modelValue"
    @input="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
    :class="class"
  >
    <slot></slot>
  </select>
</template> 