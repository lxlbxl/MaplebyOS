import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { LogOut } from 'lucide-react';
import { motion } from 'framer-motion';
import { useCan } from '@/lib/access';
import { NAV_SECTIONS, BOTTOM_ITEMS, allowed, isActivePattern } from '@/lib/nav';
import InstallAppButton from '@/Components/App/InstallAppButton';

export default function Sidebar() {
    const { url, props } = usePage();
    const currentRoute = props.ziggy?.location || url;
    const user = props.auth?.user;
    const can = useCan();
    const isActive = (pattern) => isActivePattern(pattern, currentRoute);
    const navSections = NAV_SECTIONS
        .map((section) => ({ ...section, items: section.items.filter((item) => allowed(item, can)) }))
        .filter((section) => section.items.length > 0);
    const bottomItems = BOTTOM_ITEMS.filter((item) => allowed(item, can));

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
                <div className="px-4 pb-2"><InstallAppButton variant="sidebar" /></div>
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
                                {props.auth?.role || user?.email}
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
