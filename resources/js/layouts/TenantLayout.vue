<script setup lang="ts">
import TenantLayout from '@/layouts/app/TenantSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <TenantLayout :breadcrumbs="breadcrumbs">
        <slot />
    </TenantLayout>
</template>
