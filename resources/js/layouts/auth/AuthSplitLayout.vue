<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const name = page.props.name;
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="relative hidden h-full flex-col bg-muted p-10 text-white dark:border-r lg:flex">
            <div class="absolute inset-0 bg-zinc-900" />
            <div class="absolute inset-0 animate-abstract-bg" />
            
            <!-- Education Animations Container -->
            <div class="absolute inset-0 z-10 overflow-hidden">
                <!-- Floating Book -->
                <svg class="absolute left-20 top-1/4 w-16 h-16 text-blue-300 animate-float"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3V21M12 3C10 3 8 4.5 8 6C8 7.5 10 9 12 9C14 9 16 7.5 16 6C16 4.5 14 3 12 3ZM12 3C14 3 16 4.5 16 6C16 7.5 14 9 12 9C10 9 8 7.5 8 6C8 4.5 10 3 12 3Z"/>
                </svg>

                <!-- Rotating Globe -->
                <svg class="absolute right-20 top-1/3 w-20 h-20 text-green-300 animate-spin-slow-reverse"
                     viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="3"/>
                    <path d="M50 15A35 35 0 0 1 50 85M50 15a35 35 0 0 0 0 70m0-70a40 40 0 0 1 40 40m-40-40a40 40 0 0 0-40 40" 
                          fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>

                <!-- Writing Pencil -->
                <svg class="absolute bottom-20 left-1/4 w-24 h-24 text-yellow-300 animate-write"
                     viewBox="0 0 100 100">
                    <path d="M20 80L40 20L80 50L60 90Z" fill="none" stroke="currentColor" stroke-width="3" 
                          stroke-linecap="round" pathLength="100"/>
                    <path d="M40 20L80 50" fill="none" stroke="currentColor" stroke-width="3" 
                          stroke-dasharray="100" stroke-dashoffset="100"/>
                </svg>

                <!-- Graduation Cap -->
                <svg class="absolute bottom-1/3 right-10 w-20 h-20 text-purple-300 animate-float-delayed"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 10v8m16-8v8M12 6l-8 4 8 4 8-4-8-4zM12 6v8m-8-4l8 4m0 0l8-4"/>
                </svg>
            </div>

            <Link :href="route('home')" class="relative z-20 flex items-center text-lg font-medium">
                <AppLogoIcon class="mr-2 size-8 fill-current text-white" />
                {{ name }}
            </Link>
            <div v-if="quote" class="relative z-20 mt-auto">
                <blockquote class="space-y-2">
                    <p class="text-lg">&ldquo;{{ quote.message }}&rdquo;</p>
                    <footer class="text-sm text-neutral-300">{{ quote.author }}</footer>
                </blockquote>
            </div>
            <div class="relative z-20 mt-8">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" class="w-24 h-24 text-white animate-spin-slow">
                    <path fill="currentColor" d="M50 0a50 50 0 1 0 50 50A50 50 0 0 0 50 0Zm0 90a40 40 0 1 1 40-40 40 40 0 0 1-40 40Z"/>
                    <path fill="currentColor" d="M50 10a40 40 0 1 0 40 40 40 40 0 0 0-40-40Zm0 70a30 30 0 1 1 30-30 30 30 0 0 1-30 30Z"/>
                </svg>
            </div>
        </div>
        <div class="lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">{{ title }}</h1>
                    <p class="text-sm text-muted-foreground" v-if="description">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes abstract-bg {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.animate-abstract-bg {
    background: linear-gradient(-45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b);
    background-size: 400% 400%;
    animation: abstract-bg 15s ease infinite;
    opacity: 0.1;
}

@keyframes spin-slow {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

@keyframes spin-slow-reverse {
    from { transform: rotate(0deg); }
    to { transform: rotate(-360deg); }
}

@keyframes write {
    from { stroke-dashoffset: 100; }
    to { stroke-dashoffset: 0; }
}

@keyframes float-delayed {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

.animate-spin-slow {
    animation: spin-slow 10s linear infinite;
}

.animate-float {
    animation: float 4s ease-in-out infinite;
}

.animate-spin-slow-reverse {
    animation: spin-slow-reverse 20s linear infinite;
}

.animate-write path:last-child {
    animation: write 3s ease-in-out infinite;
    animation-delay: 1s;
}

.animate-float-delayed {
    animation: float-delayed 5s ease-in-out infinite;
    animation-delay: 0.5s;
}
</style>