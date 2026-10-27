import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Metric tile in the dashboard's style. `dark` gives the hero (Total Revenue)
 * treatment; the default is the light Shipments-card look.
 * tone: 'default' | 'alert' | 'success' colours the value on light cards.
 */
export default function StatCard({ icon: Icon, label, value, hint, dark = false, tone = 'default', href, className, children }) {
    return (
        <div className={cn(dark ? 'card-void !p-6' : 'card-serene !p-6', 'relative group flex flex-col min-w-0', className)}>
            <div className="relative z-10 flex items-center justify-between gap-3 mb-4">
                <div className="flex items-center gap-3 min-w-0">
                    {Icon && (
                        <div className={cn('w-10 h-10 shrink-0 rounded-xl flex items-center justify-center',
                            dark ? 'bg-white/10 backdrop-blur-sm' : tone === 'alert' ? 'bg-sage-soft' : tone === 'success' ? 'bg-success-soft' : 'bg-cream')}>
                            <Icon className={cn('w-5 h-5', dark ? 'text-sage-light' : tone === 'alert' ? 'text-sage' : tone === 'success' ? 'text-success' : 'text-stone')} strokeWidth={1.5} />
                        </div>
                    )}
                    <span className={cn('metric-label truncate', dark && 'text-sand')}>{label}</span>
                </div>
                {href && (
                    <Link href={href} className={cn('transition-colors', dark ? 'text-sand/60 hover:text-cream' : 'text-pebble hover:text-sage')}>
                        <ChevronRight className="w-5 h-5" />
                    </Link>
                )}
            </div>
            {value !== undefined && (
                <p className={cn('relative z-10 metric-value-sm', String(value).length > 11 && '!text-[1.5rem] leading-tight break-words',
                    dark ? '!text-cream' : tone === 'alert' ? '!text-sage' : tone === 'success' ? '!text-success' : '')}>
                    {value}
                </p>
            )}
            {hint && <p className={cn('relative z-10 text-sm mt-2', dark ? 'text-sand/60' : 'text-drift')}>{hint}</p>}
            {children && <div className="relative z-10 mt-3">{children}</div>}
            {dark && <div className="absolute -top-24 -right-24 w-48 h-48 bg-sage/20 rounded-full blur-3xl opacity-60 group-hover:opacity-100 transition-opacity duration-1000" />}
        </div>
    );
}
