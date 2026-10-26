import { useEffect, useMemo, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Card, CardContent } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/Components/ui/dialog";
import Pagination from '@/Components/Pagination';
import ShipmentForm from '@/Components/Operations/ShipmentForm';
import StatusSelect from '@/Components/StatusSelect';
import { Plus, Upload, Search, X, ArrowUpDown, Container as ContainerIcon, AlertTriangle } from 'lucide-react';
import { VEHICLE_STATUS_COLORS, statusOptions, formatDate, todayIso } from '@/lib/operations';
import { cn } from '@/lib/utils';

function useFilters(filters) {
    const apply = (changes) => {
        const next = { ...filters, ...changes };
        Object.keys(next).forEach((k) => (next[k] === '' || next[k] == null || next[k] === 'all') && k !== 'view' && delete next[k]);
        router.get(route('operations.shipments.index'), next, { preserveState: true, preserveScroll: true, replace: true });
    };
    return apply;
}

function ImportDialog({ open, onOpenChange }) {
    const { flash } = usePage().props;
    const report = flash?.importReport;
    const { data, setData, post, processing, errors, reset, transform } = useForm({ file: null });

    const run = (confirm) => {
        transform((d) => ({ ...d, confirm: confirm ? 1 : 0 }));
        post(route('operations.shipments.import'), {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => confirm && reset(),
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="bg-white max-w-2xl max-h-[85vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Import tracking sheet</DialogTitle>
                    <DialogDescription>
                        Upload the CSV export of “MAPLEBY TRACKING SYSTEM - SHIPMENT”. Run a check first: nothing is saved until you confirm.
                        Re-importing updates cars with the same VIN instead of duplicating them.
                    </DialogDescription>
                </DialogHeader>
                <div className="space-y-4">
                    <Input type="file" accept=".csv,text/csv" onChange={(e) => setData('file', e.target.files[0])} className="bg-cream" />
                    {errors.file && <p className="text-sm text-red-600">{errors.file}</p>}
                    <div className="flex gap-2">
                        <Button type="button" variant="outline" disabled={!data.file || processing} onClick={() => run(false)}>Check file</Button>
                        <Button type="button" className="btn-sage" disabled={!data.file || processing || !report?.dry_run} onClick={() => run(true)}>Import for real</Button>
                    </div>

                    {report && (
                        <div className="rounded-xl border border-linen p-4 space-y-3 text-sm">
                            <p className="font-semibold text-void">
                                {report.dry_run ? 'Check result (not saved)' : 'Imported'}: {report.rows} rows · {report.created} new · {report.updated} updated · {report.containers_created} containers
                            </p>
                            <details open>
                                <summary className="cursor-pointer text-sage font-medium">Fixed automatically ({report.fixes.length})</summary>
                                <ul className="mt-2 max-h-48 overflow-y-auto text-xs text-stone space-y-0.5">
                                    {report.fixes.map((f, i) => <li key={i}>{f}</li>)}
                                </ul>
                            </details>
                            <details>
                                <summary className="cursor-pointer text-amber-700 font-medium">Needs a look ({report.warnings.length})</summary>
                                <ul className="mt-2 max-h-48 overflow-y-auto text-xs text-stone space-y-0.5">
                                    {report.warnings.map((w, i) => <li key={i}>{w}</li>)}
                                </ul>
                            </details>
                        </div>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}

export default function Index({ shipments, filters, statuses, views, shippingLines, openContainers, users, stats }) {
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [importOpen, setImportOpen] = useState(false);
    const [selected, setSelected] = useState([]);
    const [search, setSearch] = useState(filters.search || '');
    const [bulkStatus, setBulkStatus] = useState('');
    const [bulkDate, setBulkDate] = useState(todayIso());
    const [bulkContainer, setBulkContainer] = useState('');
    const apply = useFilters(filters);
    const { flash } = usePage().props;

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        if (params.get('create') === 'true') {
            setEditing(null);
            setFormOpen(true);
        }
    }, []);

    useEffect(() => { if (flash?.importReport) setImportOpen(true); }, [flash?.importReport]);

    useEffect(() => {
        const t = setTimeout(() => { if (search !== (filters.search || '')) apply({ search, page: undefined }); }, 400);
        return () => clearTimeout(t);
    }, [search]);

    useEffect(() => setSelected([]), [shipments.current_page, filters]);

    const rows = shipments.data;
    const allSelected = rows.length > 0 && rows.every((r) => selected.includes(r.id));
    const toggleAll = () => setSelected(allSelected ? [] : rows.map((r) => r.id));
    const toggle = (id) => setSelected((s) => (s.includes(id) ? s.filter((x) => x !== id) : [...s, id]));

    const sortBy = (column) => apply({ sort: column, direction: filters.sort === column && filters.direction !== 'asc' ? 'asc' : 'desc' });

    const runBulk = (payload) => router.post(route('operations.shipments.bulk'), { ids: selected, ...payload }, { preserveScroll: true, onSuccess: () => setSelected([]) });

    const total = useMemo(() => stats.reduce((sum, s) => sum + Number(s.value), 0), [stats]);

    const SortHead = ({ column, children, className }) => (
        <TableHead className={cn('text-drift font-semibold whitespace-nowrap', className)}>
            <button type="button" onClick={() => sortBy(column)} className="inline-flex items-center gap-1 hover:text-void">
                {children} <ArrowUpDown className={cn('h-3 w-3', filters.sort === column ? 'text-sage' : 'text-pebble')} />
            </button>
        </TableHead>
    );

    return (
        <AppLayout>
            <Head title="Vehicles" />

            <div className="flex flex-col gap-5">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Vehicles</h1>
                        <p className="text-drift mt-1">Every car from intake to delivery. {total} in total.</p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={() => setImportOpen(true)} className="border-linen"><Upload className="mr-2 h-4 w-4" /> Import sheet</Button>
                        <Button onClick={() => { setEditing(null); setFormOpen(true); }} className="btn-sage shadow-soft"><Plus className="mr-2 h-4 w-4" /> Add vehicle</Button>
                    </div>
                </div>

                {/* Status counts double as quick filters */}
                <div className="flex flex-wrap gap-2">
                    {stats.map((s) => {
                        const key = Object.keys(statuses).find((k) => statuses[k] === s.name) || s.name;
                        const active = filters.status === key;
                        return (
                            <button key={s.name} type="button" onClick={() => apply({ status: active ? '' : key, page: undefined })}
                                className={cn('px-3 py-1.5 rounded-full text-xs font-medium border transition', active ? 'border-sage ring-2 ring-sage/20' : 'border-transparent', VEHICLE_STATUS_COLORS[key] || 'bg-linen text-stone')}>
                                {s.name} · {s.value}
                            </button>
                        );
                    })}
                </div>

                {/* Saved views */}
                <div className="flex flex-wrap gap-1 border-b border-linen">
                    {Object.entries(views).map(([key, label]) => (
                        <button key={key} type="button" onClick={() => apply({ view: key, page: undefined })}
                            className={cn('px-3 py-2 text-sm -mb-px border-b-2', (filters.view || 'all') === key ? 'border-sage text-void font-medium' : 'border-transparent text-stone hover:text-void')}>
                            {label}
                        </button>
                    ))}
                </div>

                <div className="flex flex-col lg:flex-row gap-3 bg-white p-3 rounded-xl border border-linen shadow-sm">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-pebble w-4 h-4" />
                        <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Tracking ID, VIN, make, model, container, customer…" className="pl-10 bg-cream border-linen rounded-lg" />
                    </div>
                    <Select value={filters.shipping_line_id ? String(filters.shipping_line_id) : 'all'} onValueChange={(v) => apply({ shipping_line_id: v, page: undefined })}>
                        <SelectTrigger className="lg:w-44 bg-white border-linen"><SelectValue placeholder="Shipping line" /></SelectTrigger>
                        <SelectContent className="bg-white">
                            <SelectItem value="all">All lines</SelectItem>
                            {shippingLines.map((l) => <SelectItem key={l.id} value={String(l.id)}>{l.name}</SelectItem>)}
                        </SelectContent>
                    </Select>
                    <Input type="month" value={filters.month || ''} onChange={(e) => apply({ month: e.target.value, page: undefined })} className="lg:w-44 bg-white border-linen [color-scheme:light]" title="Month added" />
                    {(filters.search || filters.status || filters.shipping_line_id || filters.month) && (
                        <Button variant="ghost" onClick={() => { setSearch(''); router.get(route('operations.shipments.index'), { view: filters.view }); }} className="text-pebble"><X className="h-4 w-4 mr-1" /> Clear</Button>
                    )}
                </div>

                {selected.length > 0 && (
                    <div className="sticky top-2 z-20 flex flex-wrap items-center gap-2 bg-void text-white rounded-xl px-4 py-3 shadow-float">
                        <span className="text-sm font-medium mr-2">{selected.length} selected</span>
                        <Select value={bulkStatus} onValueChange={setBulkStatus}>
                            <SelectTrigger className="w-40 h-9 bg-white text-charcoal"><SelectValue placeholder="Set status…" /></SelectTrigger>
                            <SelectContent className="bg-white">{statusOptions(statuses).map((o) => <SelectItem key={o.value} value={o.value}>{o.label}</SelectItem>)}</SelectContent>
                        </Select>
                        <Input type="date" value={bulkDate} onChange={(e) => setBulkDate(e.target.value)} className="w-40 h-9 bg-white text-charcoal [color-scheme:light]" />
                        <Button size="sm" disabled={!bulkStatus} onClick={() => runBulk({ action: 'status', status: bulkStatus, date: bulkDate })} className="btn-sage h-9">Apply</Button>
                        <span className="mx-2 h-6 w-px bg-white/20" />
                        <Select value={bulkContainer} onValueChange={setBulkContainer}>
                            <SelectTrigger className="w-56 h-9 bg-white text-charcoal"><SelectValue placeholder="Add to container…" /></SelectTrigger>
                            <SelectContent className="bg-white">
                                {openContainers.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)} disabled={c.shipments_count >= c.capacity}>
                                        {c.container_number} · {c.shipping_line?.name || '—'} · {c.shipments_count}/{c.capacity}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Button size="sm" disabled={!bulkContainer} onClick={() => runBulk({ action: 'container', container_id: bulkContainer })} className="btn-sage h-9">Add</Button>
                        <Button size="sm" variant="ghost" onClick={() => runBulk({ action: 'unassign' })} className="h-9 text-white hover:bg-white/10">Remove from container</Button>
                        <Button size="sm" variant="ghost" onClick={() => setSelected([])} className="h-9 text-white hover:bg-white/10 ml-auto"><X className="h-4 w-4" /></Button>
                    </div>
                )}

                <Card className="bg-white border-linen shadow-sm overflow-hidden rounded-xl">
                    <CardContent className="p-0 overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-linen bg-cream/50">
                                    <TableHead className="w-10"><input type="checkbox" checked={allSelected} onChange={toggleAll} className="accent-sage" /></TableHead>
                                    <SortHead column="tracking_id">Tracking ID</SortHead>
                                    <SortHead column="date_added">Added</SortHead>
                                    <SortHead column="vehicle_make">Vehicle</SortHead>
                                    <TableHead className="text-drift font-semibold">VIN</TableHead>
                                    <TableHead className="text-drift font-semibold">Customer</TableHead>
                                    <TableHead className="text-drift font-semibold">Container</TableHead>
                                    <SortHead column="loaded_on">Loaded</SortHead>
                                    <SortHead column="offloaded_on">Offloaded</SortHead>
                                    <SortHead column="status">Status</SortHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {rows.map((s) => (
                                    <TableRow key={s.id} className={cn('border-linen hover:bg-cream/60', selected.includes(s.id) && 'bg-sage/5')}>
                                        <TableCell><input type="checkbox" checked={selected.includes(s.id)} onChange={() => toggle(s.id)} className="accent-sage" /></TableCell>
                                        <TableCell className="whitespace-nowrap">
                                            <Link href={route('operations.shipments.show', s.id)} className="font-mono text-sm text-sage hover:underline">{s.tracking_id}</Link>
                                        </TableCell>
                                        <TableCell className="text-sm text-stone whitespace-nowrap">{formatDate(s.date_added)}</TableCell>
                                        <TableCell className="whitespace-nowrap">
                                            <button type="button" onClick={() => { setEditing(s); setFormOpen(true); }} className="text-left hover:text-sage">
                                                <span className="font-medium text-charcoal">{s.vehicle_make} {s.vehicle_model}</span>
                                                <span className="text-stone ml-1">{s.vehicle_year}</span>
                                            </button>
                                        </TableCell>
                                        <TableCell>
                                            {s.vin ? <span className="font-mono text-xs text-stone">{s.vin}</span> : <span className="text-xs text-amber-700 inline-flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> missing</span>}
                                        </TableCell>
                                        <TableCell className="text-sm">{s.client ? (s.client.company_name || s.client.contact_person) : <span className="text-pebble">—</span>}</TableCell>
                                        <TableCell className="whitespace-nowrap">
                                            {s.container ? (
                                                <Link href={route('operations.containers.show', s.container.id)} className="inline-flex flex-col hover:text-sage">
                                                    <span className="font-mono text-xs text-charcoal">{s.container.container_number}</span>
                                                    <span className="text-[11px] text-pebble">{s.container.shipping_line?.name}</span>
                                                </Link>
                                            ) : <span className="text-pebble text-xs">—</span>}
                                        </TableCell>
                                        <TableCell className="text-sm text-stone whitespace-nowrap">{formatDate(s.loaded_on)}</TableCell>
                                        <TableCell className="text-sm text-stone whitespace-nowrap">{formatDate(s.offloaded_on)}</TableCell>
                                        <TableCell>
                                            <StatusSelect
                                                status={s.status}
                                                options={statusOptions(statuses)}
                                                colorMap={VEHICLE_STATUS_COLORS}
                                                onStatusChange={(status) => router.patch(route('operations.shipments.update-status', s.id), { status }, { preserveScroll: true })}
                                            />
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {rows.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={10} className="h-32 text-center text-pebble">
                                            No vehicles match. <button className="text-sage underline" onClick={() => setImportOpen(true)}>Import the tracking sheet</button> or add one.
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Pagination links={shipments.links} />

                <ShipmentForm open={formOpen} onOpenChange={setFormOpen} shipment={editing} statuses={statuses} users={users} />
                <ImportDialog open={importOpen} onOpenChange={setImportOpen} />
            </div>
        </AppLayout>
    );
}
