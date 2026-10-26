import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { route } from 'ziggy-js';

window.route = route;

const appName = window.appName || import.meta.env.VITE_APP_NAME || 'Laravel';

console.log('App starting...');

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        console.log('Resolving page:', name);
        return resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        );
    },
    setup({ el, App, props }) {
        console.log('Setup called, mounting to:', el);
        try {
            const root = createRoot(el);
            root.render(<App {...props} />);
            console.log('Render called successfully');
        } catch (error) {
            console.error('CRITICAL ERROR IN MOUNT:', error);
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then((registration) => {
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            })
            .catch((err) => {
                console.log('ServiceWorker registration failed: ', err);
            });
    });
}
