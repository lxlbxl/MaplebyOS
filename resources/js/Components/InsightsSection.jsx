import { useState, useEffect, useRef } from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';
import { cn } from '@/lib/utils';
import { TrendingUp, Activity } from 'lucide-react';

const COLORS = ['#8BA896', '#EBCBAE', '#D18F84', '#6B7280', '#A5B4FC']; // Sage, Cream-Dark, Coral, Gray, Indigo

export default function InsightsSection({
    stats = [], // Array of { name: 'Status', value: 10 }
    title = 'Overview',
    description = 'Distribution by status',
    totalLabel = 'Total Records'
}) {
    const [isReady, setIsReady] = useState(false);
    const containerRef = useRef(null);

    // Defer chart rendering to avoid width(-1) crash during initial layout
    useEffect(() => {
        const timer = setTimeout(() => {
            if (containerRef.current && containerRef.current.offsetWidth > 0) {
                setIsReady(true);
            }
        }, 100);
        return () => clearTimeout(timer);
    }, []);

    // Ensure values are numbers and calculate total
    const normalizedStats = stats.map(s => ({
        name: s.name,
        value: parseInt(s.value, 10) || 0
    }));
    const total = normalizedStats.reduce((sum, item) => sum + item.value, 0);

    // Prepare data for rendering - ensure we don't crash on empty stats
    const data = normalizedStats.length > 0 ? normalizedStats : [{ name: 'No Data', value: 1 }];
    const isPlaceholder = normalizedStats.length === 0;

    return (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {/* Primary Metric Card */}
            <div className="bg-void rounded-2xl p-8 text-cream shadow-gentle flex flex-col justify-between relative overflow-hidden group border border-charcoal/5">
                <div className="relative z-10">
                    <div className="flex items-center gap-3 text-pebble mb-2">
                        <Activity className="w-5 h-5" />
                        <span className="text-sm font-medium uppercase tracking-wider">{totalLabel}</span>
                    </div>
                    <span className="text-5xl font-serif font-medium text-white">{total}</span>
                </div>

                <div className="relative z-10 mt-8 flex items-center gap-2 text-cream text-sm">
                    <div className="bg-white/5 px-3 py-1.5 rounded-full flex items-center gap-2 backdrop-blur-sm border border-white/10">
                        <TrendingUp className="w-3 h-3 text-sage" />
                        <span>Active Tracking</span>
                    </div>
                </div>

                {/* Decorative background */}
                <div className="absolute top-0 right-0 w-40 h-40 bg-sage/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:bg-sage/10 transition-colors duration-500"></div>
            </div>

            {/* Chart Card */}
            <div className="md:col-span-2 bg-white rounded-2xl p-6 border border-linen shadow-sm flex flex-col md:flex-row items-center gap-8">
                <div
                    ref={containerRef}
                    className="flex-1 w-full"
                    style={{ height: '200px', minHeight: '200px', minWidth: '200px' }}
                >
                    {isReady ? (
                        <ResponsiveContainer width="100%" height={200}>
                            <PieChart>
                                <Pie
                                    data={data}
                                    cx="50%"
                                    cy="50%"
                                    innerRadius={60}
                                    outerRadius={80}
                                    paddingAngle={5}
                                    dataKey="value"
                                    stroke="none"
                                >
                                    {data.map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={isPlaceholder ? '#f3f4f6' : COLORS[index % COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip
                                    contentStyle={{ borderRadius: '12px', border: 'none', boxShadow: '0 4px 12px rgba(0,0,0,0.1)' }}
                                />
                            </PieChart>
                        </ResponsiveContainer>
                    ) : (
                        <div className="w-full h-full flex items-center justify-center">
                            <div className="w-32 h-32 rounded-full border-4 border-linen animate-pulse"></div>
                        </div>
                    )}
                </div>

                <div className="flex-1 w-full">
                    <h3 className="text-xl font-serif text-charcoal mb-1">{title}</h3>
                    <p className="text-pebble text-sm mb-6">{description}</p>

                    <div className="grid grid-cols-2 gap-4">
                        {normalizedStats.map((stat, index) => (
                            <div key={stat.name} className="flex items-center justify-between p-3 bg-cream/50 border border-linen rounded-xl">
                                <div className="flex items-center gap-2">
                                    <div
                                        className="w-3 h-3 rounded-full"
                                        style={{ backgroundColor: COLORS[index % COLORS.length] }}
                                    />
                                    <span className="text-sm font-medium text-stone capitalize">
                                        {stat.name.replace('_', ' ')}
                                    </span>
                                </div>
                                <span className="font-bold text-charcoal">{stat.value}</span>
                            </div>
                        ))}
                        {isPlaceholder && (
                            <p className="text-sm text-pebble italic col-span-2">No data available for charts.</p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

