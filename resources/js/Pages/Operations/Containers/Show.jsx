import { useMemo, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import ContainerForm from '@/Components/Operations/ContainerForm';
import { ArrowLeft, Pencil, Trash2, ExternalLink, X, Plus, AlertTriangle, History } from 'lucide-react';
import { CONTAINER_STATUS_COLORS, VEHICLE_STATUS_COLORS, formatDate, todayIso } from '@/lib/operations';
import { formatMoney, cn } from '@/lib/utils';

function Row({ label, children }) {
    return <div className="flex justify-between gap-4 py-1.5 text-sm"><span className="text-pebble">{label}</span><span className="text-charcoal text-right">{children || '—'}</span></div>;
}

export default function Show({ container, statuses, shippingLines, freightShare, transitDays, trackingUrl, unassigned }) {
    const { props } = usePage();
    const currency = props.settings?.currency || 'USD';
    const [formOpen, setFormOpen] = useState(false);
    const [picking, setPicking] = useState([]);
    const [filter, setFilter] = useState('');
    const statusForm = useForm({ status: container.status, date: todayIso(), note: '' });
    const free = container.capacity - container.shipments.length;

    const candidates = useMemo(() => {
        const q = filter.trim().toUpperCase();
        return unassigned.filter((s) => !q || [s.tracking_id, s.vin, s.vehicle_make, s.vehicle_model].some((v) => String(v || '').toUpperCase().includes(q)));
    }, [filter, unassigned]);

    const addSelected = () => router.post(route('operations.containers.add-vehicles', container.id), { shipment_ids: picking }, { preserveScroll: true, onSuccess: () => setPicking([]) });

    return (
        <AppLayout>
            <Head title={`Container ${container.container_number}`} />
            <div className="flex flex-col gap-6 max-w-7xl mx-auto">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('operations.containers.index')}><Button variant="outline" size="icon" className="border-linen"><ArrowLeft className="h-4 w-4" /></Button></Link>
                        <div>
                            <div className="flex items-center gap-3 flex-wrap">
                                <h1 className="text-display-sm text-void font-bold font-mono">{container.container_number}</h1>
                                <span className={cn('text-xs px-2 py-1 rounded-full font-medium', CONTAINER_STATUS_COLORS[container.status])}>{container.status_label}</span>
                                {!container.number_is_valid && <span className="text-xs text-amber-700 flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> check digit mismatch</span>}
                            </div>
                            <p className="text-drift text-sm mt-1">{container.shipping_line?.name || 'Line unknown'} · {container.shipments.length}/{container.capacity} cars</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {trackingUrl && <a href={trackingUrl} target="_blank" rel="noreferrer"><Button variant="outline" className="border-linen">Carrier tracking <ExternalLink className="ml-2 h-4 w-4" /></Button></a>}
                        <Button onClick={() => setFormOpen(true)} className="btn-sage"><Pencil className="mr-2 h-4 w-4" /> Edit</Button>
                        <Button variant="outline" className="border-linen text-red-600" onClick={() => confirm('Delete this container? Its cars will be unassigned.') && router.delete(route('operations.containers.destroy', container.id))}><Trash2 className="h-4 w-4" /></Button>
                    </div>
                </div>

                <Card className="bg-white border-linen shadow-sm">
                    <CardContent className="p-5">
                        <form onSubmit={(e) => { e.preventDefault(); statusForm.patch(route('operations.containers.update-status', container.id), { preserveScroll: true }); }} className="flex flex-wrap items-end gap-2">
                            <div className="text-sm text-stone mr-2">Move container <span className="text-pebble">(all {container.shipments.length} cars follow)</span></div>
                            <Select value={statusForm.data.status} onValueChange={(v) => statusForm.setData('status', v)}>
                                <SelectTrigger className="w-48 bg-cream border-linen"><SelectValue /></SelectTrigger>
                                <SelectContent className="bg-white">{Object.entries(statuses).map(([v, l]) => <SelectItem key={v} value={v}>{l}</SelectItem>)}</SelectContent>
                            </Select>
                            <Input type="date" value={statusForm.data.date} onChange={(e) => statusForm.setData('date', e.target.value)} className="w-40 bg-cream border-linen [color-scheme:light]" />
                            <Input value={statusForm.data.note} onChange={(e) => statusForm.setData('note', e.target.value)} placeholder="Note (optional)" className="flex-1 min-w-[160px] bg-cream border-linen" />
                            <Button type="submit" disabled={statusForm.processing} className="btn-sage">Update</Button>
                        </form>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 flex flex-col gap-6">
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void">Cars in this container</CardTitle></CardHeader>
                            <CardContent className="p-5 grid sm:grid-cols-2 gap-3">
                                {container.shipments.map((s) => (
                                    <div key={s.id} className="rounded-xl border border-linen p-3 flex justify-between gap-2">
                                        <div className="min-w-0">
                                            <p className="text-[11px] text-pebble">Slot {s.container_slot || '—'}</p>
                                            <Link href={route('operations.shipments.show', s.id)} className="font-medium text-void hover:text-sage">{s.vehicle_label || s.tracking_id}</Link>
                                            <p className="font-mono text-xs text-stone truncate">{s.tracking_id} · {s.vin || 'no VIN'}</p>
                                            <p className="text-xs text-stone">{s.client ? (s.client.company_name || s.client.contact_person) : 'No customer'}</p>
                                            <span className={cn('inline-block mt-1 text-[11px] px-1.5 py-0.5 rounded', VEHICLE_STATUS_COLORS[s.status])}>{s.status_label}</span>
                                        </div>
                                        <button type="button" title="Remove from container" onClick={() => confirm(`Remove ${s.tracking_id} from this container?`) && router.delete(route('operations.containers.remove-vehicle', [container.id, s.id]), { preserveScroll: true })} className="text-pebble hover:text-red-600 self-start"><X className="h-4 w-4" /></button>
                                    </div>
                                ))}
                                {Array.from({ length: Math.max(0, free) }).map((_, i) => (
                                    <div key={`free-${i}`} className="rounded-xl border border-dashed border-linen p-3 text-sm text-pebble flex items-center justify-center min-h-[96px]">Empty slot</div>
                                ))}
                                {free < 0 && <p className="sm:col-span-2 text-sm text-red-700 flex items-center gap-1"><AlertTriangle className="h-4 w-4" /> Over capacity by {-free}. Check whether some cars belong to a different trip.</p>}
                            </CardContent>
                        </Card>

                        {free > 0 && (
                            <Card className="bg-white border-linen shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void">Load cars ({free} free slot{free === 1 ? '' : 's'})</CardTitle></CardHeader>
                                <CardContent className="p-5 space-y-3">
                                    <Input value={filter} onChange={(e) => setFilter(e.target.value)} placeholder="Filter by tracking ID, VIN, make or model" className="bg-cream border-linen" />
                                    <div className="max-h-72 overflow-y-auto divide-y divide-linen">
                                        {candidates.map((s) => {
                                            const checked = picking.includes(s.id);
                                            const disabled = !checked && picking.length >= free;
                                            return (
                                                <label key={s.id} className={cn('flex items-center gap-3 py-2 text-sm', disabled ? 'opacity-50' : 'cursor-pointer')}>
                                                    <input type="checkbox" className="accent-sage" disabled={disabled} checked={checked} onChange={() => setPicking((p) => (checked ? p.filter((x) => x !== s.id) : [...p, s.id]))} />
                                                    <span className="font-mono text-xs w-28">{s.tracking_id}</span>
                                                    <span className="flex-1">{[s.vehicle_year, s.vehicle_make, s.vehicle_model].filter(Boolean).join(' ')}</span>
                                                    <span className="font-mono text-xs text-stone">{s.vin || '—'}</span>
                                                </label>
                                            );
                                        })}
                                        {candidates.length === 0 && <p className="py-4 text-sm text-pebble text-center">No unassigned cars.</p>}
                                    </div>
                                    <Button disabled={!picking.length} onClick={addSelected} className="btn-sage"><Plus className="mr-2 h-4 w-4" /> Load {picking.length || ''} car{picking.length === 1 ? '' : 's'}</Button>
                                </CardContent>
                            </Card>
                        )}

                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><History className="h-5 w-5" /> History</CardTitle></CardHeader>
                            <CardContent className="p-5">
                                <ol className="relative border-l border-linen ml-2 space-y-3">
                                    {container.milestones.map((m) => (
                                        <li key={m.id} className="ml-4">
                                            <span className="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full bg-sage" />
                                            <p className="text-sm text-void">{statuses[m.status] || m.status} <span className="text-pebble">· {formatDate(m.occurred_on)}</span></p>
                                            {(m.note || m.user) && <p className="text-xs text-stone">{m.note}{m.user ? ` — ${m.user.name}` : ''}</p>}
                                        </li>
                                    ))}
                                    {container.milestones.length === 0 && <li className="ml-4 text-sm text-pebble">No status changes recorded yet.</li>}
                                </ol>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="flex flex-col gap-6">
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void">Details</CardTitle></CardHeader>
                            <CardContent className="p-5">
                                <Row label="Size">{container.size_type}</Row>
                                <Row label="Booking #">{container.booking_number}</Row>
                                <Row label="Bill of lading #">{container.bl_number}</Row>
                                <Row label="Seal #">{container.seal_number}</Row>
                                <Row label="Vessel / voyage">{[container.vessel, container.voyage].filter(Boolean).join(' / ')}</Row>
                                <Row label="From">{container.port_of_loading}</Row>
                                <Row label="To">{container.port_of_discharge}</Row>
                            </CardContent>
                        </Card>
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void">Dates</CardTitle></CardHeader>
                            <CardContent className="p-5">
                                <Row label="Loaded">{formatDate(container.loaded_on, null)}</Row>
                                <Row label="ETD">{formatDate(container.etd, null)}</Row>
                                <Row label="Sailed">{formatDate(container.sailed_on, null)}</Row>
                                <Row label="ETA">{formatDate(container.eta, null)}</Row>
                                <Row label="Arrived">{formatDate(container.arrived_on, null)}</Row>
                                <Row label="Offloaded">{formatDate(container.offloaded_on, null)}</Row>
                                <Row label="Released">{formatDate(container.released_on, null)}</Row>
                                <Row label="Transit">{transitDays != null ? `${transitDays} days` : null}</Row>
                            </CardContent>
                        </Card>
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void">Freight</CardTitle></CardHeader>
                            <CardContent className="p-5">
                                <Row label="Freight cost">{container.freight_cost ? formatMoney(container.freight_cost, container.freight_currency || currency) : null}</Row>
                                <Row label="Per car">{freightShare ? formatMoney(freightShare, container.freight_currency || currency) : null}</Row>
                                <p className="text-xs text-pebble mt-2">The per-car share is counted as a cost on each car's profit.</p>
                            </CardContent>
                        </Card>
                        {container.notes && <Card className="bg-white border-linen shadow-sm"><CardContent className="p-5 text-sm whitespace-pre-wrap">{container.notes}</CardContent></Card>}
                    </div>
                </div>

                <ContainerForm open={formOpen} onOpenChange={setFormOpen} container={container} shippingLines={shippingLines} />
            </div>
        </AppLayout>
    );
}
