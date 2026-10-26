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
    ShieldCheck
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
            { label: 'Shipments', route: 'operations.shipments.index', active: 'operations.shipments.*', icon: Truck },
            { label: 'Quotes', route: 'operations.quotes.index', active: 'operations.quotes.*', icon: FileText },
            { label: 'Inventory', route: 'inventory.vehicles.index', active: 'inventory.*', icon: Package },
        ]
    },
    {
        title: 'Finance',
        items: [
            { label: 'Payments', route: 'finance.payments.index', active: 'finance.*', icon: DollarSign },
        ]
    },
    {
        title: 'System',
        items: [
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
            <div className="p-6 pb-4">
                <Link href={route('dashboard')} className="block group">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-sage to-sage-light flex items-center justify-center shadow-sm">
                            <span className="text-white font-display text-lg font-semibold">M</span>
                        </div>
                        <div>
                            <h1 className="text-lg font-display font-semibold text-void tracking-tight">
                                Mapleby
                            </h1>
                            <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-pebble">
                                Logistics
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            {/* Divider */}
            <div className="mx-5 h-px bg-linen" />

            {/* Navigation Sections */}
            <nav className="flex-1 py-4 overflow-y-auto">
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
                                            "w-[18px] h-[18px] nav-icon transition-colors duration-200",
                                            active ? "text-sage" : "text-pebble group-hover:text-stone"
                                        )} strokeWidth={1.75} />
                                        <span className="flex-1 text-[13px]">{item.label}</span>
                                        {active && (
                                            <motion.div
                                                layoutId="nav-indicator"
                                                className="w-1.5 h-1.5 rounded-full bg-sage"
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
            <div className="border-t border-linen py-3">
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
                                "w-[18px] h-[18px] transition-colors duration-200",
                                active ? "text-sage" : "text-pebble group-hover:text-stone"
                            )} strokeWidth={1.75} />
                            <span className="flex-1 text-[13px]">{item.label}</span>
                        </Link>
                    );
                })}

                {/* User Profile & Logout */}
                <div className="mx-4 mt-3 pt-3 border-t border-linen">
                    <div className="flex items-center gap-3 px-1">
                        <div className="avatar-ring">
                            <div className="w-9 h-9 rounded-full bg-gradient-to-br from-sage-light to-terracotta-light flex items-center justify-center text-white font-semibold text-sm">
                                {user?.name?.charAt(0) || 'U'}
                            </div>
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-void truncate">
                                {user?.name || 'User'}
                            </p>
                            <p className="text-xs text-pebble truncate">
                                {user?.email || 'email@example.com'}
                            </p>
                        </div>
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="p-2 text-pebble hover:text-error hover:bg-error-soft rounded-lg transition-all duration-200"
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
