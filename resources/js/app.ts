import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import Swal from 'sweetalert2';

//importaciones propias 
import 'boxicons/css/boxicons.min.css';
//fin importaciones propias

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Crea la instancia de la aplicación Vue
        const app = createApp({ render: () => h(App, props) });
        
        // Agrega SweetAlert2 a las propiedades globales de esta instancia
        app.config.globalProperties.$swal = Swal;
        
        // Usa la misma instancia para configurar y montar la app
        app.use(plugin)
           .use(ZiggyVue)
           .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();