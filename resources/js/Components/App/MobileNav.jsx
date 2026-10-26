import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import {
    LayoutDashboard,
    Users,
    Truck,
    Package,
    Settings
} from 'lucide-react';
import { motion } from 'framer-motion';

const navItems = [
    { label: 'Home', route: 'dashboard', active: 'dashboard', icon: LayoutDashboard },
    { label: 'Clients', route: 'crm.clients.index', active: 'crm.clients.*', icon: Users },
    { label: 'Ship', route: 'operations.shipments.index', active: 'operations.shipments.*', icon: Truck },
    { label: 'Stock', route: 'inventory.vehicles.index', active: 'inventory.*', icon: Package },
    { label: 'Settings', route: 'settings.index', active: 'settings.*', icon: Settings },
];

export default function MobileNav() {
    const { url, props } = usePage();
    const currentRoute = props.ziggy?.location || url;

    const isActive = (pattern) => {
        if (pattern.endsWith('*')) {
            const prefix = pattern.slice(0, -1);
            return currentRoute.includes(prefix.replace(/\./g, '/'));
        }
        return currentRoute === route(pattern);
    };

    return (
        <motion.nav
            initial={{ y: 100, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{
                type: "spring",
                stiffness: 260,
                damping: 25,
                delay: 0.2
            }}
            className="mobile-nav-float"
        >
            {navItems.map((item) => {
                const Icon = item.icon;
                const active = isActive(item.active);

                return (
                    <Link
                        key={item.route}
                        href={route(item.route)}
                        className={cn(
                            "mobile-nav-item relative min-w-[64px]",
                            active ? "text-sage" : "text-pebble hover:text-stone"
                        )}
                    >
                        <div className={cn(
                            "p-1.5 rounded-xl transition-colors duration-200",
                            active && "bg-sage-soft"
                        )}>
                            <Icon className="w-5 h-5" strokeWidth={active ? 2 : 1.5} />
                        </div>
                        <span className="mt-1 text-[10px] font-semibold tracking-wide">{item.label}</span>
                        {active && (
                            <motion.div
                                layoutId="mobile-nav-indicator"
                                className="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-sage"
                                transition={{
                                    type: "spring",
                                    stiffness: 350,
                                    damping: 30
                                }}
                            />
                        )}
                    </Link>
                );
            })}
        </motion.nav>
    );
}
