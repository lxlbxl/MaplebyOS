import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetFooter } from "@/Components/ui/sheet";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Field } from '@/Components/Operations/CargoParts';
import AuditTrail from '@/Components/Operations/AuditTrail';
import { Plus, Search, UserX, ChevronRight, Phone } from 'lucide-react';
import { useCan } from '@/lib/access';
import { cn } from '@/lib/utils';

export function OfficerForm({ open, onOpenChange, officer = null, users }) {
    const blank = { name: '', phone: '', email: '', company: '', licence_number: '', user_id: '', is_active: true, notes: '' };
    const { data, setData, post, put, processing, errors, reset } = useForm(blank);
    useEffect(() => {
        if (officer) {
            setData(Object.fromEntries(Object.keys(blank).map((k) => [k, k === 'is_active' ? !!officer[k] : officer[k] == null ? '' : String(officer[k])])));
        } else {
            reset();
        }
    }, [officer, open]);
    const submit = (e) => {
        e.preventDefault();
        const done = { onSuccess: () => onOpenChange(false) };
        officer ? put(route('operations.clearing-officers.update', officer.id), done) : post(route('operations.clearing-officers.store'), done);
    };
    const text = (key, label, props = {}) => (
        <Field label={label} error={errors[key]}><Input value={data[key]} onChange={(e) => setData(key, e.target.value)} className="bg-cream border-linen" {...props} /></Field>
    );
    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="bg-white overflow-y-auto sm:max-w-lg">
                <SheetHeader>
                    <SheetTitle>{officer ? 'Edit clearing officer' : 'Add a clearing officer'}</SheetTitle>
                    <SheetDescription>The person who clears containers at the port. Staff or an outside agent.</SheetDescription>
                </SheetHeader>
                <form onSubmit={submit} className="space-y-4 py-4">
                    {text('name', 'Name')}
                    {text('phone', 'Phone', { type: 'tel' })}
                    {text('email', 'Email', { type: 'email' })}
                    {text('company', 'Company / agency')}
                    {text('licence_number', 'Customs licence / agent code')}
                    <Field label="Staff login (if they have one)" error={errors.user_id}>
                        <Select value={data.user_id || 'none'} onValueChange={(v) => setData('user_id', v === 'none' ? '' : v)}>
                            <SelectTrigger className="bg-cream border-linen"><SelectValue /></SelectTrigger>
                            <SelectContent className="bg-white max-h-72"><SelectItem value="none">No login</SelectItem>{users.map((u) => <SelectItem key={u.id} value={String(u.id)}>{u.name}</SelectItem>)}</SelectContent>
                        </Select>
                    </Field>
                    {text('notes', 'Notes')}
                    {officer && (
                        <label className="flex items-center gap-2 text-sm">
                            <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} /> Active (can be assigned new clearings)
                        </label>
                    )}
                    <SheetFooter><Button type="submit" disabled={processing} className="btn-sage w-full">{officer ? 'Save' : 'Add clearing officer'}</Button></SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}

export default function Index({ officers, filters, unassigned, recentChanges, users }) {
    const can = useCan();
    const [formOpen, setFormOpen] = useState(false);
    const [search, setSearch] = useState(filters.search || '');

    useEffect(() => {
        const t = setTimeout(() => {
            if (search !== (filters.search || '')) router.get(route('operations.clearing-officers.index'), { ...filters, search }, { preserveState: true, replace: true });
        }, 400);
        return () => clearTimeout(t);
    }, [search]);

    return (
        <AppLayout>
            <Head title="Clearing officers" />
            <div className="flex flex-col gap-5">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Clearing officers</h1>
                        <p className="text-drift mt-1">Who clears each container. Work stays credited to whoever held the clearing when it was done.</p>
                    </div>
                    {can('clearing.manage') && <Button onClick={() => setFormOpen(true)} className="btn-sage shadow-soft"><Plus className="mr-2 h-4 w-4" /> Add clearing officer</Button>}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-4">
                        <div className="flex gap-3 items-center">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-pebble" />
                                <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search name, company, phone, licence…" className="pl-9 bg-white border-linen h-10" />
                            </div>
                            <button onClick={() => router.get(route('operations.clearing-officers.index'), { ...filters, show: filters.show === 'all' ? '' : 'all' }, { preserveState: true })}
                                className={cn('text-xs rounded-full px-3 py-1.5 border', filters.show === 'all' ? 'bg-void text-white border-void' : 'bg-white border-linen text-stone')}>Include inactive</button>
                        </div>
                        <div className="rounded-xl border border-linen bg-white divide-y divide-linen">
                            {officers.map((o) => (
                                <Link key={o.id} href={route('operations.clearing-officers.show', o.id)} className="flex items-center gap-3 px-4 py-3 hover:bg-cream/60">
                                    <div className="flex-1 min-w-0">
                                        <p className="font-medium text-void">{o.name} {!o.is_active && <span className="text-xs text-pebble">(inactive)</span>}</p>
                                        <p className="text-xs text-pebble">{[o.company, o.phone, o.licence_number].filter(Boolean).join(' · ') || '—'}</p>
                                    </div>
                                    <div className="text-right text-xs">
                                        <p className="text-void font-semibold">{o.current_assignments_count} now</p>
                                        <p className="text-pebble">{o.assignments_count} assigned · {o.work_count} credited</p>
                                    </div>
                                    <ChevronRight className="h-4 w-4 text-pebble" />
                                </Link>
                            ))}
                            {officers.length === 0 && <p className="p-8 text-center text-sm text-pebble">No clearing officers yet.</p>}
                        </div>
                    </div>

                    <div className="space-y-6">
                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><UserX className="h-5 w-5" /> Needs a clearing officer ({unassigned.length})</CardTitle></CardHeader>
                            <CardContent className="p-0 divide-y divide-linen">
                                {unassigned.map((u) => (
                                    <Link key={u.link} href={u.link} className="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-cream/60">
                                        <span className="flex-1 truncate">{u.label}</span><span className="text-xs text-pebble">{u.status}</span>
                                    </Link>
                                ))}
                                {unassigned.length === 0 && <p className="p-5 text-sm text-pebble">Every arrived container and open clearing job has an officer.</p>}
                            </CardContent>
                        </Card>
                        <AuditTrail entries={recentChanges} title="Recent reassignments and overrides" empty="No reassignments or overrides yet." />
                    </div>
                </div>
            </div>
            {can('clearing.manage') && <OfficerForm open={formOpen} onOpenChange={setFormOpen} users={users} />}
        </AppLayout>
    );
}
