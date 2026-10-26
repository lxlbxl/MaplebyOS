import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage, useForm } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import Pagination from '@/Components/Pagination';
import FilterBar from '@/Components/FilterBar';
import { Plus, MoreVertical, Calendar, DollarSign, FileText, Landmark } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetFooter } from "@/Components/ui/sheet";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { format } from 'date-fns';
import { formatMoney } from '@/lib/utils';

export default function Index({ payments, filters, quotes }) {
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';
    const [isSheetOpen, setIsSheetOpen] = useState(false);
    const [editingPayment, setEditingPayment] = useState(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        quote_id: '',
        amount: '',
        payment_date: format(new Date(), 'yyyy-MM-dd'),
        payment_method: 'bank_transfer',
        transaction_reference: '',
        notes: '',
    });

    const openCreate = () => {
        setEditingPayment(null);
        reset();
        setIsSheetOpen(true);
    };

    const openEdit = (payment) => {
        setEditingPayment(payment);
        setData({
            quote_id: String(payment.quote_id),
            amount: payment.amount,
            payment_date: payment.payment_date,
            payment_method: payment.payment_method,
            transaction_reference: payment.transaction_reference || '',
            notes: payment.notes || '',
        });
        setIsSheetOpen(true);
    };

    const submit = (e) => {
        e.preventDefault();
        if (editingPayment) {
            put(route('finance.payments.update', editingPayment.id), {
                onSuccess: () => setIsSheetOpen(false),
            });
        } else {
            post(route('finance.payments.store'), {
                onSuccess: () => setIsSheetOpen(false),
            });
        }
    };

    const confirmDelete = (payment) => {
        if (confirm('Are you sure you want to delete this payment record?')) {
            router.delete(route('finance.payments.destroy', payment.id));
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return format(new Date(dateString), 'MMM d, yyyy');
    };

    return (
        <AppLayout>
            <Head title="Payments" />

            <div className="flex flex-col gap-6">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Payments</h1>
                        <p className="text-drift mt-1">Track and record income from quotes.</p>
                    </div>
                    <Button onClick={openCreate} className="btn-sage shadow-soft hover:shadow-glow-sage">
                        <Plus className="mr-2 h-4 w-4" /> Record Payment
                    </Button>
                </div>

                <FilterBar
                    search={filters.search}
                    placeholder="Search by reference or quote..."
                />

                <Card className="bg-white border-linen shadow-sm overflow-hidden rounded-xl">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-linen hover:bg-cream bg-cream/50">
                                    <TableHead className="text-drift font-semibold">Date</TableHead>
                                    <TableHead className="text-drift font-semibold">Quote #</TableHead>
                                    <TableHead className="text-drift font-semibold">Amount</TableHead>
                                    <TableHead className="text-drift font-semibold">Method</TableHead>
                                    <TableHead className="text-drift font-semibold">Reference</TableHead>
                                    <TableHead className="text-right text-drift font-semibold">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {payments.data.map((payment) => (
                                    <TableRow key={payment.id} className="border-linen hover:bg-cream transition-colors">
                                        <TableCell className="text-sm text-charcoal">
                                            {formatDate(payment.payment_date)}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                {payment.quote ? (
                                                    <Link href={route('operations.quotes.show', payment.quote.id)} className="font-mono text-xs text-sage hover:underline hover:text-charcoal transition-colors">
                                                        {payment.quote.quote_number}
                                                    </Link>
                                                ) : (
                                                    <span className="font-mono text-xs text-pebble">deleted</span>
                                                )}
                                                {payment.quote?.client && (
                                                    <Link href={route('crm.clients.show', payment.quote.client.id)} className="text-xs text-pebble hover:text-charcoal hover:underline">
                                                        {payment.quote.client.company_name}
                                                    </Link>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <span className="font-bold text-charcoal">{formatMoney(payment.amount, currency)}</span>
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline" className="capitalize bg-linen text-stone border-transparent">
                                                {payment.payment_method.replace('_', ' ')}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-sm text-pebble font-mono">
                                            {payment.transaction_reference || '-'}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" className="h-8 w-8 p-0 text-pebble hover:text-charcoal">
                                                        <MoreVertical className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                                    <DropdownMenuLabel className="text-drift">Actions</DropdownMenuLabel>
                                                    <DropdownMenuItem onClick={() => openEdit(payment)} className="hover:bg-cream cursor-pointer">Edit Record</DropdownMenuItem>
                                                    <DropdownMenuSeparator className="bg-linen" />
                                                    <DropdownMenuItem onClick={() => confirmDelete(payment)} className="text-error hover:bg-error-soft cursor-pointer">Delete</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {payments.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={6} className="h-32 text-center text-pebble">
                                            <div className="flex flex-col items-center justify-center">
                                                <p className="font-medium">No payment records found</p>
                                                <p className="text-xs mt-1">Record a new payment to get started</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Pagination links={payments.links} />

                <Sheet open={isSheetOpen} onOpenChange={setIsSheetOpen}>
                    <SheetContent className="overflow-y-auto bg-white border-l border-linen text-charcoal sm:max-w-md shadow-lg">
                        <SheetHeader>
                            <SheetTitle className="text-display-sm text-void font-bold">{editingPayment ? 'Edit Payment' : 'Record New Payment'}</SheetTitle>
                            <SheetDescription className="text-drift">
                                Enter the details of the payment received.
                            </SheetDescription>
                        </SheetHeader>

                        <form onSubmit={submit} className="space-y-6 mt-6">
                            <div className="space-y-4">
                                <div className="space-y-2">
                                    <Label className="text-charcoal font-medium">Select Quote</Label>
                                    <Select value={data.quote_id} onValueChange={(val) => setData('quote_id', val)}>
                                        <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                            <SelectValue placeholder="Select a quote" />
                                        </SelectTrigger>
                                        <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                            {quotes.map(quote => (
                                                <SelectItem key={quote.id} value={String(quote.id)} className="focus:bg-cream cursor-pointer">
                                                    {quote.quote_number} ({formatMoney(quote.total_amount, currency)})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.quote_id && <span className="text-error text-sm">{errors.quote_id}</span>}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label className="text-charcoal font-medium">Amount ({currency})</Label>
                                        <Input type="number" step="0.01" value={data.amount} onChange={e => setData('amount', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                                        {errors.amount && <span className="text-error text-sm">{errors.amount}</span>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label className="text-charcoal font-medium">Date</Label>
                                        <Input type="date" value={data.payment_date} onChange={e => setData('payment_date', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg [color-scheme:light]" />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label className="text-charcoal font-medium">Payment Method</Label>
                                    <Select value={data.payment_method} onValueChange={(val) => setData('payment_method', val)}>
                                        <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                            <SelectItem value="bank_transfer" className="focus:bg-cream">Bank Transfer</SelectItem>
                                            <SelectItem value="cash" className="focus:bg-cream">Cash</SelectItem>
                                            <SelectItem value="check" className="focus:bg-cream">Check</SelectItem>
                                            <SelectItem value="other" className="focus:bg-cream">Other</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label className="text-charcoal font-medium">Reference (e.g. Transaction ID)</Label>
                                    <Input value={data.transaction_reference} onChange={e => setData('transaction_reference', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg font-mono" />
                                </div>

                                <div className="space-y-2">
                                    <Label className="text-charcoal font-medium">Notes</Label>
                                    <Textarea value={data.notes} onChange={e => setData('notes', e.target.value)} className="bg-cream border-linen text-charcoal rounded-lg" rows={3} />
                                </div>
                            </div>

                            <SheetFooter>
                                <Button type="submit" className="w-full btn-sage shadow-soft hover:shadow-glow-sage" disabled={processing}>
                                    {editingPayment ? 'Update Record' : 'Save Payment'}
                                </Button>
                            </SheetFooter>
                        </form>
                    </SheetContent>
                </Sheet>
            </div>
        </AppLayout>
    );
}
