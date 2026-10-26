import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Card, CardContent } from "@/Components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { ArrowLeft, Printer } from 'lucide-react';
import { formatDate, todayIso } from '@/lib/operations';
import { formatMoney, cn } from '@/lib/utils';

export default function Show({ invoice, statuses }) {
    const appName = usePage().props.settings?.app_name || 'Mapleby';
    const pay = useForm({ invoice_id: invoice.id, amount: invoice.balance > 0 ? invoice.balance : '', payment_date: todayIso(), payment_method: 'bank_transfer', transaction_reference: '', notes: '' });
    const setStatus = (status) => router.patch(route('finance.invoices.update-status', invoice.id), { status }, { preserveScroll: true });

    return (
        <AppLayout>
            <Head title={`Invoice ${invoice.invoice_number}`} />
            <div className="max-w-4xl mx-auto flex flex-col gap-5">
                <div className="flex flex-wrap items-center justify-between gap-3 print:hidden">
                    <Link href={route('finance.invoices.index')}><Button variant="outline" className="border-linen"><ArrowLeft className="h-4 w-4 mr-2" /> Invoices</Button></Link>
                    <div className="flex gap-2">
                        {invoice.status === 'draft' && <Button onClick={() => setStatus('sent')} className="btn-sage">Mark as sent</Button>}
                        {invoice.status !== 'void' && invoice.status !== 'paid' && <Button variant="outline" className="border-linen text-red-600" onClick={() => confirm('Void this invoice? Its lines become billable again.') && setStatus('void')}>Void</Button>}
                        <Button variant="outline" className="border-linen" onClick={() => window.print()}><Printer className="h-4 w-4 mr-2" /> Print / PDF</Button>
                    </div>
                </div>

                <Card className="bg-white border-linen shadow-sm print:shadow-none print:border-0">
                    <CardContent className="p-8 space-y-6">
                        <div className="flex justify-between gap-4">
                            <div>
                                <p className="text-2xl font-bold text-void">{appName}</p>
                                <p className="text-sm text-stone">Invoice</p>
                            </div>
                            <div className="text-right text-sm">
                                <p className="font-mono text-lg text-void">{invoice.invoice_number}</p>
                                <p>Issued {formatDate(invoice.issue_date)}</p>
                                <p>Due {formatDate(invoice.due_date)}</p>
                                <span className="inline-block mt-1 text-xs px-2 py-0.5 rounded-full bg-cream border border-linen">{statuses[invoice.status]}</span>
                            </div>
                        </div>
                        <div className="text-sm">
                            <p className="text-pebble">Bill to</p>
                            <p className="font-medium text-void">{invoice.client?.company_name || invoice.client?.contact_person}</p>
                            {invoice.client?.company_name && <p>{invoice.client.contact_person}</p>}
                            {invoice.client?.address && <p className="whitespace-pre-line">{invoice.client.address}</p>}
                            {invoice.client?.phone && <p>{invoice.client.phone}</p>}
                        </div>
                        <table className="w-full text-sm">
                            <thead><tr className="border-b border-linen text-left text-pebble"><th className="py-2">Vehicle</th><th>Description</th><th className="text-right">Amount</th></tr></thead>
                            <tbody>
                                {invoice.charges.map((c) => (
                                    <tr key={c.id} className="border-b border-linen">
                                        <td className="py-2">
                                            <Link href={route('operations.shipments.show', c.shipment.id)} className="font-mono text-xs text-sage print:text-charcoal">{c.shipment.tracking_id}</Link>
                                            <div>{[c.shipment.vehicle_year, c.shipment.vehicle_make, c.shipment.vehicle_model].filter(Boolean).join(' ')}</div>
                                            <div className="font-mono text-xs text-stone">{c.shipment.vin}</div>
                                        </td>
                                        <td>{c.category}{c.description ? ` · ${c.description}` : ''}</td>
                                        <td className="text-right">{formatMoney(c.amount, invoice.currency)}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot>
                                <tr><td colSpan={2} className="pt-3 text-right text-pebble">Total</td><td className="pt-3 text-right font-semibold">{formatMoney(invoice.total, invoice.currency)}</td></tr>
                                <tr><td colSpan={2} className="text-right text-pebble">Paid</td><td className="text-right">{formatMoney(invoice.amount_paid, invoice.currency)}</td></tr>
                                <tr><td colSpan={2} className="text-right font-semibold">Balance due</td><td className={cn('text-right font-bold', invoice.balance > 0 ? 'text-red-700' : 'text-emerald-700')}>{formatMoney(invoice.balance, invoice.currency)}</td></tr>
                            </tfoot>
                        </table>
                        {invoice.notes && <p className="text-sm text-stone whitespace-pre-wrap">{invoice.notes}</p>}
                    </CardContent>
                </Card>

                <Card className="bg-white border-linen shadow-sm print:hidden">
                    <CardContent className="p-5 space-y-3">
                        <p className="font-semibold text-void">Payments</p>
                        {invoice.payments.map((p) => (
                            <div key={p.id} className="flex justify-between text-sm"><span>{formatDate(p.payment_date)} · {p.payment_method.replace('_', ' ')}{p.transaction_reference ? ` · ${p.transaction_reference}` : ''}</span><span>{formatMoney(p.amount, invoice.currency)}</span></div>
                        ))}
                        {invoice.payments.length === 0 && <p className="text-sm text-pebble">No payments yet.</p>}
                        {invoice.status !== 'void' && invoice.balance > 0 && (
                            <form onSubmit={(e) => { e.preventDefault(); pay.post(route('finance.payments.store'), { preserveScroll: true }); }} className="grid sm:grid-cols-5 gap-2 pt-3 border-t border-linen">
                                <Input type="number" step="0.01" value={pay.data.amount} onChange={(e) => pay.setData('amount', e.target.value)} placeholder="Amount" className="bg-cream" />
                                <Input type="date" value={pay.data.payment_date} onChange={(e) => pay.setData('payment_date', e.target.value)} className="bg-cream [color-scheme:light]" />
                                <Select value={pay.data.payment_method} onValueChange={(v) => pay.setData('payment_method', v)}>
                                    <SelectTrigger className="bg-cream"><SelectValue /></SelectTrigger>
                                    <SelectContent className="bg-white">{['bank_transfer', 'interac', 'cash', 'card', 'cheque', 'other'].map((m) => <SelectItem key={m} value={m}>{m.replace('_', ' ')}</SelectItem>)}</SelectContent>
                                </Select>
                                <Input value={pay.data.transaction_reference} onChange={(e) => pay.setData('transaction_reference', e.target.value)} placeholder="Reference" className="bg-cream" />
                                <Button type="submit" disabled={pay.processing} className="btn-sage">Record payment</Button>
                                {Object.values(pay.errors).length > 0 && <p className="sm:col-span-5 text-xs text-red-600">{Object.values(pay.errors).join(' ')}</p>}
                            </form>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
