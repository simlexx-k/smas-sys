<template>
  <nav class="flex items-center justify-between" aria-label="Pagination">
    <div class="flex-1 flex justify-between sm:justify-end">
      <Link v-if="links.prev" :href="links.prev" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        Previous
      </Link>
      <Link v-if="links.next" :href="links.next" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        Next
      </Link>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  links: Object,
});
</script>
