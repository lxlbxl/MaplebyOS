import { usePage } from '@inertiajs/react';

/**
 * What the signed-in person may see/do (from App\Support\Access). The server
 * enforces all of this; the app only hides what would be refused anyway.
 */
export function useCan() {
    const can = usePage().props.auth?.can || {};
    return (permission) => !!can[permission];
}
