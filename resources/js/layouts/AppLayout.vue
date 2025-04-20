<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import 'vue-toastification/dist/index.css';
import type { BreadcrumbItemType } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    title?: string;
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    title: '',
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="font-sans min-h-screen bg-gray-50">
        <AppLayout :breadcrumbs="breadcrumbs">
            <template #header>
                <slot name="header" />
            </template>
            
            <slot />
        </AppLayout>
    </div>
</template>

<style>
/* Optional custom toast styles */
.toast-container {
    @apply text-sm;
}

.Vue-Toastification__toast--success {
    @apply bg-green-500 text-white;
}

.Vue-Toastification__toast--error {
    @apply bg-red-500 text-white;
}

.Vue-Toastification__toast--warning {
    @apply bg-yellow-500 text-white;
}

.Vue-Toastification__toast--info {
    @apply bg-blue-500 text-white;
}
</style>
