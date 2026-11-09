import { useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import Sidebar from '@/Components/App/Sidebar';
import MobileNav from '@/Components/App/MobileNav';
import { Toaster } from "@/Components/ui/sonner";
import { toast } from "sonner";
import { motion, AnimatePresence } from 'framer-motion';
import { enableStaffApp } from '@/lib/pwa';

export default function AppLayout({ children }) {
    const { flash, auth } = usePage().props;

    // Only signed-in staff get the installable app (manifest + service worker).
    useEffect(() => { enableStaffApp(); }, []);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash]);

    return (
        <div className="app-container">
            {/* Brand bar - Mobile */}
            <header className="mobile-topbar">
                <Link href={route('dashboard')} className="brand-lockup">
                    <img src="/brand/badge-96.png" alt="Mapleby" width={38} height={38} className="brand-badge" />
                    <div>
                        <p className="brand-wordmark text-lg text-white">Mapleby</p>
                        <p className="brand-tagline !mt-1">Logistics OS</p>
                    </div>
                </Link>
                <div className="flex items-center gap-2">
                    <span className="w-9 h-9 rounded-full bg-gradient-to-br from-maple to-maple-deep ring-2 ring-gold/40 flex items-center justify-center text-sm font-semibold">
                        {auth?.user?.name?.charAt(0) || 'U'}
                    </span>
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        aria-label="Sign out"
                        className="w-9 h-9 flex items-center justify-center rounded-full text-[#B9BBBE] hover:text-white hover:bg-white/10 transition"
                    >
                        <LogOut className="w-4 h-4" strokeWidth={1.75} />
                    </Link>
                </div>
            </header>

            {/* Floating Sidebar - Desktop */}
            <Sidebar />

            {/* Main Content Area */}
            <main className="content-area">
                <div className="content-inner texture-grain">
                    <AnimatePresence mode="wait">
                        <motion.div
                            key={usePage().url}
                            initial={{ opacity: 0, y: 8 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -8 }}
                            transition={{
                                duration: 0.4,
                                ease: [0.25, 0.46, 0.45, 0.94]
                            }}
                            className="min-h-full"
                        >
                            {children}
                        </motion.div>
                    </AnimatePresence>
                </div>
            </main>

            {/* Floating Mobile Navigation */}
            <MobileNav />

            {/* Toast Notifications - Refined */}
            <Toaster
                position="bottom-right"
                toastOptions={{
                    className: 'font-sans',
                    style: {
                        background: '#FFFFFF',
                        color: '#242424',
                        border: '1px solid #DDDEDF',
                        borderLeft: '3px solid #ED1722',
                        borderRadius: '12px',
                        boxShadow: '0 8px 32px rgba(23, 24, 25, 0.10)',
                        padding: '16px 20px',
                    },
                }}
            />
        </div>
    );
}
