import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import Pagination from '@/Components/Pagination';
import FilterBar from '@/Components/FilterBar';
import { FileText } from 'lucide-react';
import { formatDate } from '@/lib/operations';
import { formatMoney, cn } from '@/lib/utils';

const STATUS_COLORS = { draft: 'bg-linen text-stone', sent: 'bg-blue-50 text-blue-700', partial: 'bg-amber-50 text-amber-700', paid: 'bg-emerald-50 text-emerald-700', void: 'bg-gray-100 text-gray-500' };

function Billable({ group, currency }) {
    const [picked, setPicked] = useState(group.charges.map((c) => c.id));
    const total = group.charges.filter((c) => picked.includes(c.id)).reduce((s, c) => s + Number(c.amount), 0);
    return (
        <div className="rounded-xl border border-linen p-4 space-y-2">
            <div className="flex justify-between items-center">
                <p className="font-medium text-void">{group.client?.company_name || group.client?.contact_person}</p>
                <Button size="sm" className="btn-sage" disabled={!picked.length}
                    onClick={() => router.post(route('finance.invoices.store'), { client_id: group.client.id, charge_ids: picked })}>
                    Invoice {formatMoney(total, currency)}
                </Button>
            </div>
            <div className="text-sm divide-y divide-linen">
                {group.charges.map((c) => (
                    <label key={c.id} className="flex items-center gap-2 py-1.5 cursor-pointer">
                        <input type="checkbox" className="accent-sage" checked={picked.includes(c.id)} onChange={() => setPicked((p) => (p.includes(c.id) ? p.filter((x) => x !== c.id) : [...p, c.id]))} />
                        <span className="font-mono text-xs w-28">{c.shipment.tracking_id}</span>
                        <span className="flex-1 text-stone">{[c.shipment.vehicle_year, c.shipment.vehicle_make, c.shipment.vehicle_model].filter(Boolean).join(' ')} · {c.category}{c.description ? ` (${c.description})` : ''}</span>
                        <span>{formatMoney(c.amount, c.currency || currency)}</span>
                    </label>
                ))}
            </div>
        </div>
    );
}

export default function Index({ invoices, filters, statuses, summary, billable }) {
    const currency = usePage().props.settings?.currency || 'USD';
    return (
        <AppLayout>
            <Head title="Invoices" />
            <div className="flex flex-col gap-5">
                <div>
                    <h1 className="text-display-md text-void">Invoices</h1>
                    <p className="text-drift mt-1">Bill customers for the charges on their cars. Add charges on each vehicle's page.</p>
                </div>

                <div className="grid grid-cols-3 gap-3">
                    <div className="rounded-xl bg-white border border-linen p-4"><p className="text-xs text-pebble">Outstanding</p><p className="text-2xl font-semibold">{formatMoney(summary.outstanding, currency)}</p></div>
                    <div className="rounded-xl bg-white border border-linen p-4"><p className="text-xs text-pebble">Overdue</p><p className={cn('text-2xl font-semibold', summary.overdue > 0 && 'text-red-700')}>{formatMoney(summary.overdue, currency)}</p></div>
                    <div className="rounded-xl bg-white border border-linen p-4"><p className="text-xs text-pebble">Open invoices</p><p className="text-2xl font-semibold">{summary.open_count}</p></div>
                </div>

                {billable.length > 0 && (
                    <Card className="bg-white border-linen shadow-sm">
                        <CardHeader className="border-b border-linen"><CardTitle className="text-void">Ready to invoice</CardTitle></CardHeader>
                        <CardContent className="p-5 space-y-3">{billable.map((g) => <Billable key={g.client?.id} group={g} currency={currency} />)}</CardContent>
                    </Card>
                )}

                <FilterBar search={filters.search} status={filters.status || 'all'} statusOptions={[{ value: 'all', label: 'All statuses' }, ...Object.entries(statuses).map(([value, label]) => ({ value, label }))]} />

                <Card className="bg-white border-linen shadow-sm overflow-hidden">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-cream/50 border-linen">
                                    <TableHead>Invoice</TableHead><TableHead>Customer</TableHead><TableHead>Issued</TableHead><TableHead>Due</TableHead>
                                    <TableHead className="text-right">Total</TableHead><TableHead className="text-right">Balance</TableHead><TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {invoices.data.map((inv) => (
                                    <TableRow key={inv.id} className="border-linen hover:bg-cream/60">
                                        <TableCell><Link href={route('finance.invoices.show', inv.id)} className="font-mono text-sm text-sage hover:underline inline-flex items-center gap-1"><FileText className="h-3.5 w-3.5" />{inv.invoice_number}</Link></TableCell>
                                        <TableCell>{inv.client?.company_name || inv.client?.contact_person}</TableCell>
                                        <TableCell className="text-sm text-stone">{formatDate(inv.issue_date)}</TableCell>
                                        <TableCell className="text-sm text-stone">{formatDate(inv.due_date)}</TableCell>
                                        <TableCell className="text-right">{formatMoney(inv.total, inv.currency)}</TableCell>
                                        <TableCell className="text-right font-medium">{formatMoney(inv.balance, inv.currency)}</TableCell>
                                        <TableCell><span className={cn('text-xs px-2 py-1 rounded-full', STATUS_COLORS[inv.status])}>{statuses[inv.status]}</span></TableCell>
                                    </TableRow>
                                ))}
                                {invoices.data.length === 0 && <TableRow><TableCell colSpan={7} className="h-24 text-center text-pebble">No invoices yet.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
                <Pagination links={invoices.links} />
            </div>
        </AppLayout>
    );
}
