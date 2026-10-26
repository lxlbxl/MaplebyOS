import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Card, CardContent } from "@/Components/ui/card";
import { Pencil, Trash2, Plus, Check, X } from 'lucide-react';
import { cn } from '@/lib/utils';

const TABS = [
    { key: 'makes', label: 'Makes & models' },
    { key: 'shipping_lines', label: 'Shipping lines' },
    { key: 'platforms', label: 'Purchase platforms' },
    { key: 'ports', label: 'Ports & cities' },
];

const EXTRA = {
    shipping_lines: [['scac', 'SCAC'], ['container_prefixes', 'Container prefixes (comma separated)'], ['tracking_url', 'Tracking URL ({number})']],
    ports: [['country', 'Country'], ['kind', 'origin / destination']],
};

function toText(v) {
    return Array.isArray(v) ? v.join(', ') : (v ?? '');
}

function EntryRow({ type, entry, children }) {
    const [editing, setEditing] = useState(false);
    const extra = EXTRA[type] || [];
    const form = useForm({ name: entry.name, aliases: toText(entry.aliases), ...Object.fromEntries(extra.map(([k]) => [k, toText(entry[k])])) });

    if (editing) {
        return (
            <form onSubmit={(e) => { e.preventDefault(); form.put(route('operations.lookups.update', [type, entry.id]), { preserveScroll: true, onSuccess: () => setEditing(false) }); }}
                className="flex flex-wrap items-center gap-2 py-2">
                <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="w-48 bg-cream uppercase" />
                <Input value={form.data.aliases} onChange={(e) => form.setData('aliases', e.target.value)} placeholder="Other spellings, comma separated" className="flex-1 min-w-[200px] bg-cream uppercase" />
                {extra.map(([k, label]) => <Input key={k} value={form.data[k]} onChange={(e) => form.setData(k, e.target.value)} placeholder={label} className="w-56 bg-cream" />)}
                <Button size="icon" type="submit" className="btn-sage h-9 w-9"><Check className="h-4 w-4" /></Button>
                <Button size="icon" type="button" variant="ghost" onClick={() => setEditing(false)} className="h-9 w-9"><X className="h-4 w-4" /></Button>
                {Object.values(form.errors).length > 0 && <p className="w-full text-xs text-red-600">{Object.values(form.errors).join(' ')}</p>}
            </form>
        );
    }

    return (
        <div className="py-2">
            <div className="flex items-start gap-3">
                <div className="flex-1">
                    <span className="font-medium text-void">{entry.name}</span>
                    {entry.scac && <span className="ml-2 text-xs text-pebble">{entry.scac}</span>}
                    {entry.country && <span className="ml-2 text-xs text-pebble">{entry.country} · {entry.kind}</span>}
                    <div className="flex flex-wrap gap-1 mt-1">
                        {(entry.aliases || []).map((a) => <span key={a} className="text-[11px] px-1.5 py-0.5 rounded bg-cream border border-linen text-stone">{a}</span>)}
                        {(entry.container_prefixes || []).map((p) => <span key={p} className="text-[11px] px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 font-mono">{p}</span>)}
                    </div>
                </div>
                <button type="button" onClick={() => setEditing(true)} className="text-pebble hover:text-sage"><Pencil className="h-4 w-4" /></button>
                <button type="button" onClick={() => confirm(`Remove ${entry.name}? Vehicles keep their current values.`) && router.delete(route('operations.lookups.destroy', [type, entry.id]), { preserveScroll: true })} className="text-pebble hover:text-red-600"><Trash2 className="h-4 w-4" /></button>
            </div>
            {children}
        </div>
    );
}

function AddForm({ type, make, placeholder }) {
    const form = useForm({ name: '', aliases: '', make: make || '', force: false });
    const nearDuplicate = form.errors.name && /misspelling/.test(form.errors.name);
    return (
        <form onSubmit={(e) => { e.preventDefault(); form.post(route('operations.lookups.store', type), { preserveScroll: true, onSuccess: () => form.reset('name', 'aliases', 'force') }); }}
            className="flex flex-wrap items-center gap-2 py-2">
            <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder={placeholder} className="w-48 bg-white uppercase h-9" />
            <Input value={form.data.aliases} onChange={(e) => form.setData('aliases', e.target.value)} placeholder="Other spellings (optional)" className="flex-1 min-w-[180px] bg-white uppercase h-9" />
            <Button type="submit" size="sm" variant="outline" disabled={form.processing || !form.data.name} className="h-9 border-linen"><Plus className="h-4 w-4 mr-1" /> Add</Button>
            {form.errors.name && (
                <p className="w-full text-xs text-red-600">
                    {form.errors.name}
                    {nearDuplicate && (
                        <label className="ml-2 inline-flex items-center gap-1 text-stone"><input type="checkbox" checked={form.data.force} onChange={(e) => form.setData('force', e.target.checked)} /> it's really different</label>
                    )}
                </p>
            )}
            {form.errors.make && <p className="w-full text-xs text-red-600">{form.errors.make}</p>}
        </form>
    );
}

export default function Index({ makes, shippingLines, platforms, ports }) {
    const [tab, setTab] = useState('makes');
    const [openMake, setOpenMake] = useState(null);
    const [filter, setFilter] = useState('');
    const lists = { shipping_lines: shippingLines, platforms, ports };
    const q = filter.trim().toUpperCase();
    const match = (e) => !q || e.name.includes(q) || (e.aliases || []).some((a) => a.includes(q));

    return (
        <AppLayout>
            <Head title="Catalog" />
            <div className="flex flex-col gap-5 max-w-5xl">
                <div>
                    <h1 className="text-display-md text-void">Catalog</h1>
                    <p className="text-drift mt-1">The lists behind every suggestion box. Add common misspellings as “other spellings” and they will always be corrected to the right name, including on import. Renaming an entry renames it on every vehicle.</p>
                </div>

                <div className="flex flex-wrap gap-1 border-b border-linen">
                    {TABS.map((t) => (
                        <button key={t.key} onClick={() => setTab(t.key)} className={cn('px-3 py-2 text-sm -mb-px border-b-2', tab === t.key ? 'border-sage text-void font-medium' : 'border-transparent text-stone')}>{t.label}</button>
                    ))}
                </div>

                <Input value={filter} onChange={(e) => setFilter(e.target.value)} placeholder="Filter…" className="bg-white border-linen max-w-sm" />

                <Card className="bg-white border-linen shadow-sm">
                    <CardContent className="p-5 divide-y divide-linen">
                        {tab === 'makes' ? (
                            <>
                                <AddForm type="makes" placeholder="New make" />
                                {makes.filter((m) => match(m) || m.models.some(match)).map((make) => (
                                    <EntryRow key={make.id} type="makes" entry={make}>
                                        <button type="button" onClick={() => setOpenMake(openMake === make.id ? null : make.id)} className="mt-1 text-xs text-sage hover:underline">
                                            {openMake === make.id ? 'Hide' : 'Show'} {make.models.length} models
                                        </button>
                                        {(openMake === make.id || (q && make.models.some(match))) && (
                                            <div className="ml-4 mt-2 pl-3 border-l border-linen divide-y divide-linen">
                                                <AddForm type="models" make={make.name} placeholder={`New ${make.name} model`} />
                                                {make.models.filter((m) => !q || match(m) || match(make)).map((model) => <EntryRow key={model.id} type="models" entry={model} />)}
                                            </div>
                                        )}
                                    </EntryRow>
                                ))}
                            </>
                        ) : (
                            <>
                                <AddForm type={tab} placeholder={`New ${TABS.find((t) => t.key === tab).label.toLowerCase().replace(/s$/, '')}`} />
                                {lists[tab].filter(match).map((entry) => <EntryRow key={entry.id} type={tab} entry={entry} />)}
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
