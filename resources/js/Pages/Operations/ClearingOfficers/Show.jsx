import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { OfficerForm } from './Index';
import { ArrowLeft, Pencil, Container as ContainerIcon, ClipboardList, Phone, Mail, ArrowRightLeft } from 'lucide-react';
import { useCan } from '@/lib/access';
import { formatDateTime } from '@/lib/deposits';
import { cn } from '@/lib/utils';

function WorkList({ rows, actions, showOfficer = false }) {
    return (
        <ol className="divide-y divide-linen">
            {rows.map((w) => (
                <li key={w.id} className="px-5 py-3 text-sm">
                    <p className="text-void">{w.description} <span className="text-[11px] px-1.5 py-0.5 rounded bg-linen text-stone">{actions[w.action] || w.action}</span></p>
                    <p className="text-xs text-pebble">
                        {w.link ? <Link href={w.link} className="text-sage">{w.clearing}</Link> : w.clearing} · {formatDateTime(w.occurred_at)}{w.recorded_by ? ` · recorded by ${w.recorded_by}` : ''}
                        {showOfficer && w.officer ? ` · now credited to ${w.officer}` : ''}
                    </p>
                    {w.overridden_at && (
                        <p className="text-xs text-amber-700">Override by {w.overridden_by} on {formatDateTime(w.overridden_at)} (originally {w.original_officer}) — “{w.override_reason}”</p>
                    )}
                </li>
            ))}
        </ol>
    );
}

export default function Show({ officer, assignments, work, movedAway, counts, actions, users }) {
    const can = useCan();
    const [editOpen, setEditOpen] = useState(false);

    return (
        <AppLayout>
            <Head title={`${officer.name} · Clearing officer`} />
            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-6 max-w-6xl mx-auto">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('operations.clearing-officers.index')}><Button variant="outline" size="icon" className="border-linen"><ArrowLeft className="h-4 w-4" /></Button></Link>
                        <div>
                            <h1 className="text-display-sm text-void font-bold">{officer.name} {!officer.is_active && <span className="text-sm text-pebble font-normal">(inactive)</span>}</h1>
                            <p className="text-drift text-sm mt-1 flex flex-wrap gap-x-3">
                                {officer.company && <span>{officer.company}</span>}
                                {officer.licence_number && <span>Licence {officer.licence_number}</span>}
                                {officer.phone && <a href={`tel:${officer.phone}`} className="text-sage inline-flex items-center gap-1"><Phone className="h-3 w-3" /> {officer.phone}</a>}
                                {officer.email && <a href={`mailto:${officer.email}`} className="text-sage inline-flex items-center gap-1"><Mail className="h-3 w-3" /> {officer.email}</a>}
                                {officer.user && <span>Login: {officer.user.name}</span>}
                            </p>
                        </div>
                    </div>
                    {can('clearing.manage') && <Button variant="outline" className="border-linen" onClick={() => setEditOpen(true)}><Pencil className="mr-2 h-4 w-4" /> Edit</Button>}
                </div>

                <div className="grid grid-cols-3 gap-3">
                    <div className="rounded-2xl border border-linen bg-white p-4"><p className="text-xs text-pebble">Holding now</p><p className="text-xl font-semibold">{counts.current}</p></div>
                    <div className="rounded-2xl border border-linen bg-white p-4"><p className="text-xs text-pebble">Clearings handled</p><p className="text-xl font-semibold">{counts.handled}</p></div>
                    <div className="rounded-2xl border border-linen bg-white p-4"><p className="text-xs text-pebble">Work credited</p><p className="text-xl font-semibold">{counts.work}</p></div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card className="bg-white border-linen shadow-sm">
                        <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><ContainerIcon className="h-5 w-5" /> Clearings</CardTitle></CardHeader>
                        <CardContent className="p-0">
                            <ol className="divide-y divide-linen">
                                {assignments.map((a) => (
                                    <li key={a.id} className="px-5 py-3 text-sm">
                                        <div className="flex items-center gap-2">
                                            {a.link ? <Link href={a.link} className="text-sage font-medium">{a.label}</Link> : <span>{a.label}</span>}
                                            <span className={cn('text-[11px] px-2 py-0.5 rounded-full', a.ended_at ? 'bg-linen text-stone' : 'bg-emerald-50 text-emerald-700')}>{a.ended_at ? 'Reassigned' : 'Current'}</span>
                                            {a.status && <span className="text-xs text-pebble ml-auto">{a.status}</span>}
                                        </div>
                                        <p className="text-xs text-pebble">{formatDateTime(a.started_at)} → {a.ended_at ? formatDateTime(a.ended_at) : 'now'}{a.assigned_by ? ` · assigned by ${a.assigned_by}` : ''}{a.ended_by ? ` · moved by ${a.ended_by}` : ''}</p>
                                        {a.reason && <p className="text-xs text-stone">“{a.reason}”</p>}
                                    </li>
                                ))}
                                {assignments.length === 0 && <p className="p-5 text-sm text-pebble">Not assigned to anything yet.</p>}
                            </ol>
                        </CardContent>
                    </Card>

                    <div className="space-y-6">
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><ClipboardList className="h-5 w-5" /> Work credited to {officer.name.split(' ')[0]}</CardTitle></CardHeader>
                            <CardContent className="p-0">
                                <WorkList rows={work} actions={actions} />
                                {work.length === 0 && <p className="p-5 text-sm text-pebble">No work credited yet.</p>}
                            </CardContent>
                        </Card>
                        {movedAway.length > 0 && (
                            <Card className="bg-white border-amber-200 shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><ArrowRightLeft className="h-5 w-5" /> Their work moved to others by override</CardTitle></CardHeader>
                                <CardContent className="p-0"><WorkList rows={movedAway} actions={actions} showOfficer /></CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
            {can('clearing.manage') && <OfficerForm open={editOpen} onOpenChange={setEditOpen} officer={officer} users={users} />}
        </AppLayout>
    );
}
