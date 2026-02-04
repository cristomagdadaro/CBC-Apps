import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from './Layouts/AppLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });

        // Register global components
        vueApp.component('Head', Head);
        vueApp.component('Link', Link);
        vueApp.component('AppLayout', AppLayout);

        // Set global property
        vueApp.config.globalProperties.$appVersion = import.meta.env.VITE_APP_VERSION;
        vueApp.config.globalProperties.$appName = import.meta.env.VITE_APP_NAME;
        vueApp.config.globalProperties.$companyName = import.meta.env.VITE_COMPANY_NAME;
        vueApp.config.globalProperties.$companyNameShort = import.meta.env.VITE_COMPANY_NAME_SHORT;
        vueApp.config.globalProperties.$appEnv = import.meta.env.VITE_APP_ENV;

        if (typeof window !== 'undefined') {
            window.__APP_ENV__ = import.meta.env.VITE_APP_ENV || import.meta.env.MODE || 'production';
        }

        return vueApp
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
