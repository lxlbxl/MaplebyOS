import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/Components/ui/card";
import { ArrowLeft, Printer, ShoppingCart, Truck, CreditCard, Mail } from 'lucide-react';
import { format } from 'date-fns';
import { formatMoney } from '@/lib/utils';
import { Separator } from "@/Components/ui/separator";

export default function Show({ quote, paid_amount, payment_status, settings, auth }) {
    const currency = usePage().props.settings?.currency || 'NGN';

    const PAYMENT_STATUS_COLORS = {
        paid: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
        partial: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        pending: 'bg-gray-500/10 text-gray-500 border-gray-500/20',
    };

    const handlePrint = () => {
        window.print();
    };

    return (
        <AppLayout>
            <Head title={`Quote ${quote.quote_number}`} />

            <div className="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto space-y-6">
                {/* Header Actions */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
                    <Link
                        href={route('operations.quotes.index')}
                        className="inline-flex items-center text-sm text-stone hover:text-charcoal transition-colors"
                    >
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Back to Quotes
                    </Link>
                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" onClick={handlePrint}>
                            <Printer className="mr-2 h-4 w-4" />
                            Print / PDF
                        </Button>
                        <Button variant="default" className="bg-sage hover:bg-maple-deep">
                            <Mail className="mr-2 h-4 w-4" />
                            Email to Client
                        </Button>
                    </div>
                </div>

                {/* Invoice View */}
                <Card className="bg-white border-linen shadow-lg print:shadow-none print:border-none">
                    <CardHeader className="flex flex-row justify-between items-start border-b border-linen pb-8">
                        <div className="space-y-2">
                            <h1 className="text-2xl font-serif font-bold text-charcoal">Quote</h1>
                            <p className="font-mono text-stone">{quote.quote_number}</p>
                            <Badge variant="outline" className={`mt-2 ${PAYMENT_STATUS_COLORS[payment_status] || 'bg-gray-100'}`}>
                                {payment_status?.toUpperCase()}
                            </Badge>
                        </div>
                        <div className="text-right space-y-1">
                            {settings?.company_name ? (
                                <h2 className="text-lg font-bold text-void">{settings.company_name}</h2>
                            ) : null}
                            {settings?.company_address ? (
                                <p className="text-sm text-stone">{settings.company_address}</p>
                            ) : null}
                            {settings?.company_city_state_zip ? (
                                <p className="text-sm text-stone">{settings.company_city_state_zip}</p>
                            ) : null}
                            {settings?.company_email ? (
                                <p className="text-sm text-stone">{settings.company_email}</p>
                            ) : null}
                            {settings?.company_phone ? (
                                <p className="text-sm text-stone">{settings.company_phone}</p>
                            ) : null}
                        </div>
                    </CardHeader>

                    <CardContent className="pt-8 space-y-8">
                        {/* Client & Dates */}
                        <div className="grid grid-cols-2 gap-8">
                            <div>
                                <h3 className="text-xs font-semibold text-drift uppercase tracking-wider mb-2">Bill To</h3>
                                <div className="text-charcoal font-medium">{quote.client?.company_name}</div>
                                <div className="text-stone">{quote.client?.contact_person}</div>
                                <div className="text-stone">{quote.client?.email}</div>
                                <div className="text-stone">{quote.client?.phone}</div>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <h3 className="text-xs font-semibold text-drift uppercase tracking-wider mb-1">Issue Date</h3>
                                    <div className="text-charcoal">{format(new Date(quote.issue_date), 'MMM d, yyyy')}</div>
                                </div>
                                <div>
                                    <h3 className="text-xs font-semibold text-drift uppercase tracking-wider mb-1">Expiry Date</h3>
                                    <div className="text-charcoal">{quote.expiry_date ? format(new Date(quote.expiry_date), 'MMM d, yyyy') : '-'}</div>
                                </div>
                                <div>
                                    <h3 className="text-xs font-semibold text-drift uppercase tracking-wider mb-1">Shipment Ref</h3>
                                    <div className="text-charcoal font-mono text-sm">
                                        {quote.shipment ? (
                                            <Link href={route('operations.shipments.show', quote.shipment.id)} className="text-sage hover:underline">
                                                {quote.shipment.shipment_number}
                                            </Link>
                                        ) : '-'}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Items Table */}
                        <div className="border border-linen rounded-lg overflow-hidden">
                            <table className="w-full text-sm">
                                <thead className="bg-cream border-b border-linen">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium text-void">Description</th>
                                        <th className="px-4 py-3 text-right font-medium text-void">Qty</th>
                                        <th className="px-4 py-3 text-right font-medium text-void">Unit Price</th>
                                        <th className="px-4 py-3 text-right font-medium text-void">Amount</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sage-50">
                                    {quote.items?.map((item, i) => (
                                        <tr key={i}>
                                            <td className="px-4 py-3 text-charcoal">{item.description}</td>
                                            <td className="px-4 py-3 text-right text-void">{item.quantity}</td>
                                            <td className="px-4 py-3 text-right text-void">{formatMoney(item.unit_price, currency)}</td>
                                            <td className="px-4 py-3 text-right text-charcoal font-medium">{formatMoney(item.amount, currency)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Totals */}
                        <div className="flex flex-col items-end gap-2 pt-4">
                            <div className="w-full max-w-xs space-y-2">
                                <div className="flex justify-between text-void">
                                    <span>Subtotal:</span>
                                    <span>{formatMoney(quote.subtotal, currency)}</span>
                                </div>
                                <div className="flex justify-between text-void">
                                    <span>Tax:</span>
                                    <span>{formatMoney(quote.tax_total, currency)}</span>
                                </div>
                                <Separator className="bg-sage-soft my-2" />
                                <div className="flex justify-between text-lg font-bold text-charcoal">
                                    <span>Total:</span>
                                    <span>{formatMoney(quote.total_amount, currency)}</span>
                                </div>

                                {/* Payments Section */}
                                <div className="mt-6 pt-4 border-t border-dotted border-linen">
                                    <div className="flex justify-between text-emerald-600 font-medium">
                                        <span>Amount Paid:</span>
                                        <span>- {formatMoney(paid_amount, currency)}</span>
                                    </div>
                                    <div className="flex justify-between text-red-500 font-bold mt-2 text-lg">
                                        <span>Balance Due:</span>
                                        <span>{formatMoney(Math.max(0, quote.total_amount - paid_amount), currency)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Recent Payments */}
                        {quote.payments && quote.payments.length > 0 && (
                            <div className="mt-8 pt-6 border-t border-linen">
                                <h4 className="font-semibold text-charcoal mb-4 flex items-center">
                                    <CreditCard className="mr-2 h-4 w-4" /> Payment History
                                </h4>
                                <div className="space-y-2">
                                    {quote.payments.map((payment) => (
                                        <div key={payment.id} className="flex justify-between items-center bg-cream p-2 rounded text-sm">
                                            <div className="flex gap-4">
                                                <span className="font-mono text-stone">{format(new Date(payment.payment_date), 'MMM d, yyyy')}</span>
                                                <span className="text-charcoal">{payment.payment_method}</span>
                                                {payment.transaction_reference && (
                                                    <span className="text-drift text-xs">Ref: {payment.transaction_reference}</span>
                                                )}
                                            </div>
                                            <span className="font-medium text-emerald-600">{formatMoney(payment.amount, currency)}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Notes */}
                        {(quote.notes || quote.terms_conditions) && (
                            <div className="mt-8 pt-6 border-t border-linen text-sm text-stone">
                                {quote.notes && (
                                    <div className="mb-4">
                                        <h4 className="font-semibold text-void mb-1">Notes</h4>
                                        <p>{quote.notes}</p>
                                    </div>
                                )}
                                {quote.terms_conditions && (
                                    <div>
                                        <h4 className="font-semibold text-void mb-1">Terms & Conditions</h4>
                                        <p>{quote.terms_conditions}</p>
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>

                    <CardFooter className="bg-cream border-t border-linen p-6 flex justify-center text-drift text-sm print:hidden">
                        Thank you for your business.
                    </CardFooter>
                </Card>
            </div>
        </AppLayout>
    );
}
