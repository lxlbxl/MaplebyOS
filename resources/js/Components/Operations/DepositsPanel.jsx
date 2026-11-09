import { Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Landmark, ChevronRight } from 'lucide-react';
import { useCan } from '@/lib/access';
import { REFUND_STATUS_COLORS, money } from '@/lib/deposits';
import { formatDate, todayIso } from '@/lib/operations';
import { cn } from '@/lib/utils';

function RecordDeposit({ containers }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        container_id: containers.length === 1 ? String(containers[0].id) : '', amount: '', currency: 'NGN', paid_on: todayIso(), payment_reference: '',
    });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post(route('operations.deposits.store'), { preserveScroll: true, onSuccess: () => reset('amount', 'payment_reference') }); }}
            className="space-y-2 pt-3 border-t border-linen">
            <p className="text-xs font-medium text-stone">Record a deposit</p>
            {containers.length > 1 && (
                <Select value={data.container_id} onValueChange={(v) => setData('container_id', v)}>
                    <SelectTrigger className="bg-cream border-linen"><SelectValue placeholder="Which container?" /></SelectTrigger>
                    <SelectContent className="bg-white">{containers.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.container_number}</SelectItem>)}</SelectContent>
                </Select>
            )}
            <div className="flex gap-2">
                <Input type="number" min="0" step="0.01" value={data.amount} onChange={(e) => setData('amount', e.target.value)} placeholder="Amount" className="flex-1 bg-cream border-linen" />
                <Input value={data.currency} onChange={(e) => setData('currency', e.target.value.toUpperCase())} maxLength={3} className="w-20 bg-cream border-linen" />
            </div>
            <div className="flex gap-2">
                <Input type="date" value={data.paid_on} onChange={(e) => setData('paid_on', e.target.value)} className="w-40 bg-cream border-linen [color-scheme:light]" />
                <Input value={data.payment_reference} onChange={(e) => setData('payment_reference', e.target.value)} placeholder="Receipt / reference" className="flex-1 bg-cream border-linen" />
            </div>
            {(errors.amount || errors.container_number || errors.container_id) && <p className="text-xs text-red-600">{errors.amount || errors.container_number || errors.container_id}</p>}
            <Button type="submit" size="sm" disabled={processing || !data.container_id} className="btn-sage">Save deposit</Button>
        </form>
    );
}

/** Deposits on a container or a clearing job's containers, with refund status. */
export default function DepositsPanel({ panel }) {
    const can = useCan();
    if (!panel) return null;
    return (
        <Card className="bg-white border-linen shadow-sm">
            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><Landmark className="h-5 w-5" /> Container deposit</CardTitle></CardHeader>
            <CardContent className="p-5 space-y-3">
                {panel.deposits.map((d) => (
                    <Link key={d.id} href={route('operations.deposits.show', d.id)} className="flex items-center gap-2 text-sm hover:bg-cream/60 -mx-2 px-2 py-1.5 rounded">
                        <div className="flex-1 min-w-0">
                            <p className="text-void"><span className="font-mono">{d.container_number}</span> · {money(d.amount, d.currency)}</p>
                            <p className="text-xs text-pebble">Paid {formatDate(d.paid_on)}{d.outstanding > 0 ? ` · ${money(d.outstanding, d.currency)} still to come back` : ''}{d.owner ? ` · ${d.owner} follows up` : ''}</p>
                        </div>
                        <span className={cn('text-[11px] px-2 py-0.5 rounded-full whitespace-nowrap', REFUND_STATUS_COLORS[d.status])}>{d.status_label}</span>
                        <ChevronRight className="h-4 w-4 text-pebble" />
                    </Link>
                ))}
                {panel.deposits.length === 0 && <p className="text-sm text-pebble">No deposit recorded.</p>}
                {can('deposits.manage') && panel.containers.length > 0 && <RecordDeposit containers={panel.containers} />}
            </CardContent>
        </Card>
    );
}
