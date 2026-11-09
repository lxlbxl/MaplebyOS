import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Field, Row } from '@/Components/Operations/CargoParts';
import AuditTrail from '@/Components/Operations/AuditTrail';
import { ArrowLeft, Landmark, Undo2, PhoneCall, ShieldCheck, Trash2, Pencil, UserCheck } from 'lucide-react';
import { REFUND_STATUS_COLORS, money, formatDateTime } from '@/lib/deposits';
import { formatDate, todayIso } from '@/lib/operations';
import { useCan } from '@/lib/access';
import { cn } from '@/lib/utils';

const STEPS = ['awaiting_empty', 'to_claim', 'claimed', 'received', 'verified'];

function Steps({ status, statuses }) {
    const at = status === 'part_received' ? STEPS.indexOf('claimed') : STEPS.indexOf(status);
    return (
        <ol className="flex flex-wrap gap-2">
            {STEPS.map((s, i) => (
                <li key={s} className={cn('text-xs px-3 py-1.5 rounded-full border', i < at ? 'bg-sage/10 border-sage/30 text-sage' : i === at ? 'bg-void text-white border-void' : 'bg-white border-linen text-pebble')}>
                    {statuses[s]}
                </li>
            ))}
            {['disputed', 'written_off', 'part_received'].includes(status) && <li className={cn('text-xs px-3 py-1.5 rounded-full', REFUND_STATUS_COLORS[status])}>{statuses[status]}</li>}
        </ol>
    );
}

function DepositEdit({ deposit, options, onDone }) {
    const { data, setData, put, processing, errors } = useForm({
        amount: deposit.amount, currency: deposit.currency, paid_on: deposit.paid_on || '', paid_by: deposit.paid_by,
        payment_reference: deposit.payment_reference || '', shipping_line_id: deposit.shipping_line_id ? String(deposit.shipping_line_id) : '', notes: deposit.notes || '',
    });
    return (
        <form onSubmit={(e) => { e.preventDefault(); put(route('operations.deposits.update', deposit.id), { preserveScroll: true, onSuccess: onDone }); }} className="space-y-3">
            <div className="grid grid-cols-3 gap-2">
                <Field label="Amount" error={errors.amount} className="col-span-2"><Input type="number" step="0.01" value={data.amount} onChange={(e) => setData('amount', e.target.value)} className="bg-cream border-linen" /></Field>
                <Field label="Currency" error={errors.currency}><Input value={data.currency} maxLength={3} onChange={(e) => setData('currency', e.target.value.toUpperCase())} className="bg-cream border-linen" /></Field>
            </div>
            <div className="grid grid-cols-2 gap-2">
                <Field label="Paid on" error={errors.paid_on}><Input type="date" value={data.paid_on} onChange={(e) => setData('paid_on', e.target.value)} className="bg-cream border-linen [color-scheme:light]" /></Field>
                <Field label="Paid by">
                    <Select value={data.paid_by} onValueChange={(v) => setData('paid_by', v)}>
                        <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                        <SelectContent className="bg-white">{Object.entries(options.paidBy).map(([k, l]) => <SelectItem key={k} value={k}>{l}</SelectItem>)}</SelectContent>
                    </Select>
                </Field>
            </div>
            <Field label="Shipping line">
                <Select value={data.shipping_line_id || 'none'} onValueChange={(v) => setData('shipping_line_id', v === 'none' ? '' : v)}>
                    <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                    <SelectContent className="bg-white"><SelectItem value="none">—</SelectItem>{options.shippingLines.map((l) => <SelectItem key={l.id} value={String(l.id)}>{l.name}</SelectItem>)}</SelectContent>
                </Select>
            </Field>
            <Field label="Receipt / reference" error={errors.payment_reference}><Input value={data.payment_reference} onChange={(e) => setData('payment_reference', e.target.value)} className="bg-cream border-linen" /></Field>
            <Field label="Notes"><Input value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="bg-cream border-linen" /></Field>
            <div className="flex gap-2">
                <Button type="submit" disabled={processing} className="btn-sage">Save</Button>
                <Button type="button" variant="outline" className="border-linen" onClick={onDone}>Cancel</Button>
            </div>
        </form>
    );
}

function RefundForm({ deposit, refund, options, locked }) {
    const can = useCan();
    const { data, setData, put, processing, errors } = useForm({
        empty_returned_on: deposit.empty_returned_on || '',
        claim_submitted_on: refund.claim_submitted_on || '',
        claim_reference: refund.claim_reference || '',
        deductions: refund.deductions && Number(refund.deductions) > 0 ? refund.deductions : '',
        deduction_reason: refund.deduction_reason || '',
        amount_received: refund.amount_received && Number(refund.amount_received) > 0 ? refund.amount_received : '',
        received_on: refund.received_on || '',
        received_reference: refund.received_reference || '',
        next_follow_up_on: refund.next_follow_up_on || '',
        notes: refund.notes || '',
        ...(can('deposits.oversee') ? { owner_id: refund.owner_id ? String(refund.owner_id) : '' } : {}),
    });
    const disabled = locked || !(can('deposits.manage') || can('deposits.oversee'));
    const input = (key, props = {}) => (
        <Input value={data[key]} onChange={(e) => setData(key, e.target.value)} disabled={disabled} className="bg-cream border-linen [color-scheme:light]" {...props} />
    );

    return (
        <form onSubmit={(e) => { e.preventDefault(); put(route('operations.deposits.refund', deposit.id), { preserveScroll: true }); }} className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <Field label="Empty container returned on" error={errors.empty_returned_on}>{input('empty_returned_on', { type: 'date', max: todayIso() })}</Field>
                <Field label="Next follow-up" error={errors.next_follow_up_on}>{input('next_follow_up_on', { type: 'date' })}</Field>
                <Field label="Refund claim filed on" error={errors.claim_submitted_on}>{input('claim_submitted_on', { type: 'date', max: todayIso() })}</Field>
                <Field label="Claim reference" error={errors.claim_reference}>{input('claim_reference')}</Field>
                <Field label={`Deductions (${deposit.currency})`} error={errors.deductions}>{input('deductions', { type: 'number', step: '0.01', min: 0, placeholder: '0' })}</Field>
                <Field label="What the deductions are for" error={errors.deduction_reason}>{input('deduction_reason', { placeholder: 'Demurrage, damage, cleaning…' })}</Field>
                <Field label={`Refund received (${deposit.currency})`} error={errors.amount_received}>{input('amount_received', { type: 'number', step: '0.01', min: 0, placeholder: '0' })}</Field>
                <Field label="Received on" error={errors.received_on}>{input('received_on', { type: 'date', max: todayIso() })}</Field>
                <Field label="Refund reference / bank narration" error={errors.received_reference} className="sm:col-span-2">{input('received_reference')}</Field>
                {can('deposits.oversee') && (
                    <Field label="Who follows up" error={errors.owner_id} className="sm:col-span-2">
                        <Select value={data.owner_id || 'none'} onValueChange={(v) => setData('owner_id', v === 'none' ? '' : v)} disabled={disabled}>
                            <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                            <SelectContent className="bg-white"><SelectItem value="none">Nobody yet</SelectItem>{options.owners.map((u) => <SelectItem key={u.id} value={String(u.id)}>{u.name}</SelectItem>)}</SelectContent>
                        </Select>
                    </Field>
                )}
                <Field label="Notes" error={errors.notes} className="sm:col-span-2">
                    <Textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} disabled={disabled} rows={2} className="bg-cream border-linen" />
                </Field>
            </div>
            {!disabled && <Button type="submit" disabled={processing} className="btn-sage">Save refund</Button>}
            {locked && <p className="text-xs text-pebble">This refund is closed. The CEO or Head of Business can reopen it.</p>}
        </form>
    );
}

function FollowUpForm({ depositId, channels }) {
    const { data, setData, post, processing, errors, reset } = useForm({ contacted_on: todayIso(), channel: 'call', contact_person: '', outcome: '', next_follow_up_on: '' });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post(route('operations.deposits.follow-ups.store', depositId), { preserveScroll: true, onSuccess: () => reset('outcome', 'contact_person', 'next_follow_up_on') }); }}
            className="space-y-2 pt-3 border-t border-linen">
            <div className="flex flex-wrap gap-2">
                <Input type="date" value={data.contacted_on} max={todayIso()} onChange={(e) => setData('contacted_on', e.target.value)} className="w-40 bg-cream border-linen [color-scheme:light]" />
                <Select value={data.channel} onValueChange={(v) => setData('channel', v)}>
                    <SelectTrigger className="w-40 bg-cream border-linen"><SelectValue /></SelectTrigger>
                    <SelectContent className="bg-white">{Object.entries(channels).map(([k, l]) => <SelectItem key={k} value={k}>{l}</SelectItem>)}</SelectContent>
                </Select>
                <Input value={data.contact_person} onChange={(e) => setData('contact_person', e.target.value)} placeholder="Spoke to" className="flex-1 min-w-[120px] bg-cream border-linen" />
            </div>
            <Textarea value={data.outcome} onChange={(e) => setData('outcome', e.target.value)} rows={2} placeholder="What did the shipping line say?" className="bg-cream border-linen" />
            {errors.outcome && <p className="text-xs text-red-600">{errors.outcome}</p>}
            <div className="flex flex-wrap items-center gap-2">
                <span className="text-xs text-pebble">Chase again on</span>
                <Input type="date" value={data.next_follow_up_on} min={todayIso()} onChange={(e) => setData('next_follow_up_on', e.target.value)} className="w-40 bg-cream border-linen [color-scheme:light]" />
                <span className="text-xs text-pebble">(blank = in 7 days)</span>
                <Button type="submit" disabled={processing} className="btn-sage ml-auto">Save follow-up</Button>
            </div>
        </form>
    );
}

function Oversight({ deposit, refund }) {
    const [reason, setReason] = useState('');
    const [error, setError] = useState(null);
    const act = (action) => router.post(route('operations.deposits.review', deposit.id), { action, reason }, {
        preserveScroll: true,
        onSuccess: () => { setReason(''); setError(null); },
        onError: (e) => setError(e.reason || e.action),
    });
    const closed = ['verified', 'written_off'].includes(refund.status);
    return (
        <Card className="bg-white border-linen shadow-sm">
            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><ShieldCheck className="h-5 w-5" /> Oversight</CardTitle></CardHeader>
            <CardContent className="p-5 space-y-3">
                {refund.verified_at && <p className="text-sm text-emerald-700">Confirmed by {refund.verifier?.name} on {formatDateTime(refund.verified_at)}.</p>}
                <Input value={reason} onChange={(e) => setReason(e.target.value)} placeholder="Reason (needed to dispute, write off or reopen)" className="bg-cream border-linen" />
                {error && <p className="text-xs text-red-600">{error}</p>}
                <div className="flex flex-wrap gap-2">
                    {!closed && <Button size="sm" className="btn-sage" disabled={!(Number(refund.amount_received) > 0)} onClick={() => act('verify')}>Confirm refund landed</Button>}
                    {!closed && refund.status !== 'disputed' && <Button size="sm" variant="outline" className="border-linen" onClick={() => act('dispute')}>Mark disputed</Button>}
                    {!closed && <Button size="sm" variant="outline" className="border-linen text-red-600" onClick={() => act('write_off')}>Write off</Button>}
                    {(closed || refund.status === 'disputed') && <Button size="sm" variant="outline" className="border-linen" onClick={() => act('reopen')}><Undo2 className="h-4 w-4 mr-1" /> Reopen</Button>}
                </div>
            </CardContent>
        </Card>
    );
}

export default function Show({ deposit, clearingOfficer, audit, statuses, channels, formOptions }) {
    const can = useCan();
    const [editing, setEditing] = useState(false);
    const refund = deposit.refund;
    const locked = deposit.locked && !can('deposits.oversee');

    return (
        <AppLayout>
            <Head title={`Deposit · ${deposit.container_number}`} />
            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-6 max-w-6xl mx-auto">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('operations.deposits.index')}><Button variant="outline" size="icon" className="border-linen"><ArrowLeft className="h-4 w-4" /></Button></Link>
                        <div>
                            <div className="flex items-center gap-3 flex-wrap">
                                <h1 className="text-display-sm text-void font-bold font-mono">{deposit.container_number}</h1>
                                <span className={cn('text-xs px-2 py-1 rounded-full font-medium', REFUND_STATUS_COLORS[refund.status])}>{refund.status_label}</span>
                            </div>
                            <p className="text-drift text-sm mt-1">
                                {deposit.shipping_line || 'Shipping line not set'} · deposit {money(deposit.amount, deposit.currency)}
                                {deposit.container_link && <> · <Link href={deposit.container_link} className="text-sage underline">{deposit.job ? `clearing job ${deposit.job.tracking_id}` : 'open container'}</Link></>}
                            </p>
                        </div>
                    </div>
                    {can('records.delete') && <Button variant="outline" className="border-linen text-red-600" onClick={() => confirm('Delete this deposit and its refund record?') && router.delete(route('operations.deposits.destroy', deposit.id))}><Trash2 className="h-4 w-4" /></Button>}
                </div>

                <Card className="bg-white border-linen shadow-sm"><CardContent className="p-5"><Steps status={refund.status} statuses={statuses} /></CardContent></Card>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-6">
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><Undo2 className="h-5 w-5" /> Refund</CardTitle></CardHeader>
                            <CardContent className="p-5 space-y-4">
                                <div className="grid grid-cols-3 gap-3 text-sm">
                                    <div className="rounded-lg bg-cream p-3"><p className="text-xs text-pebble">Should come back</p><p className="font-semibold">{money(deposit.expected, deposit.currency)}</p></div>
                                    <div className="rounded-lg bg-cream p-3"><p className="text-xs text-pebble">Received</p><p className="font-semibold">{money(deposit.amount_received, deposit.currency)}</p></div>
                                    <div className={cn('rounded-lg p-3', deposit.outstanding > 0 ? 'bg-amber-50' : 'bg-emerald-50')}><p className="text-xs text-pebble">Still to come back</p><p className="font-semibold">{money(deposit.outstanding, deposit.currency)}</p></div>
                                </div>
                                <RefundForm deposit={deposit} refund={refund} options={formOptions} locked={locked} />
                            </CardContent>
                        </Card>

                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><PhoneCall className="h-5 w-5" /> Follow-ups ({refund.follow_ups.length})</CardTitle></CardHeader>
                            <CardContent className="p-5 space-y-3">
                                <ol className="space-y-3">
                                    {refund.follow_ups.map((f) => (
                                        <li key={f.id} className="text-sm">
                                            <p className="text-void">{f.outcome}</p>
                                            <p className="text-xs text-pebble">{formatDate(f.contacted_on)} · {channels[f.channel] || f.channel || '—'}{f.contact_person ? ` with ${f.contact_person}` : ''} · {f.user?.name}{f.next_follow_up_on ? ` · next ${formatDate(f.next_follow_up_on)}` : ''}</p>
                                        </li>
                                    ))}
                                    {refund.follow_ups.length === 0 && <p className="text-sm text-pebble">Nobody has chased this refund yet.</p>}
                                </ol>
                                {(can('deposits.manage') || can('deposits.oversee')) && !deposit.locked && <FollowUpForm depositId={deposit.id} channels={channels} />}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        {can('deposits.oversee') && <Oversight deposit={deposit} refund={refund} />}
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen flex flex-row items-center justify-between space-y-0">
                                <CardTitle className="text-void flex items-center gap-2"><Landmark className="h-5 w-5" /> Deposit</CardTitle>
                                {!locked && (can('deposits.manage') || can('deposits.oversee')) && !editing && <Button size="sm" variant="ghost" onClick={() => setEditing(true)}><Pencil className="h-4 w-4" /></Button>}
                            </CardHeader>
                            <CardContent className="p-5">
                                {editing ? <DepositEdit deposit={deposit} options={formOptions} onDone={() => setEditing(false)} /> : (
                                    <div className="divide-y divide-linen">
                                        <Row label="Amount">{money(deposit.amount, deposit.currency)}</Row>
                                        <Row label="Paid on">{formatDate(deposit.paid_on)}</Row>
                                        <Row label="Paid by">{formOptions.paidBy[deposit.paid_by] || deposit.paid_by}</Row>
                                        <Row label="Reference">{deposit.payment_reference}</Row>
                                        <Row label="Shipping line">{deposit.shipping_line}</Row>
                                        <Row label="Bill of Lading"><span className="font-mono">{deposit.bl_number}</span></Row>
                                        <Row label="Recorded by">{deposit.creator?.name}</Row>
                                        <Row label="Follows up">{deposit.owner?.name || 'Nobody yet'}</Row>
                                        {deposit.notes && <Row label="Notes">{deposit.notes}</Row>}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                        {clearingOfficer && (
                            <Card className="bg-white border-linen shadow-sm">
                                <CardContent className="p-5 text-sm flex items-center gap-2">
                                    <UserCheck className="h-4 w-4 text-sage" /> Clearing officer: <span className="font-medium">{clearingOfficer.name}</span>
                                    {clearingOfficer.phone && <a href={`tel:${clearingOfficer.phone}`} className="text-sage ml-auto">{clearingOfficer.phone}</a>}
                                </CardContent>
                            </Card>
                        )}
                        <AuditTrail entries={audit} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
