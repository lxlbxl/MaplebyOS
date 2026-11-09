import { useEffect } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetFooter } from "@/Components/ui/sheet";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { DollarSign, History as HistoryIcon, Trash2 } from 'lucide-react';
import { useCan } from '@/lib/access';
import { formatDate, todayIso } from '@/lib/operations';
import { formatMoney, cn } from '@/lib/utils';

// Shared pieces for general cargo clearing jobs and boxes riding in car containers.

export function Field({ label, error, children, className }) {
    return (
        <div className={cn('space-y-1.5', className)}>
            <Label className="text-charcoal font-medium">{label}</Label>
            {children}
            {error && <span className="text-error text-xs">{error}</span>}
        </div>
    );
}

export function Row({ label, children }) {
    return (
        <div className="flex justify-between gap-4 py-1.5 text-sm">
            <span className="text-pebble">{label}</span>
            <span className="text-charcoal text-right">{children ?? '—'}</span>
        </div>
    );
}

export function clientName(client) {
    return client ? (client.company_name || client.contact_person) : null;
}

function PickList({ value, onChange, options, placeholder }) {
    return (
        <Select value={value ? String(value) : 'none'} onValueChange={(v) => onChange(v === 'none' ? '' : v)}>
            <SelectTrigger className="bg-cream border-linen h-11"><SelectValue placeholder={placeholder} /></SelectTrigger>
            <SelectContent className="bg-white max-h-72">
                <SelectItem value="none">—</SelectItem>
                {options.map((o) => <SelectItem key={o.value} value={String(o.value)}>{o.label}</SelectItem>)}
            </SelectContent>
        </Select>
    );
}

const CLEARING_FIELDS = ['client_id', 'consignee', 'cargo_description', 'hs_code', 'package_count', 'package_type', 'gross_weight_kg', 'volume_cbm',
    'declared_value', 'declared_currency', 'bl_number', 'form_m_number', 'paar_number', 'customs_entry_number', 'terminal', 'destination_city',
    'duty_assessed', 'eta', 'assigned_to', 'description'];
const BOX_FIELDS = ['client_id', 'consignee', 'cargo_description', 'package_count', 'package_type', 'gross_weight_kg', 'volume_cbm',
    'declared_value', 'declared_currency', 'container_id', 'destination_city', 'assigned_to', 'description'];

/**
 * Create/edit sheet. kind = 'clearing' (a client's own containers) or 'box' (lite cargo in a car container).
 */
export function CargoForm({ kind, open, onOpenChange, item = null, options }) {
    const can = useCan();
    const isEdit = !!item;
    const fields = kind === 'clearing' ? CLEARING_FIELDS : BOX_FIELDS;
    const blank = Object.fromEntries(fields.map((f) => [f, '']));
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        ...blank, declared_currency: 'NGN', destination_city: 'Lagos', containers: '', package_type: kind === 'box' ? 'Box' : 'Cartons',
    });

    useEffect(() => {
        clearErrors();
        if (item) {
            setData(Object.fromEntries(fields.map((k) => [k, item[k] == null ? '' : String(item[k]).slice(0, k === 'eta' ? 10 : undefined)])));
        } else {
            reset();
        }
    }, [item, open]);

    const routes = kind === 'clearing'
        ? { store: 'operations.clearing.store', update: 'operations.clearing.update' }
        : { store: 'operations.boxes.store', update: 'operations.boxes.update' };

    const submit = (e) => {
        e.preventDefault();
        const opts = { preserveScroll: true, onSuccess: () => onOpenChange(false) };
        isEdit ? put(route(routes.update, item.id), opts) : post(route(routes.store), opts);
    };

    const text = (name, props = {}) => (
        <Input value={data[name]} onChange={(e) => setData(name, e.target.value)} className="bg-cream border-linen h-11" {...props} />
    );
    const clients = (options.clients || []).map((c) => ({ value: c.id, label: clientName(c) || `#${c.id}` }));

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="overflow-y-auto bg-white border-l border-linen sm:max-w-lg">
                <SheetHeader>
                    <SheetTitle className="text-display-sm text-void font-bold">
                        {isEdit ? `Edit ${item.tracking_id}` : kind === 'clearing' ? 'New clearing job' : 'New box / lite cargo'}
                    </SheetTitle>
                    <SheetDescription className="text-drift">
                        {kind === 'clearing'
                            ? 'One Bill of Lading of a client\'s goods. Add every container on the BL.'
                            : 'A client\'s box riding in a car container. It loads, sails and clears with that container.'}
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={submit} className="space-y-4 mt-6">
                    <Field label={kind === 'clearing' ? 'Cargo description' : 'What is in it'} error={errors.cargo_description}>
                        {text('cargo_description', { placeholder: kind === 'clearing' ? 'Ceramic tiles, spare parts…' : 'Clothes and shoes, auto parts…' })}
                    </Field>

                    <div className="grid grid-cols-2 gap-3">
                        <Field label={kind === 'clearing' ? 'Client' : 'Owner (client)'} error={errors.client_id}>
                            <PickList value={data.client_id} onChange={(v) => setData('client_id', v)} options={clients} />
                        </Field>
                        <Field label="Consignee / receiver" error={errors.consignee}>{text('consignee')}</Field>

                        {kind === 'box' && (
                            <Field label="Car container" error={errors.container_id} className="col-span-2">
                                <PickList value={data.container_id} onChange={(v) => setData('container_id', v)}
                                    options={(options.containers || []).map((c) => ({ value: c.id, label: `${c.container_number}${c.shipping_line ? ` · ${c.shipping_line.name}` : ''} · ${c.status}` }))} />
                            </Field>
                        )}

                        <Field label="Packages" error={errors.package_count}>{text('package_count', { type: 'number', min: 1 })}</Field>
                        <Field label="Package type" error={errors.package_type}>
                            <PickList value={data.package_type} onChange={(v) => setData('package_type', v)} options={(options.packageTypes || []).map((p) => ({ value: p, label: p }))} />
                        </Field>
                        <Field label="Gross weight (kg)" error={errors.gross_weight_kg}>{text('gross_weight_kg', { type: 'number', step: '0.01' })}</Field>
                        <Field label="Volume (CBM)" error={errors.volume_cbm}>{text('volume_cbm', { type: 'number', step: '0.001' })}</Field>

                        {can('money.view') && <>
                            <Field label="Declared value" error={errors.declared_value}>{text('declared_value', { type: 'number', step: '0.01' })}</Field>
                            <Field label="Currency" error={errors.declared_currency}>
                                <PickList value={data.declared_currency} onChange={(v) => setData('declared_currency', v)} options={['NGN', 'USD', 'CAD', 'EUR', 'GBP', 'CNY'].map((c) => ({ value: c, label: c }))} />
                            </Field>
                        </>}

                        {kind === 'clearing' && <>
                            <Field label="Bill of Lading #" error={errors.bl_number}>{text('bl_number', { className: 'bg-cream border-linen h-11 font-mono uppercase' })}</Field>
                            <Field label="HS code" error={errors.hs_code}>{text('hs_code', { placeholder: '6907.21.00' })}</Field>
                            <Field label="Form M #" error={errors.form_m_number}>{text('form_m_number')}</Field>
                            <Field label="PAAR #" error={errors.paar_number}>{text('paar_number')}</Field>
                            <Field label="Customs entry (SGD) #" error={errors.customs_entry_number}>{text('customs_entry_number')}</Field>
                            <Field label="ETA" error={errors.eta}>{text('eta', { type: 'date', className: 'bg-cream border-linen h-11 [color-scheme:light]' })}</Field>
                            <Field label="Terminal" error={errors.terminal} className="col-span-2">
                                {text('terminal', { list: 'terminal-options', placeholder: 'Tin Can Island (TICT)' })}
                                <datalist id="terminal-options">{(options.terminals || []).map((t) => <option key={t} value={t} />)}</datalist>
                            </Field>
                            {can('money.view') && <Field label="Duty assessed (NGN)" error={errors.duty_assessed}>{text('duty_assessed', { type: 'number', step: '0.01' })}</Field>}
                        </>}

                        <Field label="Handled by" error={errors.assigned_to}>
                            <PickList value={data.assigned_to} onChange={(v) => setData('assigned_to', v)} options={(options.users || []).map((u) => ({ value: u.id, label: u.name }))} />
                        </Field>
                    </div>

                    {kind === 'clearing' && !isEdit && (
                        <Field label="Containers on this BL" error={errors.containers}>
                            <Textarea value={data.containers} onChange={(e) => setData('containers', e.target.value)} rows={3}
                                placeholder={'One per line, size optional:\nMSCU1234565 40HC\nTGHU1234567 20'} className="bg-cream border-linen font-mono" />
                        </Field>
                    )}

                    <Field label="Notes" error={errors.description}>
                        <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="bg-cream border-linen" />
                    </Field>
                    <SheetFooter><Button type="submit" disabled={processing} className="w-full btn-sage">{isEdit ? 'Save' : kind === 'clearing' ? 'Open clearing job' : 'Add box'}</Button></SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}

export function StatusChanger({ routeName, id, status, statuses }) {
    const { data, setData, patch, processing } = useForm({ status, date: todayIso(), note: '' });
    useEffect(() => setData('status', status), [status]);
    return (
        <form onSubmit={(e) => { e.preventDefault(); patch(route(routeName, id), { preserveScroll: true, onSuccess: () => setData('note', '') }); }}
            className="flex flex-wrap items-end gap-2">
            <Select value={data.status} onValueChange={(v) => setData('status', v)}>
                <SelectTrigger className="w-48 bg-cream border-linen"><SelectValue /></SelectTrigger>
                <SelectContent className="bg-white">{Object.entries(statuses).map(([v, l]) => <SelectItem key={v} value={v}>{l}</SelectItem>)}</SelectContent>
            </Select>
            <Input type="date" value={data.date} onChange={(e) => setData('date', e.target.value)} className="w-40 bg-cream border-linen [color-scheme:light]" />
            <Input value={data.note} onChange={(e) => setData('note', e.target.value)} placeholder="Note (optional)" className="flex-1 min-w-[160px] bg-cream border-linen" />
            <Button type="submit" disabled={processing} className="btn-sage">Update status</Button>
        </form>
    );
}

/** Stage progress bar: reached stages are filled, the current one is highlighted. */
export function Pipeline({ statuses, status, milestones }) {
    const steps = Object.entries(statuses).filter(([k]) => !['on_hold', 'issue'].includes(k));
    const current = steps.findIndex(([k]) => k === status);
    const reached = new Set((milestones || []).map((m) => m.status));
    return (
        <ol className="grid gap-1" style={{ gridTemplateColumns: `repeat(${steps.length}, minmax(0, 1fr))` }}>
            {steps.map(([key, label], i) => (
                <li key={key} className="text-center">
                    <div className={cn('h-1.5 rounded-full', i <= current || reached.has(key) ? 'bg-sage' : 'bg-linen', key === status && 'ring-2 ring-sage/30')} />
                    <p className={cn('mt-1 text-[10px] leading-tight', key === status ? 'text-void font-semibold' : 'text-pebble')}>{label}</p>
                </li>
            ))}
        </ol>
    );
}

export function HistoryCard({ milestones, statuses }) {
    return (
        <Card className="bg-white border-linen shadow-sm">
            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><HistoryIcon className="h-5 w-5" /> History</CardTitle></CardHeader>
            <CardContent className="p-5">
                <ol className="relative border-l border-linen ml-2 space-y-4">
                    {(milestones || []).map((m) => (
                        <li key={m.id} className="ml-4">
                            <span className="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full bg-sage" />
                            <p className="text-sm font-medium text-void">{statuses[m.status] || m.status} <span className="text-pebble font-normal">· {formatDate(m.occurred_on)}</span></p>
                            {(m.note || m.user) && <p className="text-xs text-stone">{m.note}{m.user ? ` — ${m.user.name}` : ''}</p>}
                        </li>
                    ))}
                </ol>
            </CardContent>
        </Card>
    );
}

/**
 * Cost and charge lines. totals: { CUR: { cost, charge, profit } } (clearing, which can mix
 * NGN and USD) or { total_cost, total_charges, profit } in a single currency (boxes).
 */
export function ChargesCard({ shipmentId, lines, totals, categories, defaultCurrency }) {
    const can = useCan();
    const kinds = [can('finance.manage') && 'charge', can('money.edit_costs') && 'cost'].filter(Boolean);
    const { data, setData, post, processing, errors, reset } = useForm({ kind: kinds[0] || 'charge', category: '', description: '', amount: '', currency: defaultCurrency, incurred_on: todayIso() });
    const add = (e) => {
        e.preventDefault();
        post(route('finance.charges.store', shipmentId), { preserveScroll: true, onSuccess: () => reset('category', 'description', 'amount') });
    };
    const groups = totals && 'total_cost' in totals
        ? { [defaultCurrency]: { cost: totals.total_cost, charge: totals.total_charges, profit: totals.profit } }
        : (totals || {});

    return (
        <Card className="bg-white border-linen shadow-sm">
            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><DollarSign className="h-5 w-5" /> Money</CardTitle></CardHeader>
            <CardContent className="p-5 space-y-4">
                {Object.entries(groups).map(([cur, t]) => (
                    <div key={cur} className="rounded-lg bg-cream p-3 text-sm space-y-1">
                        <div className="flex justify-between gap-2"><span className="text-pebble">Costs</span><span className="font-semibold">{formatMoney(t.cost, cur)}</span></div>
                        <div className="flex justify-between gap-2"><span className="text-pebble">Charged</span><span className="font-semibold">{formatMoney(t.charge, cur)}</span></div>
                        <div className="flex justify-between gap-2 border-t border-linen pt-1"><span className="text-pebble">Margin</span><span className={cn('font-semibold', t.profit >= 0 ? 'text-emerald-700' : 'text-red-700')}>{formatMoney(t.profit, cur)}</span></div>
                    </div>
                ))}

                <div className="divide-y divide-linen text-sm">
                    {(lines || []).map((c) => (
                        <div key={c.id} className="flex justify-between items-center py-2 gap-2">
                            <span>
                                {c.category}{c.description ? ` · ${c.description}` : ''}{' '}
                                <span className={cn('text-xs', c.kind === 'cost' ? 'text-pebble' : 'text-sage')}>({c.kind})</span>
                                {c.invoice && <Link href={route('finance.invoices.show', c.invoice.id)} className="ml-2 text-xs text-sage underline">{c.invoice.invoice_number}</Link>}
                            </span>
                            <span className="flex items-center gap-2">
                                {formatMoney(c.amount, c.currency || defaultCurrency)}
                                {!c.invoice_id && ((c.kind === 'cost' && can('money.edit_costs')) || (c.kind === 'charge' && can('finance.manage'))) && (
                                    <button type="button" onClick={() => confirm('Remove this line?') && router.delete(route('finance.charges.destroy', c.id), { preserveScroll: true })} className="text-pebble hover:text-red-600"><Trash2 className="h-3.5 w-3.5" /></button>
                                )}
                            </span>
                        </div>
                    ))}
                    {!(lines || []).length && <p className="py-2 text-pebble">No costs or charges yet.</p>}
                </div>

                {kinds.length > 0 && (
                    <form onSubmit={add} className="grid grid-cols-2 gap-2 pt-2 border-t border-linen">
                        <Select value={data.kind} onValueChange={(v) => setData((d) => ({ ...d, kind: v, category: '' }))}>
                            <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                            <SelectContent className="bg-white">
                                {kinds.includes('charge') && <SelectItem value="charge">Charge to client</SelectItem>}
                                {kinds.includes('cost') && <SelectItem value="cost">Cost to Mapleby</SelectItem>}
                            </SelectContent>
                        </Select>
                        <Select value={data.category} onValueChange={(v) => setData('category', v)}>
                            <SelectTrigger className="bg-cream border-linen"><SelectValue placeholder="Category" /></SelectTrigger>
                            <SelectContent className="bg-white">{(categories[data.kind] || []).map((c) => <SelectItem key={c} value={c}>{c}</SelectItem>)}</SelectContent>
                        </Select>
                        <Input value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Description" className="bg-cream border-linen col-span-2" />
                        <Input type="number" step="0.01" value={data.amount} onChange={(e) => setData('amount', e.target.value)} placeholder="Amount" className="bg-cream border-linen" />
                        <Select value={data.currency} onValueChange={(v) => setData('currency', v)}>
                            <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                            <SelectContent className="bg-white">{['NGN', 'USD', 'CAD'].map((c) => <SelectItem key={c} value={c}>{c}</SelectItem>)}</SelectContent>
                        </Select>
                        {(errors.category || errors.amount) && <p className="col-span-2 text-xs text-red-600">{errors.category || errors.amount}</p>}
                        <Button type="submit" disabled={processing} variant="outline" className="col-span-2 border-linen">Add line</Button>
                    </form>
                )}
            </CardContent>
        </Card>
    );
}
