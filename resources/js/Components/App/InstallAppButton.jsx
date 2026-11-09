import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { Download, Share, PlusSquare, MoreVertical, AlertTriangle, X, Smartphone } from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { usePwaInstall, promptInstall } from '@/lib/pwa';
import { cn } from '@/lib/utils';

const DISMISS_KEY = 'mapleby:install-banner-dismissed';

function Steps({ state }) {
    if (state.mode === 'unsupported') {
        const inApp = state.reason === 'in_app';
        return (
            <div className="space-y-3 text-sm">
                <p className="flex gap-2 rounded-lg bg-amber-50 border border-amber-200 p-3 text-amber-800">
                    <AlertTriangle className="h-5 w-5 shrink-0" />
                    {inApp
                        ? 'You opened Mapleby inside another app (for example Instagram, Facebook or WhatsApp). Apps can’t be installed from here.'
                        : 'This browser can’t install apps.'}
                </p>
                <p className="text-charcoal">
                    {state.browser.iOS
                        ? <>Open this page in <strong>Safari</strong>, sign in, and tap Install again.</>
                        : <>Open this page in <strong>Google Chrome</strong> (or Microsoft Edge), sign in, and tap Install again.</>}
                </p>
                {inApp && <p className="text-pebble">Tip: use the ⋯ menu and choose “Open in browser”.</p>}
            </div>
        );
    }

    const lists = {
        ios: [
            <>Tap <Share className="inline h-4 w-4" /> <strong>Share</strong> at the bottom of Safari (top right on iPad).</>,
            <>Scroll down and tap <PlusSquare className="inline h-4 w-4" /> <strong>Add to Home Screen</strong>, then <strong>Add</strong>.</>,
        ],
        mac_safari: [
            <>In the menu bar, choose <strong>File → Add to Dock</strong>.</>,
            <>Click <strong>Add</strong>.</>,
        ],
        firefox_android: [
            <>Tap the <MoreVertical className="inline h-4 w-4" /> menu.</>,
            <>Tap <strong>Install</strong> (or <strong>Add to Home screen</strong>).</>,
        ],
        chromium_menu: [
            <>Open the browser menu <MoreVertical className="inline h-4 w-4" />.</>,
            <>Choose <strong>Install app</strong> (or <strong>Add to Home screen</strong>). If it isn’t there yet, reload the page once and try again.</>,
        ],
    };
    const steps = lists[state.steps] || lists.chromium_menu;

    return (
        <ol className="space-y-3 text-sm text-charcoal">
            {steps.map((s, i) => (
                <li key={i} className="flex gap-3">
                    <span className="h-6 w-6 shrink-0 rounded-full bg-sage text-white text-xs font-bold flex items-center justify-center">{i + 1}</span>
                    <span className="pt-0.5">{s}</span>
                </li>
            ))}
            {state.browser.iOS && !state.browser.safari && (
                <li className="text-pebble">If you don’t see Add to Home Screen, open this page in Safari.</li>
            )}
        </ol>
    );
}

/**
 * Install button for the staff app. Rendered only inside AppLayout, so only
 * signed-in staff ever see it. variant: 'sidebar' | 'banner'.
 */
export default function InstallAppButton({ variant = 'sidebar' }) {
    const { props } = usePage();
    const state = usePwaInstall();
    const [open, setOpen] = useState(false);
    const [dismissed, setDismissed] = useState(() => {
        try { return localStorage.getItem(DISMISS_KEY) === '1'; } catch (e) { return false; }
    });

    if (!props.auth?.user || state.mode === 'installed') return null;
    if (variant === 'banner' && dismissed) return null;

    const onInstall = async () => {
        if (state.mode === 'prompt') {
            const outcome = await promptInstall();
            if (outcome !== 'unavailable') return;
        }
        setOpen(true);
    };

    const dismiss = () => {
        try { localStorage.setItem(DISMISS_KEY, '1'); } catch (e) { /* storage blocked */ }
        setDismissed(true);
    };

    const warn = state.mode === 'unsupported';

    return (
        <>
            {variant === 'banner' ? (
                <div className={cn('rounded-2xl border p-4 flex items-center gap-3', warn ? 'bg-amber-50 border-amber-200' : 'bg-white border-linen shadow-sm')}>
                    <span className={cn('h-10 w-10 rounded-xl flex items-center justify-center shrink-0', warn ? 'bg-amber-100 text-amber-700' : 'bg-sage-soft text-sage')}>
                        {warn ? <AlertTriangle className="h-5 w-5" /> : <Smartphone className="h-5 w-5" />}
                    </span>
                    <div className="flex-1 min-w-0">
                        <p className="text-sm font-semibold text-void">{warn ? 'This browser can’t install the app' : 'Install Mapleby on this device'}</p>
                        <p className="text-xs text-stone">{warn ? 'Tap to see which browser to use.' : 'Opens full-screen from your home screen, like any app.'}</p>
                    </div>
                    <Button size="sm" onClick={onInstall} className={warn ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'btn-sage'}>
                        {warn ? 'Why?' : <><Download className="mr-1.5 h-4 w-4" /> Install</>}
                    </Button>
                    <button type="button" onClick={dismiss} title="Hide" className="p-1 text-pebble hover:text-stone"><X className="h-4 w-4" /></button>
                </div>
            ) : (
                <button type="button" onClick={onInstall}
                    className={cn('w-full flex items-center gap-2 rounded-xl px-3 py-2 text-[13px] font-medium transition-colors',
                        warn ? 'bg-amber-50 text-amber-800 hover:bg-amber-100' : 'bg-sage-soft text-sage hover:bg-sage/15')}>
                    {warn ? <AlertTriangle className="h-4 w-4" /> : <Download className="h-4 w-4" />}
                    {warn ? 'Can’t install here' : 'Install app'}
                </button>
            )}

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="bg-white max-w-sm">
                    <DialogHeader>
                        <DialogTitle>{warn ? 'Use a different browser' : 'Install Mapleby'}</DialogTitle>
                        <DialogDescription>
                            {warn ? 'Installing needs a browser that supports apps.' : 'Your browser installs apps from its own menu. It takes two taps.'}
                        </DialogDescription>
                    </DialogHeader>
                    <Steps state={state} />
                </DialogContent>
            </Dialog>
        </>
    );
}
