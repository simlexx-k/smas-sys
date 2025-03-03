import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, type DefineComponent } from 'vue';
// import { ZiggyVue } from 'ziggy-js/dist/vue.m';
import { ZiggyVue } from 'ziggy-js';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import VueApexCharts from 'vue3-apexcharts';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, {
                position: 'top-right',
                timeout: 3000,
            })
            .use(VueApexCharts)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();
