import './bootstrap';
import '../css/app.css';
import 'v-calendar/style.css';
import 'bootstrap-icons/font/bootstrap-icons.css';


import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import Vue3Toasity from 'vue3-toastify';
import 'vue3-toastify/dist/index.css'

const appName = import.meta.env.VITE_APP_NAME || 'Marsella Etapa 1';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(
                Vue3Toasity,
                { autoClose: 3000, },
              )
              .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
