import { useEffect, useState } from 'react';

/*
 * Installing the staff app.
 *
 * Browsers only let a page install itself after a tap, so "auto install" means:
 * one tap on Install opens the browser's own install prompt straight away
 * (Chrome, Edge, Samsung Internet, Opera). Where no browser API exists
 * (iPhone/iPad, Safari on Mac, Firefox on Android) we show the two steps.
 * Where installing is impossible (Firefox desktop, in-app browsers like
 * Instagram/Facebook) we warn and recommend a browser that can.
 */

let deferredPrompt = null;
let installed = false;
const listeners = new Set();
const notify = () => listeners.forEach((fn) => fn());

if (typeof window !== 'undefined') {
    // Must be caught early: Chrome fires this once, possibly before React mounts.
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        notify();
    });
    window.addEventListener('appinstalled', () => {
        installed = true;
        deferredPrompt = null;
        notify();
    });
}

export function isStandalone() {
    if (typeof window === 'undefined') return false;
    return window.matchMedia?.('(display-mode: standalone)').matches
        || window.matchMedia?.('(display-mode: minimal-ui)').matches
        || window.navigator.standalone === true;
}

/** What kind of browser this is, as far as installing goes. */
export function detectBrowser(ua = typeof navigator !== 'undefined' ? navigator.userAgent : '') {
    const iOS = /iPad|iPhone|iPod/.test(ua) || (/Macintosh/.test(ua) && typeof navigator !== 'undefined' && navigator.maxTouchPoints > 1);
    const android = /Android/i.test(ua);
    const inApp = /FBAN|FBAV|FB_IAB|Instagram|Line\/|Snapchat|TikTok|musical_ly|Twitter|LinkedInApp|GSA\//i.test(ua)
        || (android && /; wv\)/.test(ua));
    const firefox = /Firefox|FxiOS/i.test(ua);
    const samsung = /SamsungBrowser/i.test(ua);
    const edge = /Edg(e|A|iOS)?\//i.test(ua);
    const opera = /OPR\/|Opera/i.test(ua);
    const chrome = /Chrome|CriOS/i.test(ua) && !edge && !opera && !samsung;
    const safari = /Safari/i.test(ua) && !/Chrome|CriOS|FxiOS|EdgiOS|OPR|SamsungBrowser/i.test(ua);

    return { iOS, android, inApp, firefox, samsung, edge, opera, chrome, safari, chromium: chrome || edge || opera || samsung };
}

/**
 * mode:
 *  - installed:   already running as the installed app (hide the button)
 *  - prompt:      one tap opens the browser's install prompt
 *  - steps:       no API; show the browser's own steps (iPhone, Safari Mac, Firefox Android, Chromium without prompt yet)
 *  - unsupported: this browser cannot install apps; recommend another
 */
export function installState() {
    const b = detectBrowser();
    if (installed || isStandalone()) return { mode: 'installed', browser: b };
    if (b.inApp) return { mode: 'unsupported', browser: b, reason: 'in_app' };
    if (deferredPrompt) return { mode: 'prompt', browser: b };
    if (b.iOS) return { mode: 'steps', browser: b, steps: 'ios' };
    if (b.safari) return { mode: 'steps', browser: b, steps: 'mac_safari' };
    if (b.firefox && b.android) return { mode: 'steps', browser: b, steps: 'firefox_android' };
    if (b.firefox) return { mode: 'unsupported', browser: b, reason: 'firefox_desktop' };
    if (b.chromium) return { mode: 'steps', browser: b, steps: 'chromium_menu' };
    return { mode: 'unsupported', browser: b, reason: 'unknown' };
}

/** Opens the browser's install prompt. Resolves to 'accepted' | 'dismissed' | 'unavailable'. */
export async function promptInstall() {
    if (!deferredPrompt) return 'unavailable';
    const evt = deferredPrompt;
    deferredPrompt = null;
    evt.prompt();
    const choice = await evt.userChoice.catch(() => ({ outcome: 'dismissed' }));
    if (choice?.outcome === 'accepted') installed = true;
    notify();
    return choice?.outcome || 'dismissed';
}

export function usePwaInstall() {
    const [state, setState] = useState(installState);
    useEffect(() => {
        const update = () => setState(installState());
        listeners.add(update);
        update();
        return () => listeners.delete(update);
    }, []);
    return state;
}

/**
 * Called from AppLayout, so only signed-in staff get the manifest and service worker.
 * (After an in-app login the page head is not reloaded, so the manifest is added here too.)
 */
export function enableStaffApp() {
    if (typeof document === 'undefined') return;
    if (!document.querySelector('link[rel="manifest"]')) {
        const link = document.createElement('link');
        link.rel = 'manifest';
        link.href = '/manifest.json';
        document.head.appendChild(link);
    }
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js', { scope: '/app/' }).catch(() => { /* not fatal */ });
    }
}
