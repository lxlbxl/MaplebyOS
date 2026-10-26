import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import ContainerForm from '@/Components/Operations/ContainerForm';
import { Plus, Search, Ship, Anchor, Car, ExternalLink } from 'lucide-react';
import { CONTAINER_STATUS_COLORS, formatDate } from '@/lib/operations';
import { cn } from '@/lib/utils';

function daysUntil(date) {
    if (!date) return null;
    const [y, m, d] = date.slice(0, 10).split('-').map(Number);
    return Math.round((new Date(y, m - 1, d) - new Date(new Date().toDateString())) / 86400000);
}

function ContainerCard({ c }) {
    const eta = daysUntil(c.eta);
    const slots = Array.from({ length: Math.max(c.capacity, c.shipments.length) }, (_, i) => c.shipments[i]);
    return (
        <Link href={route('operations.containers.show', c.id)} className="block rounded-xl border border-linen bg-white p-3 shadow-sm hover:shadow-md hover:border-sage/40 transition">
            <div className="flex items-start justify-between gap-2">
                <div>
                    <p className="font-mono text-sm font-semibold text-void">{c.container_number}</p>
                    <p className="text-xs text-pebble">{c.shipping_line?.name || 'Line unknown'}{c.vessel ? ` · ${c.vessel}` : ''}</p>
                </div>
                <span className={cn('text-[11px] font-mono px-1.5 py-0.5 rounded', c.shipments_count > c.capacity ? 'bg-red-50 text-red-700' : 'bg-cream text-stone')}>{c.shipments_count}/{c.capacity}</span>
            </div>
            <div className="mt-2 grid grid-cols-2 gap-1">
                {slots.map((s, i) => (
                    <div key={i} className={cn('rounded-md px-2 py-1 text-[11px] truncate', s ? 'bg-sage/10 text-charcoal' : 'border border-dashed border-linen text-pebble')}>
                        {s ? `${s.vehicle_make || ''} ${s.vehicle_model || ''}`.trim() || s.tracking_id : 'empty'}
                    </div>
                ))}
            </div>
            <div className="mt-2 flex justify-between text-[11px] text-stone">
                <span>Loaded {formatDate(c.loaded_on)}</span>
                {c.offloaded_on ? <span>Offloaded {formatDate(c.offloaded_on)}</span>
                    : eta != null ? <span className={cn(eta < 0 && 'text-red-600 font-medium')}>{eta >= 0 ? `ETA in ${eta}d` : `ETA ${-eta}d ago`}</span> : null}
            </div>
        </Link>
    );
}

export default function Index({ containers, statuses, shippingLines, filters, summary }) {
    const [formOpen, setFormOpen] = useState(false);
    const [search, setSearch] = useState(filters.search || '');

    useEffect(() => {
        const t = setTimeout(() => {
            if (search !== (filters.search || '')) router.get(route('operations.containers.index'), { ...filters, search }, { preserveState: true, replace: true });
        }, 400);
        return () => clearTimeout(t);
    }, [search]);

    const columns = Object.entries(statuses).map(([key, label]) => ({ key, label, items: containers.filter((c) => c.status === key) }));

    return (
        <AppLayout>
            <Head title="Containers" />
            <div className="flex flex-col gap-5">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Containers</h1>
                        <p className="text-drift mt-1">Update a container once and every car inside moves with it.</p>
                    </div>
                    <Button onClick={() => setFormOpen(true)} className="btn-sage shadow-soft"><Plus className="mr-2 h-4 w-4" /> New container</Button>
                </div>

                <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div className="rounded-xl bg-white border border-linen p-4"><p className="text-xs text-pebble flex items-center gap-1"><Ship className="h-3.5 w-3.5" /> On the water</p><p className="text-2xl font-semibold text-void">{summary.in_transit}</p></div>
                    <div className="rounded-xl bg-white border border-linen p-4"><p className="text-xs text-pebble flex items-center gap-1"><Anchor className="h-3.5 w-3.5" /> At Lagos, not released</p><p className="text-2xl font-semibold text-void">{summary.arrived}</p></div>
                    <div className="rounded-xl bg-white border border-linen p-4"><p className="text-xs text-pebble flex items-center gap-1"><Car className="h-3.5 w-3.5" /> Cars in transit</p><p className="text-2xl font-semibold text-void">{summary.cars_in_transit}</p></div>
                    <div className="rounded-xl bg-white border border-linen p-4">
                        <p className="text-xs text-pebble">Average transit (loaded → offloaded)</p>
                        {summary.transit_by_line.length ? summary.transit_by_line.map((l) => (
                            <p key={l.line} className="text-sm text-charcoal flex justify-between"><span>{l.line}</span><span className="font-semibold">{l.avg_days} days</span></p>
                        )) : <p className="text-sm text-pebble">Not enough data yet</p>}
                    </div>
                </div>

                <div className="flex flex-col md:flex-row gap-3 bg-white p-3 rounded-xl border border-linen">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-pebble w-4 h-4" />
                        <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Container, booking, BL, VIN or tracking ID…" className="pl-10 bg-cream border-linen" />
                    </div>
                    <label className="flex items-center gap-2 text-sm text-stone">
                        <input type="checkbox" className="accent-sage" checked={!!filters.include_released}
                            onChange={(e) => router.get(route('operations.containers.index'), { ...filters, include_released: e.target.checked ? 1 : undefined }, { preserveState: true })} />
                        Show all released
                    </label>
                </div>

                <div className="flex gap-4 overflow-x-auto pb-4">
                    {columns.map((col) => (
                        <div key={col.key} className="w-72 shrink-0">
                            <div className="flex items-center justify-between mb-2 px-1">
                                <span className={cn('text-xs font-semibold px-2 py-1 rounded-full', CONTAINER_STATUS_COLORS[col.key])}>{col.label}</span>
                                <span className="text-xs text-pebble">{col.items.length}</span>
                            </div>
                            <div className="space-y-2 max-h-[70vh] overflow-y-auto pr-1">
                                {col.items.map((c) => <ContainerCard key={c.id} c={c} />)}
                                {col.items.length === 0 && <div className="text-xs text-pebble border border-dashed border-linen rounded-xl p-4 text-center">None</div>}
                            </div>
                        </div>
                    ))}
                </div>

                <ContainerForm open={formOpen} onOpenChange={setFormOpen} shippingLines={shippingLines} />
            </div>
        </AppLayout>
    );
}
