import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { motion } from 'framer-motion';
import { useCan } from '@/lib/access';
import { mobileTabsFor, isActivePattern } from '@/lib/nav';

/**
 * Bottom tab bar on phones. Each role gets its own tabs (see MOBILE_TABS),
 * filtered by what the person is allowed to open.
 */
export default function MobileNav() {
    const { url, props } = usePage();
    const currentRoute = props.ziggy?.location || url;
    const can = useCan();
    const tabs = mobileTabsFor(props.auth?.home, can);

    return (
        <motion.nav
            initial={{ y: 100, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ type: 'spring', stiffness: 260, damping: 25, delay: 0.2 }}
            className="mobile-nav-float"
        >
            {tabs.map((item) => {
                const Icon = item.icon;
                const active = isActivePattern(item.active, currentRoute);

                return (
                    <Link
                        key={item.label}
                        href={route(item.route, item.params || {})}
                        className={cn(
                            'mobile-nav-item relative min-w-[64px]',
                            item.primary ? 'text-white' : active ? 'text-sage' : 'text-pebble hover:text-stone'
                        )}
                    >
                        <div className={cn(
                            'p-1.5 rounded-xl transition-colors duration-200',
                            item.primary ? 'bg-sage shadow-soft' : active && 'bg-sage-soft'
                        )}>
                            <Icon className="w-5 h-5" strokeWidth={active || item.primary ? 2 : 1.5} />
                        </div>
                        <span className={cn('mt-1 text-[10px] font-semibold tracking-wide', item.primary && 'text-sage')}>{item.label}</span>
                        {active && (
                            <motion.div
                                layoutId="mobile-nav-indicator"
                                className="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-sage"
                                transition={{ type: 'spring', stiffness: 350, damping: 30 }}
                            />
                        )}
                    </Link>
                );
            })}
        </motion.nav>
    );
}
