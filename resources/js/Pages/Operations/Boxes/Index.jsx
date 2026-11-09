import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import Pagination from '@/Components/Pagination';
import { CargoForm, clientName } from '@/Components/Operations/CargoParts';
import { Plus, Search } from 'lucide-react';
import { VEHICLE_STATUS_COLORS, formatDate } from '@/lib/operations';
import { useCan } from '@/lib/access';
import { cn } from '@/lib/utils';

export default function Index({ boxes, filters, statuses, views, formOptions }) {
    const can = useCan();
    const [formOpen, setFormOpen] = useState(false);
    const [search, setSearch] = useState(filters.search || '');
    const view = filters.view || 'open';

    useEffect(() => {
        const t = setTimeout(() => {
            if (search !== (filters.search || '')) router.get(route('operations.boxes.index'), { ...filters, search }, { preserveState: true, replace: true });
        }, 400);
        return () => clearTimeout(t);
    }, [search]);

    return (
        <AppLayout>
            <Head title="Small cargo" />
            <div className="flex flex-col gap-5">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Small cargo</h1>
                        <p className="text-drift mt-1">Boxes belonging to other clients that ride in car containers. They load, sail and clear with the container.</p>
                    </div>
                    {can('cargo.manage') && <Button onClick={() => setFormOpen(true)} className="btn-sage shadow-soft"><Plus className="mr-2 h-4 w-4" /> New box</Button>}
                </div>

                <div className="flex flex-col md:flex-row gap-3 md:items-center">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-pebble" />
                        <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search box ID, contents, owner, container…" className="pl-9 bg-white border-linen h-10" />
                    </div>
                    <div className="flex gap-1 flex-wrap">
                        {Object.entries(views).map(([key, label]) => (
                            <button key={key} onClick={() => router.get(route('operations.boxes.index'), { ...filters, view: key }, { preserveState: true, replace: true })}
                                className={cn('text-xs rounded-full px-3 py-1.5 border', view === key ? 'bg-void text-white border-void' : 'bg-white border-linen text-stone')}>{label}</button>
                        ))}
                    </div>
                </div>

                <div className="rounded-xl border border-linen bg-white overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-linen text-left text-pebble text-xs">
                                <th className="p-3">Box</th><th className="p-3">Contents</th><th className="p-3">Owner</th><th className="p-3">Size</th>
                                <th className="p-3">Container</th><th className="p-3">Added</th><th className="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {boxes.data.map((b) => (
                                <tr key={b.id} className="border-b border-linen last:border-0 hover:bg-cream/50 cursor-pointer" onClick={() => router.visit(route('operations.boxes.show', b.id))}>
                                    <td className="p-3 font-mono text-xs"><Link href={route('operations.boxes.show', b.id)} className="text-sage">{b.tracking_id}</Link></td>
                                    <td className="p-3">{b.cargo_description}</td>
                                    <td className="p-3">{clientName(b.client) || b.consignee || '—'}</td>
                                    <td className="p-3 text-xs">{[b.package_count && `${b.package_count} ${b.package_type || ''}`, b.volume_cbm && `${Number(b.volume_cbm)} CBM`, b.gross_weight_kg && `${Number(b.gross_weight_kg)} kg`].filter(Boolean).join(' · ') || '—'}</td>
                                    <td className="p-3 font-mono text-xs">{b.container?.container_number || <span className="text-pebble font-sans">not loaded</span>}</td>
                                    <td className="p-3 text-xs">{formatDate(b.date_added)}</td>
                                    <td className="p-3"><span className={cn('text-xs px-2 py-1 rounded-full font-medium whitespace-nowrap', VEHICLE_STATUS_COLORS[b.status])}>{b.status_label}</span></td>
                                </tr>
                            ))}
                            {!boxes.data.length && <tr><td colSpan={7} className="p-8 text-center text-pebble">No boxes here yet.</td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination links={boxes.links} />
            </div>

            <CargoForm kind="box" open={formOpen} onOpenChange={setFormOpen} options={formOptions} />
        </AppLayout>
    );
}
