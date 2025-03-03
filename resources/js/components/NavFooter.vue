<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { type NavItem } from '@/types';
import { SidebarMenu, SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar';

interface Props {
    items: NavItem[];
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem v-for="item in items" :key="item.title">
            <SidebarMenuButton v-if="item.href.startsWith('http')" as="a" :href="item.href" target="_blank">
                <component :is="item.icon" class="h-4 w-4" />
                {{ item.title }}
            </SidebarMenuButton>
            <SidebarMenuButton v-else as-child>
                <Link :href="item.href">
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.title }}
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
