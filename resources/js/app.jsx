import '../css/app.css';
import './bootstrap';
import './lib/pwa'; // catches the browser's install event early

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { route } from 'ziggy-js';

window.route = route;

const appName = window.appName || import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        return resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        );
    },
    setup({ el, App, props }) {
        try {
            const root = createRoot(el);
            root.render(<App {...props} />);
        } catch (error) {
            console.error('CRITICAL ERROR IN MOUNT:', error);
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// The service worker is registered by AppLayout, i.e. only for signed-in staff (see lib/pwa.js).
