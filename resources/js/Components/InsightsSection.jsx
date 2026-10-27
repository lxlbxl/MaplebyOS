import { useState, useEffect, useRef } from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';
import { Activity } from 'lucide-react';

// Brand palette, ordered so neighbouring slices stay distinguishable:
// Maple Red, Charcoal, Champagne Gold, Deep Maple, Slate, Soft red, Graphite, Light gold.
const COLORS = ['#ED1722', '#242424', '#C5A46D', '#A80F1B', '#8C8F94', '#F4959A', '#393B3E', '#DCC499'];

const pretty = (name) => String(name).replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

export default function InsightsSection({
    stats = [], // Array of { name: 'Status', value: 10 }
    title = 'Overview',
    description = 'Distribution by status',
    totalLabel = 'Total Records',
    icon: Icon = Activity,
    compact = false, // breakdown card only, for pages that already show the total
}) {
    const [isReady, setIsReady] = useState(false);
    const containerRef = useRef(null);

    // Defer chart rendering to avoid width(-1) crash during initial layout
    useEffect(() => {
        const el = containerRef.current;
        if (!el) return;
        if (el.offsetWidth > 0) {
            setIsReady(true);
            return;
        }
        const observer = new ResizeObserver(() => {
            if (el.offsetWidth > 0) {
                setIsReady(true);
                observer.disconnect();
            }
        });
        observer.observe(el);
        return () => observer.disconnect();
    }, []);

    const data = stats
        .map((s) => ({ name: pretty(s.name), value: parseInt(s.value, 10) || 0 }))
        .filter((s) => s.value > 0)
        .sort((a, b) => b.value - a.value)
        .map((s, i) => ({ ...s, color: COLORS[i % COLORS.length] }));
    const total = data.reduce((sum, item) => sum + item.value, 0);
    const pct = (v) => (total ? Math.round((v / total) * 100) : 0);
    const lead = data[0];

    return (
        <div className={compact ? 'h-full' : 'grid grid-cols-1 lg:grid-cols-12 gap-5 mb-8'}>
            {/* Hero total, styled like the dashboard's revenue card */}
            {!compact && <div className="lg:col-span-4 card-void p-8 relative group flex flex-col justify-between min-h-[14rem]">
                <div className="relative z-10">
                    <div className="flex items-center gap-3 mb-8">
                        <div className="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <Icon className="w-5 h-5 text-sage-light" strokeWidth={1.5} />
                        </div>
                        <span className="metric-label text-sand">{totalLabel}</span>
                    </div>
                    <p className="metric-value !text-cream mb-5">{total.toLocaleString()}</p>
                    {lead ? (
                        <div className="flex flex-wrap items-center gap-3">
                            <span className="flex items-center gap-1.5 text-sage-light text-sm font-medium bg-sage/20 px-2.5 py-1 rounded-full">
                                {lead.name} · {pct(lead.value)}%
                            </span>
                            <span className="text-sand/40 text-sm">largest group</span>
                        </div>
                    ) : (
                        <span className="text-sand/60 text-sm">Nothing recorded yet</span>
                    )}
                </div>

                {/* Proportion strip */}
                {total > 0 && (
                    <div className="relative z-10 mt-8 flex h-2 rounded-full overflow-hidden bg-white/10 gap-0.5">
                        {data.map((s) => (
                            <div key={s.name} title={`${s.name}: ${s.value}`} style={{ width: `${(s.value / total) * 100}%`, backgroundColor: s.color === '#242424' ? '#62656A' : s.color }} />
                        ))}
                    </div>
                )}

                {/* Ambient glow */}
                <div className="absolute -top-32 -right-32 w-64 h-64 bg-sage/20 rounded-full blur-3xl opacity-60 group-hover:opacity-100 transition-opacity duration-1000" />
                <div className="absolute -bottom-20 -left-20 w-48 h-48 bg-terracotta/10 rounded-full blur-3xl opacity-40" />
            </div>}

            {/* Breakdown */}
            <div className={compact ? 'card-serene p-7 flex flex-col h-full' : 'lg:col-span-8 card-serene p-7 flex flex-col'}>
                <div className="mb-5">
                    <h2 className="text-display-md text-void">{title}</h2>
                    <p className="text-caption mt-1">{description}</p>
                </div>

                <div className="flex flex-col sm:flex-row items-center gap-6 flex-1">
                    <div ref={containerRef} className="relative w-full sm:w-48 shrink-0" style={{ height: 184 }}>
                        {isReady ? (
                            <ResponsiveContainer width="100%" height={184}>
                                <PieChart>
                                    <Pie
                                        data={total ? data : [{ name: 'None', value: 1, color: '#F3F3F1' }]}
                                        cx="50%" cy="50%" innerRadius={62} outerRadius={84}
                                        paddingAngle={total && data.length > 1 ? 2 : 0} cornerRadius={4}
                                        dataKey="value" stroke="none" startAngle={90} endAngle={-270} animationDuration={500}
                                    >
                                        {(total ? data : [{ color: '#F3F3F1' }]).map((entry, index) => (
                                            <Cell key={index} fill={entry.color} />
                                        ))}
                                    </Pie>
                                    {total > 0 && (
                                        <Tooltip
                                            formatter={(v, n) => [`${v} (${pct(v)}%)`, n]}
                                            contentStyle={{ borderRadius: 12, border: '1px solid #DDDEDF', boxShadow: '0 4px 12px rgba(23,24,25,0.08)', fontSize: 13 }}
                                        />
                                    )}
                                </PieChart>
                            </ResponsiveContainer>
                        ) : (
                            <div className="w-full h-full flex items-center justify-center">
                                <div className="w-36 h-36 rounded-full border-[20px] border-cream animate-pulse" />
                            </div>
                        )}
                        <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span className="metric-value-sm">{total}</span>
                            <span className="metric-label !text-[0.6rem] mt-1">Total</span>
                        </div>
                    </div>

                    <div className="w-full grid grid-cols-1 sm:grid-cols-2 gap-2">
                        {data.map((s) => (
                            <div key={s.name} className="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl bg-cream hover:bg-linen transition-colors">
                                <div className="flex items-center gap-2 min-w-0">
                                    <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: s.color }} />
                                    <span className="text-sm font-medium text-charcoal truncate">{s.name}</span>
                                </div>
                                <span className="flex items-baseline gap-1.5 shrink-0">
                                    <span className="hidden sm:inline text-xs text-pebble">{pct(s.value)}%</span>
                                    <span className="text-xs font-bold text-void bg-white px-2 py-0.5 rounded-md shadow-whisper">{s.value}</span>
                                </span>
                            </div>
                        ))}
                        {total === 0 && <p className="text-sm text-pebble col-span-2">No data yet.</p>}
                    </div>
                </div>
            </div>
        </div>
    );
}
