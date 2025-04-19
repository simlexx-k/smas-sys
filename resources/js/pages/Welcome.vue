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


const features = [
  {
    title: "CBC Compliance",
    description: "Full alignment with Kenya's Competency-Based Curriculum requirements",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>`
  },
  {
    title: "Automated Reporting",
    description: "Generate ministry-compliant reports with one click",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>`
  },
  {
    title: "Parent Portal",
    description: "Real-time progress tracking for parents & guardians",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>`
  }
];

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
              aria-label="Toggle dark mode"
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
              aria-label="Toggle mobile menu"
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
        <div class="flex flex-col lg:flex-row items-center gap-12">
          <div class="lg:w-1/2 text-center lg:text-left">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
              CBC Management Made Simple for Kenyan Schools
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
              Streamline assessments, reporting, and compliance with Kenya's Competency-Based Curriculum.
            </p>
            <div class="mt-10 flex flex-wrap gap-4 justify-center lg:justify-start">
              <Link
                :href="route('register')"
                class="rounded-md bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-500"
              >
                Start Free Trial
              </Link>
              <a 
                href="#demo" 
                class="flex items-center gap-2 px-6 py-3 text-gray-900 dark:text-gray-200 hover:text-blue-600"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Watch Demo
              </a>
            </div>
          </div>

          <div class="lg:w-1/2 mt-12 lg:mt-0">
            <div class="relative rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
              <img 
                src="/screenshots/attendance-dashboard.png" 
                alt="SMAS CBC dashboard interface showing assessment tracking"
                class="w-full h-auto"
                loading="lazy"
              >
              <div class="absolute inset-0 ring-1 ring-inset ring-gray-900/10"></div>
            </div>
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

    <!-- Client Logos -->
    <div class="py-16 bg-white dark:bg-gray-900" data-aos="fade-up">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <p class="text-gray-600 dark:text-gray-300">Trusted by leading Kenyan institutions</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 opacity-75">
          <img src="/logos/moe.png" alt="Ministry of Education Kenya" class="h-12 mx-auto" loading="lazy">
          <img src="/logos/knec.jpg" alt="KNEC" class="h-12 mx-auto" loading="lazy">
          <img src="/logos/knut.jpg" alt="KNUT" class="h-12 mx-auto" loading="lazy">
          <img src="/logos/kenyattau.png" alt="Kenyatta University" class="h-12 mx-auto" loading="lazy">
          <img src="/logos/kicd.jpg" alt="KICD" class="h-12 mx-auto" loading="lazy">
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-24 bg-white dark:bg-gray-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Built for Kenyan Education Needs</h2>
          <p class="mt-4 text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            Specialized tools that align perfectly with CBC requirements and local school operations
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div 
            v-for="(feature, index) in features" 
            :key="index"
            class="p-8 bg-gray-50 dark:bg-gray-800 rounded-xl hover:shadow-lg transition-shadow"
            data-aos="zoom-in"
          >
            <div class="w-12 h-12 mb-6 flex items-center justify-center bg-blue-100 dark:bg-blue-900 rounded-lg">
              <span v-html="feature.icon"></span>
            </div>
            <h3 class="text-xl font-semibold mb-3 text-gray-900 dark:text-white">{{ feature.title }}</h3>
            <p class="text-gray-600 dark:text-gray-300">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Demo Video -->
    <div class="py-16" data-aos="fade-up">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="aspect-video bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden shadow-xl">
          <iframe 
            class="w-full h-full" 
            src="https://www.youtube.com/embed/your-video-id" 
            title="SMAS Platform Demo"
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen
          ></iframe>
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
          <div 
            v-for="(plan, index) in pricingPlans" 
            :key="plan.name" 
            class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow"
          >
            <h3 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">{{ plan.name }}</h3>
            <div class="text-4xl font-bold mb-6 text-blue-600 dark:text-blue-400">{{ plan.price }}</div>
            <ul class="space-y-3 mb-8">
              <li 
                v-for="feature in plan.features" 
                :key="feature" 
                class="flex items-center text-gray-600 dark:text-gray-300"
              >
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
      <div class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-4 gap-8">
        <div>
          <h3 class="text-sm font-semibold mb-4">Product</h3>
          <ul class="space-y-2">
            <li><a href="#features" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Features</a></li>
            <li><a href="#pricing" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Pricing</a></li>
            <li><a href="/docs" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Documentation</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-sm font-semibold mb-4">Company</h3>
          <ul class="space-y-2">
            <li><a href="/about" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">About</a></li>
            <li><a href="/blog" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Blog</a></li>
            <li><a href="/contact" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Contact</a></li>
          </ul>
        </div>

        <div>
          <h3 class="text-sm font-semibold mb-4">Legal</h3>
          <ul class="space-y-2">
            <li><a href="/privacy" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Privacy</a></li>
            <li><a href="/terms" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Terms</a></li>
            <li><a href="/security" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Security</a></li>
          </ul>
        </div>

        <div class="md:col-span-2 lg:col-span-1">
          <h3 class="text-sm font-semibold mb-4">Stay Updated</h3>
          <form @submit.prevent="subscribeNewsletter" class="flex gap-2">
            <input
              type="email"
              v-model="email"
              placeholder="Email address"
              class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800"
              required
            >
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500"
            >
              Subscribe
            </button>
          </form>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-6 py-8 border-t border-gray-200 dark:border-gray-800">
        <div class="md:flex md:items-center md:justify-between">
          <p class="text-center text-xs text-gray-600 dark:text-gray-400">
            &copy; {{ new Date().getFullYear() }} SMAS. All rights reserved.
          </p>
          <div class="mt-4 md:mt-0 flex justify-center space-x-6">
            <a href="#" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
              <span class="sr-only">Twitter</span>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
              <span class="sr-only">Facebook</span>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
              </svg>
            </a>
          </div>
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

/* AOS animation delays */
[data-aos="fade-up"] {
  transition-delay: 0.3s;
}

[data-aos="zoom-in"] {
  transition-delay: 0.2s;
}
</style>