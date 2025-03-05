<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';

interface Notification {
    id: string;
    type: string;
    data: {
        tenant_id?: number;
        tenant_name?: string;
        deleted_by?: string;
        deleted_at?: string;
        message?: string;
    };
    read_at: string | null;
    created_at: string;
}

const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);
const isOpen = ref(false);

const fetchNotifications = () => {
    router.get(route('admin.notifications.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            notifications.value = page.props.notifications;
            unreadCount.value = page.props.unreadCount;
        }
    });
};

const markAsRead = (notification: Notification) => {
    router.post(route('admin.notifications.mark-read', notification.id), {}, {
        preserveState: true,
        onSuccess: () => {
            notification.read_at = new Date().toISOString();
            unreadCount.value--;
        }
    });
};

const markAllAsRead = () => {
    router.post(route('admin.notifications.mark-all-read'), {}, {
        preserveState: true,
        onSuccess: () => {
            notifications.value.forEach(n => n.read_at = new Date().toISOString());
            unreadCount.value = 0;
        }
    });
};

onMounted(() => {
    fetchNotifications();
    // Refresh notifications every minute
    setInterval(fetchNotifications, 60000);
});
</script>

<template>
    <div class="relative">
        <!-- Notification Bell -->
        <button
            @click="isOpen = !isOpen"
            class="relative p-1 rounded-full hover:bg-gray-100 focus:outline-none"
        >
            <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="unreadCount > 0" class="absolute top-0 right-0 -mt-1 -mr-1 px-2 py-1 text-xs leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ unreadCount }}
            </span>
        </button>

        <!-- Notification Panel -->
        <div v-if="isOpen" 
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-sm text-indigo-600 hover:text-indigo-900"
                    >
                        Mark all as read
                    </button>
                </div>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                <div v-for="notification in notifications" 
                    :key="notification.id"
                    :class="[
                        'p-4 hover:bg-gray-50 cursor-pointer transition duration-150 ease-in-out',
                        { 'bg-blue-50': !notification.read_at }
                    ]"
                    @click="markAsRead(notification)"
                >
                    <div class="flex items-start">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                {{ notification.data.tenant_name }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ notification.data.message }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ formatDistanceToNow(new Date(notification.created_at), { addSuffix: true }) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="notifications.length === 0" class="p-4 text-center text-gray-500">
                No notifications
            </div>
        </div>
    </div>
</template> 