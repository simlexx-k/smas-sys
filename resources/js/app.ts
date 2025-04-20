import './bootstrap';
import '../css/app.css';

import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

// import { ZiggyVue } from 'ziggy-js/dist/vue.m';
import { ZiggyVue } from 'ziggy-js';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import VueApexCharts from 'vue3-apexcharts';
import { initializeTheme } from './composables/useAppearance';
import ChatWidget from './components/ChatWidget.vue';
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        console.log('Resolving page:', name);
        const pages = import.meta.glob('./Pages/**/*.vue');
        console.log('Available pages:', Object.keys(pages));
        const page = pages[`./Pages/${name}.vue`];
        if (!page) {
            console.error(`Page not found: ${name}`);
            throw new Error(`Page not found: ${name}`);
        }
        return page();
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        app.use(plugin)
            .use(ZiggyVue)
            .use(Toast, {
                container: document.body,
                transition: 'Vue-Toastification__fade',
                maxToasts: 5,
                newestOnTop: true
            })
            .use(VueApexCharts);

        // Register the ChatWidget component globally
        app.component('ChatWidget', ChatWidget);
        
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();
