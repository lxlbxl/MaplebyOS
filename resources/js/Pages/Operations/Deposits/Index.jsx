import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetFooter } from "@/Components/ui/sheet";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import Pagination from '@/Components/Pagination';
import { Field } from '@/Components/Operations/CargoParts';
import { Plus, Search, BellRing } from 'lucide-react';
import { REFUND_STATUS_COLORS, money } from '@/lib/deposits';
import { formatDate, todayIso } from '@/lib/operations';
import { useCan } from '@/lib/access';
import { cn } from '@/lib/utils';

function Tile({ label, value, tone, onClick, active }) {
    return (
        <button type="button" onClick={onClick}
            className={cn('shrink-0 rounded-xl border px-4 py-3 text-left min-w-[140px]', active ? 'border-sage bg-sage/10' : tone === 'bad' ? 'border-red-100 bg-red-50' : 'border-linen bg-white')}>
            <p className="text-[11px] text-pebble whitespace-nowrap">{label}</p>
            <p className={cn('text-lg font-semibold', tone === 'bad' ? 'text-red-700' : 'text-void')}>{value}</p>
        </button>
    );
}

export function DepositForm({ open, onOpenChange, options }) {
    const can = useCan();
    const { data, setData, post, processing, errors, reset } = useForm({
        container_number: '', amount: '', currency: 'NGN', paid_on: todayIso(), paid_by: 'mapleby', payment_reference: '', shipping_line_id: '', notes: '', owner_id: '',
    });
    const submit = (e) => {
        e.preventDefault();
        post(route('operations.deposits.store'), { onSuccess: () => { reset(); onOpenChange(false); } });
    };
    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="bg-white overflow-y-auto sm:max-w-lg">
                <SheetHeader>
                    <SheetTitle>Record a container deposit</SheetTitle>
                    <SheetDescription>What was paid to the shipping line to release the container. The refund is tracked separately once the empty is returned.</SheetDescription>
                </SheetHeader>
                <form onSubmit={submit} className="space-y-4 py-4">
                    <Field label="Container number" error={errors.container_number}>
                        <Input list="deposit-containers" value={data.container_number} onChange={(e) => setData('container_number', e.target.value.toUpperCase())} placeholder="MSCU1234565" className="bg-cream border-linen font-mono" />
                        <datalist id="deposit-containers">{options.containers.map((c) => <option key={c.id} value={c.container_number} />)}</datalist>
                    </Field>
                    <div className="grid grid-cols-3 gap-3">
                        <Field label="Amount" error={errors.amount} className="col-span-2">
                            <Input type="number" min="0" step="0.01" value={data.amount} onChange={(e) => setData('amount', e.target.value)} className="bg-cream border-linen" />
                        </Field>
                        <Field label="Currency" error={errors.currency}>
                            <Input value={data.currency} maxLength={3} onChange={(e) => setData('currency', e.target.value.toUpperCase())} className="bg-cream border-linen" />
                        </Field>
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <Field label="Paid on" error={errors.paid_on}>
                            <Input type="date" value={data.paid_on} onChange={(e) => setData('paid_on', e.target.value)} className="bg-cream border-linen [color-scheme:light]" />
                        </Field>
                        <Field label="Paid by" error={errors.paid_by}>
                            <Select value={data.paid_by} onValueChange={(v) => setData('paid_by', v)}>
                                <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                                <SelectContent className="bg-white">{Object.entries(options.paidBy).map(([k, l]) => <SelectItem key={k} value={k}>{l}</SelectItem>)}</SelectContent>
                            </Select>
                        </Field>
                    </div>
                    <Field label="Shipping line (if different from the container's)" error={errors.shipping_line_id}>
                        <Select value={data.shipping_line_id ? String(data.shipping_line_id) : 'none'} onValueChange={(v) => setData('shipping_line_id', v === 'none' ? '' : v)}>
                            <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                            <SelectContent className="bg-white"><SelectItem value="none">Same as container</SelectItem>{options.shippingLines.map((l) => <SelectItem key={l.id} value={String(l.id)}>{l.name}</SelectItem>)}</SelectContent>
                        </Select>
                    </Field>
                    <Field label="Receipt / payment reference" error={errors.payment_reference}>
                        <Input value={data.payment_reference} onChange={(e) => setData('payment_reference', e.target.value)} className="bg-cream border-linen" />
                    </Field>
                    {can('deposits.oversee') && (
                        <Field label="Who follows up the refund" error={errors.owner_id}>
                            <Select value={data.owner_id ? String(data.owner_id) : 'none'} onValueChange={(v) => setData('owner_id', v === 'none' ? '' : v)}>
                                <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                                <SelectContent className="bg-white"><SelectItem value="none">Me</SelectItem>{options.owners.map((u) => <SelectItem key={u.id} value={String(u.id)}>{u.name}</SelectItem>)}</SelectContent>
                            </Select>
                        </Field>
                    )}
                    <Field label="Notes" error={errors.notes}>
                        <Input value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="bg-cream border-linen" />
                    </Field>
                    <SheetFooter>
                        <Button type="submit" disabled={processing} className="btn-sage w-full">Save deposit</Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}

export default function Index({ deposits, filters, views, summary, formOptions }) {
    const can = useCan();
    const [formOpen, setFormOpen] = useState(false);
    const [search, setSearch] = useState(filters.search || '');
    const view = filters.view || 'open';
    const c = summary.counts;

    useEffect(() => {
        const t = setTimeout(() => {
            if (search !== (filters.search || '')) router.get(route('operations.deposits.index'), { ...filters, search }, { preserveState: true, replace: true });
        }, 400);
        return () => clearTimeout(t);
    }, [search]);

    const go = (params) => router.get(route('operations.deposits.index'), { ...filters, ...params }, { preserveState: true, replace: true });

    return (
        <AppLayout>
            <Head title="Container deposits" />
            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-5">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Container deposits</h1>
                        <p className="text-drift mt-1">Deposits paid to shipping lines and the refunds that should come back after the empty is returned.</p>
                    </div>
                    {can('deposits.manage') && <Button onClick={() => setFormOpen(true)} className="btn-sage shadow-soft"><Plus className="mr-2 h-4 w-4" /> Record deposit</Button>}
                </div>

                <div className="flex gap-2 overflow-x-auto pb-1">
                    {Object.entries(summary.byCurrency).map(([cur, t]) => (
                        <Tile key={cur} label={`Still to come back (${cur})`} value={money(t.outstanding, cur)} onClick={() => go({ view: 'open' })} />
                    ))}
                    <Tile label="Follow-up due" value={c.follow_up} tone={c.follow_up ? 'bad' : undefined} active={view === 'follow_up'} onClick={() => go({ view: 'follow_up' })} />
                    <Tile label="Claim not filed" value={c.to_claim} tone={c.to_claim ? 'bad' : undefined} active={view === 'to_claim'} onClick={() => go({ view: 'to_claim' })} />
                    <Tile label="Refunded, to confirm" value={c.to_confirm} active={view === 'to_confirm'} onClick={() => go({ view: 'to_confirm' })} />
                    <Tile label="Disputed" value={c.disputed} active={view === 'disputed'} onClick={() => go({ view: 'disputed' })} />
                    {can('deposits.oversee') && <Tile label="Nobody following up" value={c.unowned} tone={c.unowned ? 'bad' : undefined} onClick={() => go({ view: 'open' })} />}
                </div>

                <div className="flex flex-col md:flex-row gap-3 md:items-center">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-pebble" />
                        <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search container, BL, shipping line, reference…" className="pl-9 bg-white border-linen h-10" />
                    </div>
                    <div className="flex gap-1 flex-wrap">
                        {Object.entries(views).map(([key, label]) => (
                            <button key={key} onClick={() => go({ view: key })}
                                className={cn('text-xs rounded-full px-3 py-1.5 border', view === key ? 'bg-void text-white border-void' : 'bg-white border-linen text-stone')}>{label}</button>
                        ))}
                        <button onClick={() => go({ owner: filters.owner === 'mine' ? '' : 'mine' })}
                            className={cn('text-xs rounded-full px-3 py-1.5 border', filters.owner === 'mine' ? 'bg-sage text-white border-sage' : 'bg-white border-linen text-stone')}>Mine</button>
                    </div>
                </div>

                <div className="rounded-xl border border-linen bg-white overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-linen text-left text-pebble text-xs">
                                <th className="p-3">Container</th><th className="p-3">Line</th><th className="p-3">Deposit</th><th className="p-3">Paid</th>
                                <th className="p-3">Empty returned</th><th className="p-3">Still to come back</th><th className="p-3">Refund</th><th className="p-3">Next follow-up</th><th className="p-3">Follows up</th>
                            </tr>
                        </thead>
                        <tbody>
                            {deposits.data.map((d) => (
                                <tr key={d.id} className="border-b border-linen last:border-0 hover:bg-cream/50 cursor-pointer" onClick={() => router.visit(route('operations.deposits.show', d.id))}>
                                    <td className="p-3 font-mono text-xs">
                                        <Link href={route('operations.deposits.show', d.id)} className="text-sage">{d.container_number}</Link>
                                        {d.job && <div className="text-pebble font-sans">{d.job.tracking_id}</div>}
                                    </td>
                                    <td className="p-3 text-xs">{d.shipping_line || '—'}</td>
                                    <td className="p-3 whitespace-nowrap">{money(d.amount, d.currency)}</td>
                                    <td className="p-3 text-xs whitespace-nowrap">{formatDate(d.paid_on)}</td>
                                    <td className="p-3 text-xs whitespace-nowrap">{d.empty_returned_on ? formatDate(d.empty_returned_on) : <span className="text-pebble">not yet</span>}</td>
                                    <td className="p-3 whitespace-nowrap">{d.outstanding > 0 ? money(d.outstanding, d.currency) : <span className="text-pebble">—</span>}</td>
                                    <td className="p-3"><span className={cn('text-xs px-2 py-1 rounded-full font-medium whitespace-nowrap', REFUND_STATUS_COLORS[d.status])}>{d.status_label}</span></td>
                                    <td className={cn('p-3 text-xs whitespace-nowrap', d.follow_up_due && 'text-red-700 font-medium')}>
                                        {d.follow_up_due && <BellRing className="inline h-3.5 w-3.5 mr-1" />}{d.next_follow_up_on ? formatDate(d.next_follow_up_on) : '—'}
                                    </td>
                                    <td className="p-3 text-xs">{d.owner?.name || <span className="text-amber-700">nobody</span>}</td>
                                </tr>
                            ))}
                            {!deposits.data.length && <tr><td colSpan={9} className="p-8 text-center text-pebble">No deposits here.</td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination links={deposits.links} />
            </div>

            {can('deposits.manage') && <DepositForm open={formOpen} onOpenChange={setFormOpen} options={formOptions} />}
        </AppLayout>
    );
}
