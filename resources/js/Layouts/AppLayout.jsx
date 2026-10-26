import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import Sidebar from '@/Components/App/Sidebar';
import MobileNav from '@/Components/App/MobileNav';
import { Toaster } from "@/Components/ui/sonner";
import { toast } from "sonner";
import { motion, AnimatePresence } from 'framer-motion';

export default function AppLayout({ children }) {
    const { flash } = usePage().props;

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash]);

    return (
        <div className="app-container">
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
                        background: '#faf9f6',
                        color: '#2d2b26',
                        border: '1px solid #e8e4db',
                        borderRadius: '12px',
                        boxShadow: '0 8px 32px rgba(26, 25, 21, 0.08)',
                        padding: '16px 20px',
                    },
                }}
            />
        </div>
    );
}
