import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { registerGlobalComponents } from '@/registerGlobalComponents';
import 'driver.js/dist/driver.css';
import { PUBLIC_SERVICES } from '@/Modules/constants/publicServices';
import { resolveAuthContext } from '@/Modules/composables/useAppContext';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pages = import.meta.glob([
    './Pages/**/*.vue',
    '!./Pages/Shared/**/*.vue',
    //'!./Pages/**/components/**/*.vue',
]);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, pages),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });
        registerGlobalComponents(vueApp);
        const fallbackPageProps = props?.initialPage?.props ?? {};

        const defineGlobalGetter = (name, resolver) => {
            Object.defineProperty(vueApp.config.globalProperties, name, {
                configurable: true,
                enumerable: true,
                get() {
                    const pageProps = this?.$page?.props ?? fallbackPageProps;
                    return resolver(pageProps);
                },
            });
        };

        // Set global properties
        vueApp.config.globalProperties.$appVersion = import.meta.env.VITE_APP_VERSION;
        vueApp.config.globalProperties.$appName = import.meta.env.VITE_APP_NAME;
        vueApp.config.globalProperties.$companyName = import.meta.env.VITE_COMPANY_NAME;
        vueApp.config.globalProperties.$companyNameShort = import.meta.env.VITE_COMPANY_NAME_SHORT;
        vueApp.config.globalProperties.$appEnv = import.meta.env.VITE_APP_ENV;
        defineGlobalGetter('$authContext', (pageProps) => resolveAuthContext(pageProps));
        defineGlobalGetter('$currentUser', (pageProps) => resolveAuthContext(pageProps).user);
        defineGlobalGetter('$currentRoles', (pageProps) => resolveAuthContext(pageProps).roles);
        defineGlobalGetter('$currentPermissions', (pageProps) => resolveAuthContext(pageProps).permissions);
        defineGlobalGetter('$isAdminUser', (pageProps) => resolveAuthContext(pageProps).isAdminUser);
        defineGlobalGetter('$deploymentAccess', (pageProps) => pageProps?.deployment_access ?? {});
        vueApp.config.globalProperties.$publicServices = PUBLIC_SERVICES;

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
