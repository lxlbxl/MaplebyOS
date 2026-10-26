import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import {
    LayoutDashboard,
    Users,
    Package,
    FileText,
    Settings,
    User,
    LogOut,
    DollarSign,
    Truck,
    Sparkles,
    ShieldCheck,
    Car,
    Container,
    BookOpen,
    Receipt
} from 'lucide-react';
import { motion } from 'framer-motion';

const navSections = [
    {
        title: 'Overview',
        items: [
            { label: 'Dashboard', route: 'dashboard', active: 'dashboard', icon: LayoutDashboard },
        ]
    },
    {
        title: 'Business',
        items: [
            { label: 'Clients', route: 'crm.clients.index', active: 'crm.clients.*', icon: Users },
            { label: 'Leads', route: 'crm.leads.index', active: 'crm.leads.*', icon: Sparkles },
        ]
    },
    {
        title: 'Operations',
        items: [
            { label: 'Vehicles', route: 'operations.shipments.index', active: 'operations.shipments.*', icon: Car },
            { label: 'Containers', route: 'operations.containers.index', active: 'operations.containers.*', icon: Container },
            { label: 'Quotes', route: 'operations.quotes.index', active: 'operations.quotes.*', icon: FileText },
            { label: 'Inventory', route: 'inventory.vehicles.index', active: 'inventory.*', icon: Package },
        ]
    },
    {
        title: 'Finance',
        items: [
            { label: 'Invoices', route: 'finance.invoices.index', active: 'finance.invoices.*', icon: Receipt },
            { label: 'Payments', route: 'finance.payments.index', active: 'finance.payments.*', icon: DollarSign },
        ]
    },
    {
        title: 'System',
        items: [
            { label: 'Catalog', route: 'operations.catalog.index', active: 'operations.catalog.*', icon: BookOpen },
            { label: 'User Management', route: 'users.index', active: 'users.*', icon: ShieldCheck },
        ]
    },
];

const bottomItems = [
    { label: 'Settings', route: 'settings.index', active: 'settings.*', icon: Settings },
    { label: 'Profile', route: 'profile.edit', active: 'profile.*', icon: User },
];

export default function Sidebar() {
    const { url, props } = usePage();
    const currentRoute = props.ziggy?.location || url;
    const user = props.auth?.user;

    const isActive = (pattern) => {
        if (pattern.endsWith('*')) {
            const prefix = pattern.slice(0, -1);
            return currentRoute.includes(prefix.replace(/\./g, '/'));
        }
        return currentRoute === route(pattern);
    };

    return (
        <aside className="sidebar-float scrollbar-thin">
            {/* Brand Header */}
            <div className="p-6 pb-5">
                <Link href={route('dashboard')} className="brand-lockup">
                    <img src="/brand/badge-96.png" srcSet="/brand/badge-96.png 1x, /brand/badge-192.png 2x" alt="Mapleby" width={46} height={46} className="brand-badge" />
                    <div>
                        <p className="brand-wordmark text-[1.35rem] text-white">Mapleby</p>
                        <p className="brand-tagline">Logistics OS</p>
                    </div>
                </Link>
            </div>

            <div className="sidebar-divider" />

            {/* Navigation Sections */}
            <nav className="relative z-10 flex-1 py-4 overflow-y-auto scrollbar-thin">
                {navSections.map((section, sectionIdx) => (
                    <div key={section.title} className={cn(sectionIdx > 0 && "mt-6")}>
                        <p className="nav-section-label">
                            {section.title}
                        </p>
                        <div className="space-y-0.5">
                            {section.items.map((item) => {
                                const Icon = item.icon;
                                const active = isActive(item.active);

                                return (
                                    <Link
                                        key={item.route}
                                        href={route(item.route)}
                                        className={cn(
                                            "nav-item group",
                                            active && "active"
                                        )}
                                    >
                                        <Icon className={cn(
                                            "w-[18px] h-[18px] nav-icon transition-colors duration-200"
                                        )} strokeWidth={1.75} />
                                        <span className="flex-1 text-[13px]">{item.label}</span>
                                        {active && (
                                            <motion.div
                                                layoutId="nav-indicator"
                                                className="w-1.5 h-1.5 rounded-full bg-maple shadow-[0_0_8px_rgba(237,23,34,0.8)]"
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
                        </div>
                    </div>
                ))}
            </nav>

            {/* Bottom Section */}
            <div className="relative z-10 border-t border-white/10 py-3">
                {bottomItems.map((item) => {
                    const Icon = item.icon;
                    const active = isActive(item.active);

                    return (
                        <Link
                            key={item.route}
                            href={route(item.route)}
                            className={cn("nav-item group", active && "active")}
                        >
                            <Icon className={cn(
                                "w-[18px] h-[18px] transition-colors duration-200"
                            )} strokeWidth={1.75} />
                            <span className="flex-1 text-[13px]">{item.label}</span>
                        </Link>
                    );
                })}

                {/* User Profile & Logout */}
                <div className="mx-4 mt-3 pt-3 border-t border-white/10">
                    <div className="flex items-center gap-3 px-1">
                        <div className="avatar-ring">
                            <div className="w-9 h-9 rounded-full bg-gradient-to-br from-maple to-maple-deep ring-2 ring-gold/40 flex items-center justify-center text-white font-semibold text-sm">
                                {user?.name?.charAt(0) || 'U'}
                            </div>
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-white truncate">
                                {user?.name || 'User'}
                            </p>
                            <p className="text-xs text-[#8C8F94] truncate">
                                {user?.email || 'email@example.com'}
                            </p>
                        </div>
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="p-2 text-[#8C8F94] hover:text-white hover:bg-maple rounded-lg transition-all duration-200"
                            title="Sign out"
                        >
                            <LogOut className="w-4 h-4" strokeWidth={1.75} />
                        </Link>
                    </div>
                </div>
            </div>
        </aside>
    );
}
