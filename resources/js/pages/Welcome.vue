<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useDark, useToggle } from '@vueuse/core';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import AOS from 'aos';
import 'aos/dist/aos.css';

const isDark = useDark();
const toggleDark = useToggle(isDark);
const showMobileMenu = ref(false);
const scrollProgress = ref(0);
const email = ref('');

const testimonials = [
  {
    id: 1,
    text: "SMAS has revolutionized how we manage CBC assessments. It's intuitive and saves us countless hours.",
    author: 'Jane Muthoni',
    school: 'Green Valley Academy'
  },
  {
    id: 2,
    text: "The best education management system for Kenyan schools. Highly recommended!",
    author: 'David Omondi',
    school: 'Sunrise High School'
  }
];

const pricingPlans = [
  {
    name: 'Starter',
    price: 'Free',
    features: ['Up to 200 students', 'Basic reporting', 'Email support'],
    cta: 'Get Started'
  },
  {
    name: 'Pro',
    price: 'KSh 15,000/mo',
    features: ['Up to 1000 students', 'Advanced analytics', 'Priority support', 'CBC compliance reports'],
    cta: 'Start Trial'
  },
  {
    name: 'Enterprise',
    price: 'Custom',
    features: ['Unlimited students', 'Dedicated support', 'Custom integrations', 'Training sessions'],
    cta: 'Contact Sales'
  }
];

onMounted(() => {
  AOS.init({ duration: 1000 });
  window.addEventListener('scroll', updateScrollProgress);
});

function updateScrollProgress() {
  const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
  const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
  scrollProgress.value = (winScroll / height) * 100;
}

function subscribeNewsletter() {
  // Handle newsletter subscription
  console.log('Subscribed with:', email.value);
  email.value = '';
}
</script>

<template>
  <Head title="SMAS - School Management System">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 dark:from-gray-900 dark:to-gray-800">
    <!-- Scroll Progress -->
    <div class="fixed top-0 left-0 h-1 bg-blue-600 z-50" :style="{ width: scrollProgress + '%' }"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-40 bg-white/80 backdrop-blur-md dark:bg-gray-900/80 border-b border-gray-200/80 dark:border-gray-700/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">SMAS</span>
          </div>
          
          <div class="hidden md:flex items-center space-x-4">
            <Link
              v-if="$page.props.auth.user"
              :href="route('dashboard')"
              class="px-4 py-2 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400"
            >
              Dashboard
            </Link>
            <template v-else>
              <a href="#features" class="px-4 py-2 text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Features</a>
              <a href="#pricing" class="px-4 py-2 text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Pricing</a>
              <Link
                :href="route('login')"
                class="px-4 py-2 text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400"
              >
                Log in
              </Link>
              <Link
                :href="route('register')"
                class="px-6 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-500"
              >
                Get Started
              </Link>
            </template>
            <button
              @click="toggleDark()"
              class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              <svg v-if="isDark" class="w-6 h-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
              </svg>
              <svg v-else class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
              </svg>
            </button>
          </div>

          <!-- Mobile Menu Button -->
          <div class="md:hidden flex items-center">
            <button
              @click="showMobileMenu = !showMobileMenu"
              class="p-2 text-gray-600 hover:text-blue-600 dark:text-gray-300"
            >
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-show="showMobileMenu" class="md:hidden bg-white dark:bg-gray-800">
        <div class="px-4 pt-2 pb-4 space-y-2">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="block px-4 py-2 text-gray-700 dark:text-gray-200"
          >
            Dashboard
          </Link>
          <template v-else>
            <a href="#features" class="block px-4 py-2 text-gray-600 dark:text-gray-300">Features</a>
            <a href="#pricing" class="block px-4 py-2 text-gray-600 dark:text-gray-300">Pricing</a>
            <Link
              :href="route('login')"
              class="block px-4 py-2 text-gray-600 dark:text-gray-300"
            >
              Log in
            </Link>
            <Link
              :href="route('register')"
              class="block px-4 py-2 text-white bg-blue-600 rounded-md"
            >
              Get Started
            </Link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <div class="pt-24 pb-16 sm:pt-32 sm:pb-24" data-aos="fade-up">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
            Transforming Education with CBC-Focused Management
          </h1>
          <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            Streamline your school's CBC implementation with our comprehensive management system designed specifically for Kenyan schools.
          </p>
          <div class="mt-10 flex items-center justify-center gap-x-6">
            <Link
              v-if="!$page.props.auth.user"
              :href="route('register')"
              class="rounded-md bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-500"
            >
              Start Free Trial
            </Link>
            <a href="#features" class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-200">
              Learn more <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics -->
    <div class="py-16 bg-gray-50 dark:bg-gray-900" data-aos="fade-up">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400">150+</div>
            <div class="mt-2 text-gray-600 dark:text-gray-300">Schools</div>
          </div>
          <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="text-4xl font-bold text-green-600 dark:text-green-400">50k+</div>
            <div class="mt-2 text-gray-600 dark:text-gray-300">Students</div>
          </div>
          <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="text-4xl font-bold text-purple-600 dark:text-purple-400">98%</div>
            <div class="mt-2 text-gray-600 dark:text-gray-300">Satisfaction</div>
          </div>
          <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="text-4xl font-bold text-yellow-600 dark:text-yellow-400">24/7</div>
            <div class="mt-2 text-gray-600 dark:text-gray-300">Support</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-24 bg-white dark:bg-gray-900">
      <!-- ... Existing features content ... -->
    </div>

    <!-- Demo Video -->
    <div class="py-16" data-aos="fade-up">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="aspect-video bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden shadow-xl">
          <iframe 
            class="w-full h-full" 
            src="https://www.youtube.com/embed/your-video-id" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
          </iframe>
        </div>
      </div>
    </div>

    <!-- Testimonials -->
    <div class="py-24 bg-gray-50 dark:bg-gray-900" data-aos="fade-up">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">What Our Clients Say</h2>
        <Swiper :slides-per-view="1" :autoplay="{ delay: 5000 }" class="py-8">
          <SwiperSlide v-for="testimonial in testimonials" :key="testimonial.id">
            <div class="text-center px-4">
              <p class="text-lg text-gray-600 dark:text-gray-300 italic">"{{ testimonial.text }}"</p>
              <div class="mt-6 font-semibold text-gray-900 dark:text-white">{{ testimonial.author }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">{{ testimonial.school }}</div>
            </div>
          </SwiperSlide>
        </Swiper>
      </div>
    </div>

    <!-- Pricing -->
    <div id="pricing" class="py-24 bg-white dark:bg-gray-900" data-aos="fade-up">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">Simple, Transparent Pricing</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(plan, index) in pricingPlans" :key="plan.name" 
               class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">{{ plan.name }}</h3>
            <div class="text-4xl font-bold mb-6 text-blue-600 dark:text-blue-400">{{ plan.price }}</div>
            <ul class="space-y-3 mb-8">
              <li v-for="feature in plan.features" :key="feature" 
                  class="flex items-center text-gray-600 dark:text-gray-300">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ feature }}
              </li>
            </ul>
            <button class="w-full py-3 font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-500">
              {{ plan.cta }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Newsletter -->
    <div class="py-16 bg-gray-50 dark:bg-gray-900" data-aos="fade-up">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Stay Updated</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-8">Subscribe to our newsletter for updates and education insights.</p>
        <form @submit.prevent="subscribeNewsletter" class="max-w-md mx-auto flex gap-4">
          <input
            type="email"
            v-model="email"
            placeholder="Enter your email"
            required
            class="flex-1 px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-2 focus:ring-blue-500"
          >
          <button
            type="submit"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-500 focus:ring-2 focus:ring-blue-500"
          >
            Subscribe
          </button>
        </form>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-6 py-12 md:flex md:items-center md:justify-between lg:px-8">
        <div class="flex justify-center space-x-6 md:order-2">
          <a href="#" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
            <span class="sr-only">Twitter</span>
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
            </svg>
          </a>
          <a href="#" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
            <span class="sr-only">Facebook</span>
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
            </svg>
          </a>
        </div>
        <div class="mt-8 md:mt-0 md:order-1">
          <p class="text-center text-xs leading-5 text-gray-500 dark:text-gray-400">
            &copy; {{ new Date().getFullYear() }} SMAS. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style>
html {
  scroll-behavior: smooth;
}

.dark body {
  background-color: #111827;
  color: #fff;
}

.swiper-pagination-bullet {
  @apply bg-gray-400 dark:bg-gray-600 opacity-100;
}

.swiper-pagination-bullet-active {
  @apply bg-blue-600 dark:bg-blue-400;
}
</style>