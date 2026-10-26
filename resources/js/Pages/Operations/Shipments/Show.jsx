import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import ShipmentForm from '@/Components/Operations/ShipmentForm';
import { ArrowLeft, Pencil, Ship, User, ExternalLink, AlertTriangle, CheckCircle2, Trash2, Container as ContainerIcon, DollarSign, History } from 'lucide-react';
import { VEHICLE_STATUS_COLORS, formatDate, todayIso } from '@/lib/operations';
import { formatMoney, cn } from '@/lib/utils';

const PIPELINE = ['added', 'purchased', 'picked_up', 'at_yard', 'loaded', 'sailed', 'arrived', 'offloaded', 'cleared', 'delivered'];

function Row({ label, children }) {
    return (
        <div className="flex justify-between gap-4 py-1.5 text-sm">
            <span className="text-pebble">{label}</span>
            <span className="text-charcoal text-right">{children ?? '—'}</span>
        </div>
    );
}

function StatusChanger({ shipment, statuses }) {
    const { data, setData, patch, processing } = useForm({ status: shipment.status, date: todayIso(), note: '' });
    return (
        <form onSubmit={(e) => { e.preventDefault(); patch(route('operations.shipments.update-status', shipment.id), { preserveScroll: true, onSuccess: () => setData('note', '') }); }}
            className="flex flex-wrap items-end gap-2">
            <Select value={data.status} onValueChange={(v) => setData('status', v)}>
                <SelectTrigger className="w-44 bg-cream border-linen"><SelectValue /></SelectTrigger>
                <SelectContent className="bg-white">{Object.entries(statuses).map(([v, l]) => <SelectItem key={v} value={v}>{l}</SelectItem>)}</SelectContent>
            </Select>
            <Input type="date" value={data.date} onChange={(e) => setData('date', e.target.value)} className="w-40 bg-cream border-linen [color-scheme:light]" />
            <Input value={data.note} onChange={(e) => setData('note', e.target.value)} placeholder="Note (optional)" className="flex-1 min-w-[160px] bg-cream border-linen" />
            <Button type="submit" disabled={processing} className="btn-sage">Update status</Button>
        </form>
    );
}

function Money({ shipment, money, categories, currency }) {
    const { data, setData, post, processing, errors, reset } = useForm({ kind: 'charge', category: '', description: '', amount: '', incurred_on: todayIso() });
    const add = (e) => {
        e.preventDefault();
        post(route('finance.charges.store', shipment.id), { preserveScroll: true, onSuccess: () => reset('category', 'description', 'amount') });
    };
    const lines = shipment.charges || [];

    return (
        <Card className="bg-white border-linen shadow-sm">
            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><DollarSign className="h-5 w-5" /> Money</CardTitle></CardHeader>
            <CardContent className="p-5 space-y-4">
                <div className="grid grid-cols-3 gap-3 text-center">
                    <div className="rounded-lg bg-cream p-3"><p className="text-xs text-pebble">Costs</p><p className="font-semibold">{formatMoney(money.total_cost, currency)}</p></div>
                    <div className="rounded-lg bg-cream p-3"><p className="text-xs text-pebble">Charged</p><p className="font-semibold">{formatMoney(money.total_charges, currency)}</p></div>
                    <div className={cn('rounded-lg p-3', money.profit >= 0 ? 'bg-emerald-50' : 'bg-red-50')}><p className="text-xs text-pebble">Profit</p><p className={cn('font-semibold', money.profit >= 0 ? 'text-emerald-700' : 'text-red-700')}>{formatMoney(money.profit, currency)}</p></div>
                </div>

                <div className="divide-y divide-linen text-sm">
                    {shipment.purchase_price && <div className="flex justify-between py-2"><span>Purchase price <span className="text-pebble">(cost)</span></span><span>{formatMoney(shipment.purchase_price, currency)}</span></div>}
                    {money.freight_share != null && <div className="flex justify-between py-2"><span>Share of container freight <span className="text-pebble">(cost)</span></span><span>{formatMoney(money.freight_share, currency)}</span></div>}
                    {lines.map((c) => (
                        <div key={c.id} className="flex justify-between items-center py-2 gap-2">
                            <span>
                                {c.category}{c.description ? ` · ${c.description}` : ''}{' '}
                                <span className={cn('text-xs', c.kind === 'cost' ? 'text-pebble' : 'text-sage')}>({c.kind === 'cost' ? 'cost' : 'charge'})</span>
                                {c.invoice && <Link href={route('finance.invoices.show', c.invoice.id)} className="ml-2 text-xs text-sage underline">{c.invoice.invoice_number}</Link>}
                            </span>
                            <span className="flex items-center gap-2">
                                {formatMoney(c.amount, c.currency || currency)}
                                {!c.invoice_id && (
                                    <button type="button" onClick={() => confirm('Remove this line?') && router.delete(route('finance.charges.destroy', c.id), { preserveScroll: true })} className="text-pebble hover:text-red-600"><Trash2 className="h-3.5 w-3.5" /></button>
                                )}
                            </span>
                        </div>
                    ))}
                    {lines.length === 0 && !shipment.purchase_price && money.freight_share == null && <p className="py-2 text-pebble">No costs or charges yet.</p>}
                </div>

                <form onSubmit={add} className="grid grid-cols-2 gap-2 pt-2 border-t border-linen">
                    <Select value={data.kind} onValueChange={(v) => setData((d) => ({ ...d, kind: v, category: '' }))}>
                        <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                        <SelectContent className="bg-white"><SelectItem value="charge">Charge to customer</SelectItem><SelectItem value="cost">Cost to Mapleby</SelectItem></SelectContent>
                    </Select>
                    <Select value={data.category} onValueChange={(v) => setData('category', v)}>
                        <SelectTrigger className="bg-cream border-linen"><SelectValue placeholder="Category" /></SelectTrigger>
                        <SelectContent className="bg-white">{(categories[data.kind] || []).map((c) => <SelectItem key={c} value={c}>{c}</SelectItem>)}</SelectContent>
                    </Select>
                    <Input value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Description" className="bg-cream border-linen" />
                    <Input type="number" step="0.01" value={data.amount} onChange={(e) => setData('amount', e.target.value)} placeholder="Amount" className="bg-cream border-linen" />
                    {(errors.category || errors.amount) && <p className="col-span-2 text-xs text-red-600">{errors.category || errors.amount}</p>}
                    <Button type="submit" disabled={processing} variant="outline" className="col-span-2 border-linen">Add line</Button>
                </form>
            </CardContent>
        </Card>
    );
}

export default function Show({ shipment, statuses, users, vin, trackingUrl, money, chargeCategories }) {
    const { props } = usePage();
    const currency = props.settings?.currency || 'USD';
    const [formOpen, setFormOpen] = useState(false);
    const container = shipment.container;
    const reachedIndex = PIPELINE.indexOf(shipment.status);
    const reached = new Set((shipment.milestones || []).map((m) => m.status));

    return (
        <AppLayout>
            <Head title={`${shipment.tracking_id} · ${shipment.vehicle_label}`} />

            <div className="flex flex-col gap-6 max-w-7xl mx-auto">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('operations.shipments.index')}><Button variant="outline" size="icon" className="border-linen"><ArrowLeft className="h-4 w-4" /></Button></Link>
                        <div>
                            <div className="flex items-center gap-3 flex-wrap">
                                <h1 className="text-display-sm text-void font-bold">{shipment.vehicle_label || 'Vehicle'}</h1>
                                <span className="font-mono text-sm px-2 py-0.5 rounded bg-cream border border-linen">{shipment.tracking_id}</span>
                                <span className={cn('text-xs px-2 py-1 rounded-full font-medium', VEHICLE_STATUS_COLORS[shipment.status])}>{shipment.status_label}</span>
                            </div>
                            <p className="text-drift text-sm mt-1 font-mono">{shipment.vin || 'No VIN yet'}</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button onClick={() => setFormOpen(true)} className="btn-sage"><Pencil className="mr-2 h-4 w-4" /> Edit</Button>
                        <Button variant="outline" className="border-linen text-red-600" onClick={() => confirm('Delete this vehicle?') && router.delete(route('operations.shipments.destroy', shipment.id), { onSuccess: () => router.visit(route('operations.shipments.index')) })}><Trash2 className="h-4 w-4" /></Button>
                    </div>
                </div>

                {/* Pipeline */}
                <Card className="bg-white border-linen shadow-sm">
                    <CardContent className="p-5 space-y-5">
                        <div className="flex items-center overflow-x-auto pb-2">
                            {PIPELINE.map((step, i) => {
                                const done = i <= reachedIndex || reached.has(step);
                                return (
                                    <div key={step} className="flex items-center shrink-0">
                                        <div className="flex flex-col items-center gap-1 w-20">
                                            <div className={cn('h-7 w-7 rounded-full border-2 flex items-center justify-center text-[11px] font-bold', done ? 'bg-sage border-sage text-white' : 'border-linen text-pebble bg-white', step === shipment.status && 'ring-4 ring-sage/20')}>{i + 1}</div>
                                            <span className={cn('text-[11px] text-center leading-tight', done ? 'text-void' : 'text-pebble')}>{statuses[step]}</span>
                                        </div>
                                        {i < PIPELINE.length - 1 && <div className={cn('h-0.5 w-6', i < reachedIndex ? 'bg-sage' : 'bg-linen')} />}
                                    </div>
                                );
                            })}
                        </div>
                        {['on_hold', 'issue'].includes(shipment.status) && (
                            <p className="text-sm text-red-700 flex items-center gap-2"><AlertTriangle className="h-4 w-4" /> This car is marked {shipment.status_label}.</p>
                        )}
                        <StatusChanger shipment={shipment} statuses={statuses} />
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 flex flex-col gap-6">
                        <div className="grid md:grid-cols-2 gap-6">
                            <Card className="bg-white border-linen shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void">Vehicle</CardTitle></CardHeader>
                                <CardContent className="p-5">
                                    <Row label="Year">{shipment.vehicle_year}</Row>
                                    <Row label="Make">{shipment.vehicle_make}</Row>
                                    <Row label="Model">{shipment.vehicle_model}</Row>
                                    <Row label="Colour">{shipment.vehicle_color}</Row>
                                    <Row label="Date added">{formatDate(shipment.date_added)}</Row>
                                    <Row label="Destination">{shipment.destination_city}</Row>
                                    {vin && (
                                        <div className="mt-3 text-xs space-y-1">
                                            {vin.valid && !vin.warnings.length && <p className="text-sage flex items-center gap-1"><CheckCircle2 className="h-3 w-3" /> VIN checks out{vin.year && vin.year !== shipment.vehicle_year ? ` (decodes as ${vin.year})` : ''}</p>}
                                            {[...vin.errors, ...vin.warnings].map((w) => <p key={w} className="text-amber-700 flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> {w}</p>)}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <Card className="bg-white border-linen shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><Ship className="h-5 w-5" /> Shipping</CardTitle></CardHeader>
                                <CardContent className="p-5">
                                    {container ? (
                                        <>
                                            <Row label="Container"><Link href={route('operations.containers.show', container.id)} className="font-mono text-sage hover:underline">{container.container_number}</Link> <span className="text-pebble">slot {shipment.container_slot || '—'}</span></Row>
                                            <Row label="Shipping line">{container.shipping_line?.name}</Row>
                                            <Row label="Vessel / voyage">{[container.vessel, container.voyage].filter(Boolean).join(' / ') || null}</Row>
                                            <Row label="Loaded">{formatDate(shipment.loaded_on || container.loaded_on)}</Row>
                                            <Row label="ETA">{formatDate(container.eta)}</Row>
                                            <Row label="Offloaded">{formatDate(shipment.offloaded_on || container.offloaded_on)}</Row>
                                            {trackingUrl && <a href={trackingUrl} target="_blank" rel="noreferrer" className="mt-2 inline-flex items-center gap-1 text-sm text-sage hover:underline">Track on carrier site <ExternalLink className="h-3 w-3" /></a>}
                                        </>
                                    ) : (
                                        <div className="text-sm text-pebble space-y-2">
                                            <p>Not in a container yet.</p>
                                            <Link href={route('operations.containers.index')} className="text-sage underline inline-flex items-center gap-1"><ContainerIcon className="h-4 w-4" /> Load it from the container board</Link>
                                        </div>
                                    )}
                                    <Row label="Delivered">{formatDate(shipment.delivered_on)}</Row>
                                </CardContent>
                            </Card>
                        </div>

                        <Money shipment={shipment} money={money} categories={chargeCategories} currency={currency} />

                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><History className="h-5 w-5" /> History</CardTitle></CardHeader>
                            <CardContent className="p-5">
                                <ol className="relative border-l border-linen ml-2 space-y-4">
                                    {(shipment.milestones || []).map((m) => (
                                        <li key={m.id} className="ml-4">
                                            <span className="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full bg-sage" />
                                            <p className="text-sm font-medium text-void">{statuses[m.status] || m.status} <span className="text-pebble font-normal">· {formatDate(m.occurred_on)}</span></p>
                                            {(m.note || m.user) && <p className="text-xs text-stone">{m.note}{m.user ? ` — ${m.user.name}` : ''}</p>}
                                        </li>
                                    ))}
                                    {(!shipment.milestones || shipment.milestones.length === 0) && <li className="ml-4 text-sm text-pebble">No history yet.</li>}
                                </ol>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="flex flex-col gap-6">
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><User className="h-5 w-5" /> Customer</CardTitle></CardHeader>
                            <CardContent className="p-5">
                                {shipment.client ? (
                                    <>
                                        <Link href={route('crm.clients.show', shipment.client.id)} className="font-semibold text-void hover:text-sage">{shipment.client.company_name || shipment.client.contact_person}</Link>
                                        {shipment.client.company_name && <p className="text-sm text-stone">{shipment.client.contact_person}</p>}
                                        <Row label="Phone">{shipment.client.phone}</Row>
                                        <Row label="Email">{shipment.client.email}</Row>
                                    </>
                                ) : (
                                    <p className="text-sm text-amber-700 flex items-center gap-1"><AlertTriangle className="h-4 w-4" /> No customer yet. <button className="underline" onClick={() => setFormOpen(true)}>Add one</button></p>
                                )}
                                <Row label="Payment">{shipment.payment_status}</Row>
                            </CardContent>
                        </Card>

                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void">Purchase</CardTitle></CardHeader>
                            <CardContent className="p-5">
                                <Row label="Platform">{shipment.purchase_platform}</Row>
                                <Row label="Lot / stock #">{shipment.lot_number}</Row>
                                <Row label="Purchase date">{formatDate(shipment.purchase_date)}</Row>
                                <Row label="Price">{shipment.purchase_price ? formatMoney(shipment.purchase_price, currency) : null}</Row>
                            </CardContent>
                        </Card>

                        {shipment.description && (
                            <Card className="bg-white border-linen shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void">Notes</CardTitle></CardHeader>
                                <CardContent className="p-5 text-sm whitespace-pre-wrap text-charcoal">{shipment.description}</CardContent>
                            </Card>
                        )}
                    </div>
                </div>

                <ShipmentForm open={formOpen} onOpenChange={setFormOpen} shipment={shipment} statuses={statuses} users={users} />
            </div>
        </AppLayout>
    );
}
