import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import Pagination from '@/Components/Pagination';
import { CargoForm, clientName } from '@/Components/Operations/CargoParts';
import { Plus, Search } from 'lucide-react';
import { CLEARING_STATUS_COLORS, formatDate } from '@/lib/operations';
import { useCan } from '@/lib/access';
import { cn } from '@/lib/utils';

export default function Index({ jobs, filters, statuses, views, counts, formOptions }) {
    const can = useCan();
    const [formOpen, setFormOpen] = useState(false);
    const [search, setSearch] = useState(filters.search || '');
    const view = filters.view || 'open';

    useEffect(() => {
        const t = setTimeout(() => {
            if (search !== (filters.search || '')) router.get(route('operations.clearing.index'), { ...filters, search }, { preserveState: true, replace: true });
        }, 400);
        return () => clearTimeout(t);
    }, [search]);

    const go = (params) => router.get(route('operations.clearing.index'), { ...filters, ...params }, { preserveState: true, replace: true });

    return (
        <AppLayout>
            <Head title="Clearing jobs" />
            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-5">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Clearing</h1>
                        <p className="text-drift mt-1">Clients' general cargo containers cleared through Nigerian customs. One job per Bill of Lading.</p>
                    </div>
                    {can('cargo.manage') && <Button onClick={() => setFormOpen(true)} className="btn-sage shadow-soft"><Plus className="mr-2 h-4 w-4" /> New clearing job</Button>}
                </div>

                <div className="flex gap-2 overflow-x-auto pb-1">
                    {Object.entries(statuses).map(([key, label]) => (
                        <button key={key} onClick={() => go({ status: filters.status === key ? 'all' : key, view: 'all' })}
                            className={cn('shrink-0 rounded-lg border px-3 py-2 text-left', filters.status === key ? 'border-sage bg-sage/10' : 'border-linen bg-white')}>
                            <p className="text-[11px] text-pebble whitespace-nowrap">{label}</p>
                            <p className="text-lg font-semibold text-void">{counts[key] || 0}</p>
                        </button>
                    ))}
                </div>

                <div className="flex flex-col md:flex-row gap-3 md:items-center">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-pebble" />
                        <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search job, BL, Form M, container, client…" className="pl-9 bg-white border-linen h-10" />
                    </div>
                    <div className="flex gap-1 flex-wrap">
                        {Object.entries(views).map(([key, label]) => (
                            <button key={key} onClick={() => go({ view: key, status: 'all' })}
                                className={cn('text-xs rounded-full px-3 py-1.5 border', view === key ? 'bg-void text-white border-void' : 'bg-white border-linen text-stone')}>{label}</button>
                        ))}
                    </div>
                </div>

                <div className="rounded-xl border border-linen bg-white overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-linen text-left text-pebble text-xs">
                                <th className="p-3">Job</th><th className="p-3">Cargo</th><th className="p-3">Client</th><th className="p-3">BL</th>
                                <th className="p-3">Containers</th><th className="p-3">Terminal</th><th className="p-3">ETA / arrived</th><th className="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {jobs.data.map((j) => (
                                <tr key={j.id} className="border-b border-linen last:border-0 hover:bg-cream/50 cursor-pointer" onClick={() => router.visit(route('operations.clearing.show', j.id))}>
                                    <td className="p-3 font-mono text-xs"><Link href={route('operations.clearing.show', j.id)} className="text-sage">{j.tracking_id}</Link></td>
                                    <td className="p-3">{j.cargo_description}{j.package_count ? <span className="text-pebble"> · {j.package_count} {j.package_type || ''}</span> : null}</td>
                                    <td className="p-3">{clientName(j.client) || j.consignee || '—'}</td>
                                    <td className="p-3 font-mono text-xs">{j.bl_number || '—'}</td>
                                    <td className="p-3 font-mono text-xs">
                                        {j.cargo_containers?.length ? j.cargo_containers.map((c) => <div key={c.id}>{c.container_number}{c.size_type ? <span className="text-pebble"> {c.size_type}</span> : null}</div>) : <span className="text-pebble">none yet</span>}
                                    </td>
                                    <td className="p-3 text-xs">{j.terminal || '—'}</td>
                                    <td className="p-3 text-xs">{j.arrived_on ? `Arrived ${formatDate(j.arrived_on)}` : j.eta ? `ETA ${formatDate(j.eta)}` : '—'}</td>
                                    <td className="p-3"><span className={cn('text-xs px-2 py-1 rounded-full font-medium whitespace-nowrap', CLEARING_STATUS_COLORS[j.status])}>{j.status_label}</span></td>
                                </tr>
                            ))}
                            {!jobs.data.length && <tr><td colSpan={8} className="p-8 text-center text-pebble">No clearing jobs here yet.</td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination links={jobs.links} />
            </div>

            <CargoForm kind="clearing" open={formOpen} onOpenChange={setFormOpen} options={formOptions} />
        </AppLayout>
    );
}
