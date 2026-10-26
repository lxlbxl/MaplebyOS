import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/Components/ui/card";
import { ArrowLeft, Printer, ShoppingCart, Truck, CreditCard, Mail } from 'lucide-react';
import { format } from 'date-fns';
import { formatMoney } from '@/lib/utils';
import { Separator } from "@/Components/ui/separator";

export default function Show({ quote, paid_amount, payment_status, settings, auth }) {
    const currency = 'USD'; // Default currency

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

            <div className="max-w-4xl mx-auto space-y-6">
                {/* Header Actions */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
                    <Link
                        href={route('operations.quotes.index')}
                        className="inline-flex items-center text-sm text-sage-500 hover:text-sage-700 transition-colors"
                    >
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Back to Quotes
                    </Link>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={handlePrint}>
                            <Printer className="mr-2 h-4 w-4" />
                            Print / PDF
                        </Button>
                        <Button variant="default" className="bg-sage-600 hover:bg-sage-700">
                            <Mail className="mr-2 h-4 w-4" />
                            Email to Client
                        </Button>
                    </div>
                </div>

                {/* Invoice View */}
                <Card className="bg-white border-sage-100 shadow-lg print:shadow-none print:border-none">
                    <CardHeader className="flex flex-row justify-between items-start border-b border-sage-50 pb-8">
                        <div className="space-y-2">
                            <h1 className="text-2xl font-serif font-bold text-sage-700">Quote</h1>
                            <p className="font-mono text-sage-500">{quote.quote_number}</p>
                            <Badge variant="outline" className={`mt-2 ${PAYMENT_STATUS_COLORS[payment_status] || 'bg-gray-100'}`}>
                                {payment_status?.toUpperCase()}
                            </Badge>
                        </div>
                        <div className="text-right space-y-1">
                            {settings?.company_name ? (
                                <h2 className="text-lg font-bold text-sage-600">{settings.company_name}</h2>
                            ) : null}
                            {settings?.company_address ? (
                                <p className="text-sm text-sage-500">{settings.company_address}</p>
                            ) : null}
                            {settings?.company_city_state_zip ? (
                                <p className="text-sm text-sage-500">{settings.company_city_state_zip}</p>
                            ) : null}
                            {settings?.company_email ? (
                                <p className="text-sm text-sage-500">{settings.company_email}</p>
                            ) : null}
                            {settings?.company_phone ? (
                                <p className="text-sm text-sage-500">{settings.company_phone}</p>
                            ) : null}
                        </div>
                    </CardHeader>

                    <CardContent className="pt-8 space-y-8">
                        {/* Client & Dates */}
                        <div className="grid grid-cols-2 gap-8">
                            <div>
                                <h3 className="text-xs font-semibold text-sage-400 uppercase tracking-wider mb-2">Bill To</h3>
                                <div className="text-sage-700 font-medium">{quote.client?.company_name}</div>
                                <div className="text-sage-500">{quote.client?.contact_person}</div>
                                <div className="text-sage-500">{quote.client?.email}</div>
                                <div className="text-sage-500">{quote.client?.phone}</div>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <h3 className="text-xs font-semibold text-sage-400 uppercase tracking-wider mb-1">Issue Date</h3>
                                    <div className="text-sage-700">{format(new Date(quote.issue_date), 'MMM d, yyyy')}</div>
                                </div>
                                <div>
                                    <h3 className="text-xs font-semibold text-sage-400 uppercase tracking-wider mb-1">Expiry Date</h3>
                                    <div className="text-sage-700">{quote.expiry_date ? format(new Date(quote.expiry_date), 'MMM d, yyyy') : '-'}</div>
                                </div>
                                <div>
                                    <h3 className="text-xs font-semibold text-sage-400 uppercase tracking-wider mb-1">Shipment Ref</h3>
                                    <div className="text-sage-700 font-mono text-sm">
                                        {quote.shipment ? (
                                            <Link href={route('operations.shipments.show', quote.shipment.id)} className="text-coral-500 hover:underline">
                                                {quote.shipment.shipment_number}
                                            </Link>
                                        ) : '-'}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Items Table */}
                        <div className="border border-sage-100 rounded-lg overflow-hidden">
                            <table className="w-full text-sm">
                                <thead className="bg-sage-50 border-b border-sage-100">
                                    <tr>
                                        <th className="px-4 py-3 text-left font-medium text-sage-600">Description</th>
                                        <th className="px-4 py-3 text-right font-medium text-sage-600">Qty</th>
                                        <th className="px-4 py-3 text-right font-medium text-sage-600">Unit Price</th>
                                        <th className="px-4 py-3 text-right font-medium text-sage-600">Amount</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sage-50">
                                    {quote.items?.map((item, i) => (
                                        <tr key={i}>
                                            <td className="px-4 py-3 text-sage-700">{item.description}</td>
                                            <td className="px-4 py-3 text-right text-sage-600">{item.quantity}</td>
                                            <td className="px-4 py-3 text-right text-sage-600">{formatMoney(item.unit_price, currency)}</td>
                                            <td className="px-4 py-3 text-right text-sage-700 font-medium">{formatMoney(item.amount, currency)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Totals */}
                        <div className="flex flex-col items-end gap-2 pt-4">
                            <div className="w-full max-w-xs space-y-2">
                                <div className="flex justify-between text-sage-600">
                                    <span>Subtotal:</span>
                                    <span>{formatMoney(quote.subtotal, currency)}</span>
                                </div>
                                <div className="flex justify-between text-sage-600">
                                    <span>Tax:</span>
                                    <span>{formatMoney(quote.tax_total, currency)}</span>
                                </div>
                                <Separator className="bg-sage-100 my-2" />
                                <div className="flex justify-between text-lg font-bold text-sage-800">
                                    <span>Total:</span>
                                    <span>{formatMoney(quote.total_amount, currency)}</span>
                                </div>

                                {/* Payments Section */}
                                <div className="mt-6 pt-4 border-t border-dotted border-sage-200">
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
                            <div className="mt-8 pt-6 border-t border-sage-100">
                                <h4 className="font-semibold text-sage-700 mb-4 flex items-center">
                                    <CreditCard className="mr-2 h-4 w-4" /> Payment History
                                </h4>
                                <div className="space-y-2">
                                    {quote.payments.map((payment) => (
                                        <div key={payment.id} className="flex justify-between items-center bg-sage-50 p-2 rounded text-sm">
                                            <div className="flex gap-4">
                                                <span className="font-mono text-sage-500">{format(new Date(payment.payment_date), 'MMM d, yyyy')}</span>
                                                <span className="text-sage-700">{payment.payment_method}</span>
                                                {payment.transaction_reference && (
                                                    <span className="text-sage-400 text-xs">Ref: {payment.transaction_reference}</span>
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
                            <div className="mt-8 pt-6 border-t border-sage-100 text-sm text-sage-500">
                                {quote.notes && (
                                    <div className="mb-4">
                                        <h4 className="font-semibold text-sage-600 mb-1">Notes</h4>
                                        <p>{quote.notes}</p>
                                    </div>
                                )}
                                {quote.terms_conditions && (
                                    <div>
                                        <h4 className="font-semibold text-sage-600 mb-1">Terms & Conditions</h4>
                                        <p>{quote.terms_conditions}</p>
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>

                    <CardFooter className="bg-sage-50 border-t border-sage-100 p-6 flex justify-center text-sage-400 text-sm print:hidden">
                        Thank you for your business.
                    </CardFooter>
                </Card>
            </div>
        </AppLayout>
    );
}
