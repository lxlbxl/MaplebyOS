import { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { UserCheck, Repeat, ClipboardList, Phone, ArrowRightLeft } from 'lucide-react';
import AuditTrail from '@/Components/Operations/AuditTrail';
import { useCan } from '@/lib/access';
import { formatDateTime } from '@/lib/deposits';
import { cn } from '@/lib/utils';

function OfficerPicker({ value, onChange, officers, exclude }) {
    return (
        <Select value={value ? String(value) : ''} onValueChange={onChange}>
            <SelectTrigger className="bg-cream border-linen"><SelectValue placeholder="Pick a clearing officer" /></SelectTrigger>
            <SelectContent className="bg-white max-h-72">
                {officers.filter((o) => o.id !== exclude).map((o) => (
                    <SelectItem key={o.id} value={String(o.id)}>{o.name}{o.company ? ` · ${o.company}` : ''}</SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

function AssignForm({ panel, current }) {
    const can = useCan();
    const { data, setData, post, processing, errors, reset } = useForm({
        clearable_type: panel.clearable.type,
        clearable_id: panel.clearable.id,
        clearing_officer_id: '',
        reason: '',
        move_earlier_work: false,
        override_reason: '',
    });
    const submit = (e) => {
        e.preventDefault();
        post(route('operations.clearing-officers.assign'), { preserveScroll: true, onSuccess: () => reset('clearing_officer_id', 'reason', 'move_earlier_work', 'override_reason') });
    };

    if (!panel.officers.length) {
        return (
            <p className="text-sm text-pebble">
                No clearing officers yet. <Link href={route('operations.clearing-officers.index')} className="text-sage underline">Add one</Link> first.
            </p>
        );
    }

    return (
        <form onSubmit={submit} className="space-y-2">
            <OfficerPicker value={data.clearing_officer_id} onChange={(v) => setData('clearing_officer_id', v)} officers={panel.officers} exclude={current?.clearing_officer_id} />
            {errors.clearing_officer_id && <p className="text-xs text-red-600">{errors.clearing_officer_id}</p>}
            {current && (
                <>
                    <Input value={data.reason} onChange={(e) => setData('reason', e.target.value)} placeholder="Why is it being reassigned?" className="bg-cream border-linen" />
                    {errors.reason && <p className="text-xs text-red-600">{errors.reason}</p>}
                    <p className="text-xs text-stone">Work done so far stays credited to {current.officer?.name}.</p>
                    {can('clearing.override') && (
                        <label className="flex items-start gap-2 text-xs text-charcoal">
                            <input type="checkbox" className="mt-0.5" checked={data.move_earlier_work} onChange={(e) => setData('move_earlier_work', e.target.checked)} />
                            <span>Override: credit the earlier work to the new officer instead (shows on the audit trail)</span>
                        </label>
                    )}
                    {data.move_earlier_work && (
                        <>
                            <Input value={data.override_reason} onChange={(e) => setData('override_reason', e.target.value)} placeholder="Reason for moving the credit" className="bg-cream border-linen" />
                            {errors.override_reason && <p className="text-xs text-red-600">{errors.override_reason}</p>}
                        </>
                    )}
                </>
            )}
            <Button type="submit" disabled={processing || !data.clearing_officer_id} className="btn-sage w-full">
                {current ? <><Repeat className="h-4 w-4 mr-2" /> Reassign</> : <><UserCheck className="h-4 w-4 mr-2" /> Assign</>}
            </Button>
        </form>
    );
}

function LogWork({ panel }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        clearable_type: panel.clearable.type, clearable_id: panel.clearable.id, description: '', occurred_at: '',
    });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post(route('operations.clearing-work.store'), { preserveScroll: true, onSuccess: () => reset('description', 'occurred_at') }); }}
            className="flex flex-wrap gap-2 pt-3 border-t border-linen">
            <Input value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Log work, e.g. Collected delivery order" className="flex-1 min-w-[180px] bg-cream border-linen" />
            <Input type="datetime-local" value={data.occurred_at} onChange={(e) => setData('occurred_at', e.target.value)} className="w-52 bg-cream border-linen [color-scheme:light]" />
            <Button type="submit" variant="outline" className="border-linen" disabled={processing}>Log</Button>
            {errors.description && <p className="text-xs text-red-600 w-full">{errors.description}</p>}
        </form>
    );
}

function MoveCredit({ panel, selected, onDone }) {
    const { data, setData, post, processing, errors, transform } = useForm({ work_ids: [], clearing_officer_id: '', reason: '' });
    const submit = (e) => {
        e.preventDefault();
        transform((d) => ({ ...d, work_ids: selected }));
        post(route('operations.clearing-work.override'), { preserveScroll: true, onSuccess: () => { setData({ work_ids: [], clearing_officer_id: '', reason: '' }); onDone(); } });
    };
    return (
        <form onSubmit={submit} className="rounded-lg border border-amber-200 bg-amber-50/60 p-3 space-y-2">
            <p className="text-xs font-medium text-amber-800">Move credit for {selected.length} selected {selected.length === 1 ? 'item' : 'items'} (override)</p>
            <OfficerPicker value={data.clearing_officer_id} onChange={(v) => setData('clearing_officer_id', v)} officers={panel.officers} />
            <Input value={data.reason} onChange={(e) => setData('reason', e.target.value)} placeholder="Reason (required)" className="bg-white border-linen" />
            {(errors.reason || errors.clearing_officer_id || errors.work_ids) && <p className="text-xs text-red-600">{errors.reason || errors.clearing_officer_id || errors.work_ids}</p>}
            <Button type="submit" size="sm" disabled={processing || !data.clearing_officer_id} className="btn-sage"><ArrowRightLeft className="h-4 w-4 mr-1" /> Move credit</Button>
        </form>
    );
}

/**
 * Clearing officer for a car container or clearing job: who holds it now,
 * every past officer, the work credited to each, and the audit trail.
 */
export default function ClearingOfficerPanel({ panel }) {
    const can = useCan();
    const [selected, setSelected] = useState([]);
    if (!panel) return null;
    const current = panel.current;
    const toggle = (id) => setSelected((s) => (s.includes(id) ? s.filter((x) => x !== id) : [...s, id]));

    return (
        <div className="space-y-6">
            <Card className="bg-white border-linen shadow-sm">
                <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><UserCheck className="h-5 w-5" /> Clearing officer</CardTitle></CardHeader>
                <CardContent className="p-5 space-y-4">
                    {current ? (
                        <div>
                            <Link href={route('operations.clearing-officers.show', current.clearing_officer_id)} className="text-lg font-semibold text-void hover:underline">{current.officer?.name}</Link>
                            <p className="text-xs text-pebble">
                                {current.officer?.company ? `${current.officer.company} · ` : ''}since {formatDateTime(current.started_at)}{current.assigner ? ` · by ${current.assigner.name}` : ''}
                            </p>
                            {current.officer?.phone && <a href={`tel:${current.officer.phone}`} className="text-xs text-sage inline-flex items-center gap-1 mt-1"><Phone className="h-3 w-3" /> {current.officer.phone}</a>}
                        </div>
                    ) : (
                        <p className="text-sm text-amber-700">No clearing officer assigned yet. Work is only credited once someone is assigned.</p>
                    )}
                    {can('clearing.manage') && <AssignForm panel={panel} current={current} />}

                    {panel.assignments.length > 1 && (
                        <div className="pt-3 border-t border-linen">
                            <p className="text-xs font-medium text-stone mb-2">Officer history</p>
                            <ol className="space-y-2">
                                {panel.assignments.map((a) => (
                                    <li key={a.id} className="text-xs">
                                        <span className={cn('font-medium', a.ended_at ? 'text-stone' : 'text-void')}>{a.officer?.name}</span>
                                        <span className="text-pebble"> · {formatDateTime(a.started_at)} → {a.ended_at ? formatDateTime(a.ended_at) : 'now'}</span>
                                        {a.reason && <span className="block text-pebble">“{a.reason}”{a.assigner ? ` — ${a.assigner.name}` : ''}</span>}
                                    </li>
                                ))}
                            </ol>
                        </div>
                    )}
                </CardContent>
            </Card>

            <Card className="bg-white border-linen shadow-sm">
                <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><ClipboardList className="h-5 w-5" /> Clearing work and credit</CardTitle></CardHeader>
                <CardContent className="p-5 space-y-3">
                    {panel.work.length === 0 && <p className="text-sm text-pebble">No work credited yet. Stages, documents, paperwork and deposit steps are credited to the officer holding the clearing at the time.</p>}
                    <ol className="space-y-2">
                        {panel.work.map((w) => {
                            const moved = w.overridden_at;
                            return (
                                <li key={w.id} className="flex items-start gap-2 text-sm">
                                    {can('clearing.override') && <input type="checkbox" className="mt-1" checked={selected.includes(w.id)} onChange={() => toggle(w.id)} />}
                                    <div className="flex-1 min-w-0">
                                        <p className="text-void">{w.description} <span className="text-[11px] px-1.5 py-0.5 rounded bg-linen text-stone">{panel.actions[w.action] || w.action}</span></p>
                                        <p className="text-xs text-pebble">
                                            Credited to <span className="text-charcoal font-medium">{w.officer?.name || '—'}</span> · {formatDateTime(w.occurred_at)}{w.user ? ` · recorded by ${w.user.name}` : ''}
                                        </p>
                                        {moved && (
                                            <p className="text-xs text-amber-700">Override: originally {w.original_officer?.name}, moved by {w.overrider?.name} on {formatDateTime(w.overridden_at)} — “{w.override_reason}”</p>
                                        )}
                                    </div>
                                </li>
                            );
                        })}
                    </ol>
                    {can('clearing.override') && selected.length > 0 && <MoveCredit panel={panel} selected={selected} onDone={() => setSelected([])} />}
                    {can('clearing.manage') && current && <LogWork panel={panel} />}
                </CardContent>
            </Card>

            <AuditTrail entries={panel.audit} title="Clearing audit trail" />
        </div>
    );
}
