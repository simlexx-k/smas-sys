<template>
  <div class="error-page">
    <h1>Error</h1>
    <p>{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps({
  error: String,
});
</script>

<style scoped>
.error-page {
  text-align: center;
  padding: 2rem;
}

h1 {
  color: #dc3545;
}
</style>
