import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { ScrollText } from 'lucide-react';
import { formatDateTime } from '@/lib/deposits';
import { formatDate } from '@/lib/operations';
import { cn } from '@/lib/utils';

const EVENT_TONES = {
    reassigned: 'bg-amber-50 text-amber-700',
    credit_overridden: 'bg-red-50 text-red-700',
    assigned: 'bg-sky-50 text-sky-700',
    verify: 'bg-emerald-50 text-emerald-700',
    write_off: 'bg-gray-100 text-gray-700',
    dispute: 'bg-red-50 text-red-700',
    reopen: 'bg-amber-50 text-amber-700',
    deleted: 'bg-red-50 text-red-700',
};

const EVENT_LABELS = {
    assigned: 'Assigned',
    reassigned: 'Reassigned',
    credit_overridden: 'Credit override',
    created: 'Recorded',
    updated: 'Updated',
    followed_up: 'Follow-up',
    verify: 'Confirmed',
    dispute: 'Disputed',
    write_off: 'Written off',
    reopen: 'Reopened',
    deleted: 'Deleted',
};

function show(value) {
    if (value === null || value === undefined || value === '') return '—';
    if (/^\d{4}-\d{2}-\d{2}/.test(String(value))) return formatDate(value);
    return String(value);
}

/**
 * Who did what and when: assignments, reassignments, credit overrides and
 * deposit changes, newest first. Entries cannot be edited or removed.
 */
export default function AuditTrail({ entries = [], title = 'Audit trail', empty = 'Nothing recorded yet.' }) {
    return (
        <Card className="bg-white border-linen shadow-sm">
            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><ScrollText className="h-5 w-5" /> {title}</CardTitle></CardHeader>
            <CardContent className="p-0">
                <ol className="divide-y divide-linen">
                    {entries.map((a) => {
                        const p = a.properties || {};
                        return (
                            <li key={a.id} className="px-5 py-3 text-sm space-y-1">
                                <div className="flex items-start gap-2">
                                    <span className={cn('text-[11px] px-2 py-0.5 rounded-full whitespace-nowrap', EVENT_TONES[a.event] || 'bg-linen text-stone')}>{EVENT_LABELS[a.event] || a.event}</span>
                                    <span className="text-void flex-1">{a.description}</span>
                                </div>
                                <p className="text-xs text-pebble">{a.by || 'System'} · {formatDateTime(a.at)}</p>
                                {p.reason && <p className="text-xs text-stone">Reason: {p.reason}</p>}
                                {p.outcome && <p className="text-xs text-stone">“{p.outcome}”{p.next_follow_up_on ? ` · next ${formatDate(p.next_follow_up_on)}` : ''}</p>}
                                {p.changes && (
                                    <ul className="text-xs text-stone space-y-0.5">
                                        {Object.entries(p.changes).map(([field, c]) => (
                                            <li key={field}>{c.label}: <span className="line-through text-pebble">{show(c.old)}</span> → <span className="text-charcoal">{show(c.new)}</span></li>
                                        ))}
                                    </ul>
                                )}
                                {Array.isArray(p.work) && p.work.length > 0 && (
                                    <ul className="text-xs text-stone list-disc ml-4">
                                        {p.work.slice(0, 10).map((w) => <li key={w.work_id}>{w.description} <span className="text-pebble">(was {w.from_officer || 'no officer'})</span></li>)}
                                        {p.work.length > 10 && <li>and {p.work.length - 10} more</li>}
                                    </ul>
                                )}
                            </li>
                        );
                    })}
                    {entries.length === 0 && <li className="px-5 py-6 text-sm text-pebble text-center">{empty}</li>}
                </ol>
            </CardContent>
        </Card>
    );
}
