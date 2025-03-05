<script setup lang="ts">
import { type User } from '@/types';

interface Teacher {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Class {
    id: number;
    name: string;
    grade: string;
    students_count: number;
}

interface Lesson {
    id: number;
    subject: {
        name: string;
    };
    class: {
        name: string;
    };
    start_time: string;
    end_time: string;
    status: 'scheduled' | 'in_progress' | 'completed' | 'cancelled';
}

interface Activity {
    id: number;
    type: string;
    description: string;
    created_at: string;
}

interface Props {
    tenant: {
        id: number;
        name: string;
    };
    teacher: User;
    classes: Class[];
    upcoming_lessons: Lesson[];
    recent_activities: Activity[];
}

const props = defineProps<Props>();

const formatDateTime = (datetime: string) => {
    return new Date(datetime).toLocaleString();
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'scheduled':
            return 'bg-blue-100 text-blue-800';
        case 'in_progress':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-gray-100 text-gray-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <div>
        <!-- Welcome Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800">Welcome, {{ teacher.name }}!</h2>
            <p class="text-gray-600">Here's your teaching overview for today</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900">My Classes</h3>
                <p class="text-3xl font-bold text-indigo-600">{{ classes.length }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900">Total Students</h3>
                <p class="text-3xl font-bold text-indigo-600">
                    {{ classes.reduce((sum, c) => sum + c.students_count, 0) }}
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900">Today's Lessons</h3>
                <p class="text-3xl font-bold text-indigo-600">
                    {{ upcoming_lessons.filter(lesson => 
                        new Date(lesson.start_time).toDateString() === new Date().toDateString()
                    ).length }}
                </p>
            </div>
        </div>

        <!-- Upcoming Lessons -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Lessons</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul role="list" class="divide-y divide-gray-200">
                    <li v-for="lesson in upcoming_lessons" :key="lesson.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-indigo-600">
                                        {{ lesson.subject?.name || 'No Subject' }}
                                    </p>
                                    <span class="mx-2 text-gray-500">•</span>
                                    <p class="text-sm text-gray-500">
                                        {{ lesson.class?.name || 'No Class' }}
                                    </p>
                                    <span 
                                        class="ml-2 px-2 py-1 text-xs rounded-full"
                                        :class="getStatusColor(lesson.status)"
                                    >
                                        {{ lesson.status }}
                                    </span>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ new Date(lesson.start_time).toLocaleTimeString() }} - 
                                    {{ new Date(lesson.end_time).toLocaleTimeString() }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ new Date(lesson.start_time).toLocaleDateString() }}
                            </div>
                        </div>
                    </li>
                    <li v-if="upcoming_lessons.length === 0" class="px-6 py-4 text-gray-500">
                        No upcoming lessons
                    </li>
                </ul>
            </div>
        </div>

        <!-- Recent Activities -->
        <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activities</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul role="list" class="divide-y divide-gray-200">
                    <li v-for="activity in recent_activities" :key="activity.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ activity.type }}</p>
                                <p class="text-sm text-gray-500">{{ activity.description }}</p>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ formatDateTime(activity.created_at) }}
                            </div>
                        </div>
                    </li>
                    <li v-if="recent_activities.length === 0" class="px-6 py-4 text-gray-500">
                        No recent activities
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template> 