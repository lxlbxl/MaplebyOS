import { useState, useEffect } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import Pagination from '@/Components/Pagination';
import QuoteForm from '@/Components/Operations/QuoteForm';
import StatusSelect from '@/Components/StatusSelect';
import InsightsSection from '@/Components/InsightsSection';
import FilterBar from '@/Components/FilterBar';
import { Plus, MoreVertical, Calendar, DollarSign, FileText } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { format } from 'date-fns';
import { formatMoney } from '@/lib/utils';

const STATUS_COLORS = {
    draft: 'bg-gray-500/10 text-gray-500 border-gray-500/20',
    sent: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    approved: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
    rejected: 'bg-red-500/10 text-red-500 border-red-500/20',
    expired: 'bg-orange-500/10 text-orange-500 border-orange-500/20',
};

export default function Index({ quotes, filters, clients, shipments, vehicles, stats }) {
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';
    const [isSheetOpen, setIsSheetOpen] = useState(false);
    const [editingQuote, setEditingQuote] = useState(null);

    const openCreate = () => {
        setEditingQuote(null);
        setIsSheetOpen(true);
    };

    const openEdit = (quote) => {
        setEditingQuote(quote);
        setIsSheetOpen(true);
    };

    const confirmDelete = (quote) => {
        if (confirm('Are you sure you want to delete this quote?')) {
            router.delete(route('operations.quotes.destroy', quote.id));
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return format(new Date(dateString), 'MMM d, yyyy');
    };

    return (
        <AppLayout>
            <Head title="Quotes" />

            <div className="flex flex-col gap-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Quotes</h1>
                        <p className="text-drift mt-1">Manage price proposals and estimates.</p>
                    </div>
                    <Button onClick={openCreate} className="btn-sage shadow-soft hover:shadow-glow-sage">
                        <Plus className="mr-2 h-4 w-4" /> Create Quote
                    </Button>
                </div>

                {/* Insights & Filters */}
                <InsightsSection
                    stats={stats}
                    title="Quote Status"
                    description="Distribution of quotes by status"
                    totalLabel="Total Quotes"
                />

                <FilterBar
                    search={filters.search}
                    status={filters.status}
                    statusOptions={[
                        { value: 'all', label: 'All Statuses' },
                        { value: 'draft', label: 'Draft' },
                        { value: 'sent', label: 'Sent' },
                        { value: 'approved', label: 'Approved' },
                        { value: 'rejected', label: 'Rejected' },
                        { value: 'expired', label: 'Expired' },
                    ]}
                />

                {/* Data Table */}
                <Card className="bg-white border-linen shadow-sm overflow-hidden rounded-xl">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-linen hover:bg-cream bg-cream/50">
                                    <TableHead className="text-drift font-semibold">Quote #</TableHead>
                                    <TableHead className="text-drift font-semibold">Client</TableHead>
                                    <TableHead className="text-drift font-semibold">Status</TableHead>
                                    <TableHead className="text-drift font-semibold">Payment</TableHead>
                                    <TableHead className="text-drift font-semibold">Date</TableHead>
                                    <TableHead className="text-drift font-semibold">Total</TableHead>
                                    <TableHead className="text-right text-drift font-semibold">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {quotes.data.map((quote) => (
                                    <TableRow key={quote.id} className="border-linen hover:bg-cream transition-colors">
                                        <TableCell className="font-medium">
                                            <Link href={route('operations.quotes.show', quote.id)} className="flex items-center text-sage font-mono hover:underline group">
                                                <FileText className="h-4 w-4 mr-2 text-pebble group-hover:text-sage" />
                                                {quote.quote_number}
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                {quote.client ? (
                                                    <Link href={route('crm.clients.show', quote.client.id)} className="text-base font-semibold text-charcoal hover:text-sage transition-colors">
                                                        {quote.client.company_name || quote.client.contact_person}
                                                    </Link>
                                                ) : (
                                                    <span className="text-sm text-pebble font-medium">-</span>
                                                )}
                                                <div className="flex items-center gap-2 mt-1">
                                                    {quote.shipment_id && (
                                                        <Link href={route('operations.shipments.show', quote.shipment_id)}>
                                                            <Badge variant="outline" className="text-[10px] py-0 px-1 bg-linen text-stone border-transparent hover:bg-stone/10 cursor-pointer">
                                                                Shipment: {quote.shipment_id}
                                                            </Badge>
                                                        </Link>
                                                    )}
                                                    {quote.inventory_id && (
                                                        <Badge variant="outline" className="text-[10px] py-0 px-1 bg-linen text-stone border-transparent">
                                                            Inventory: {quote.inventory_id}
                                                        </Badge>
                                                    )}
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <StatusSelect
                                                status={quote.status}
                                                options={[
                                                    { value: 'draft', label: 'Draft' },
                                                    { value: 'sent', label: 'Sent' },
                                                    { value: 'approved', label: 'Approved' },
                                                    { value: 'rejected', label: 'Rejected' },
                                                    { value: 'expired', label: 'Expired' },
                                                ]}
                                                colorMap={{
                                                    draft: 'badge-muted border-transparent',
                                                    sent: 'bg-blue-50 text-blue-600 border-transparent',
                                                    approved: 'badge-success border-transparent',
                                                    rejected: 'badge-error border-transparent',
                                                    expired: 'badge-warning border-transparent',
                                                }}
                                                onStatusChange={(newStatus) => {
                                                    router.patch(route('operations.quotes.update-status', quote.id), { status: newStatus }, {
                                                        preserveScroll: true,
                                                    });
                                                }}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            {quote.payment_status && (
                                                <Badge variant="outline" className={`text-[10px] py-0 px-2 h-5 border-transparent 
                                                    ${quote.payment_status === 'paid' ? 'badge-success' :
                                                        quote.payment_status === 'partial' ? 'bg-blue-50 text-blue-600' :
                                                            'badge-error'}`}>
                                                    {quote.payment_status.charAt(0).toUpperCase() + quote.payment_status.slice(1)}
                                                </Badge>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col text-sm text-charcoal">
                                                <span>{formatDate(quote.issue_date)}</span>
                                                {quote.expiry_date && <span className="text-xs text-pebble">Exp: {formatDate(quote.expiry_date)}</span>}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col items-start gap-1">
                                                <span className="text-charcoal font-bold">
                                                    {formatMoney(quote.total_amount, currency)}
                                                </span>
                                                {Number(quote.paid_amount) > 0 && (
                                                    <span className="text-xs text-sage font-medium">
                                                        Paid: {formatMoney(quote.paid_amount, currency)}
                                                    </span>
                                                )}
                                            </div>
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
                                                    <DropdownMenuItem onClick={() => router.visit(route('operations.quotes.show', quote.id))} className="hover:bg-cream cursor-pointer">View Invoice</DropdownMenuItem>
                                                    <DropdownMenuItem onClick={() => openEdit(quote)} className="hover:bg-cream cursor-pointer">Edit Quote</DropdownMenuItem>
                                                    <DropdownMenuSeparator className="bg-linen" />
                                                    <DropdownMenuItem onClick={() => confirmDelete(quote)} className="text-error hover:bg-error-soft cursor-pointer">Delete</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {quotes.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={7} className="h-32 text-center">
                                            <div className="flex flex-col items-center justify-center text-pebble">
                                                <p className="font-medium">No quotes found</p>
                                                <p className="text-xs mt-1">Create a new quote to get started.</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Pagination links={quotes.links} />

                <QuoteForm
                    open={isSheetOpen}
                    onOpenChange={setIsSheetOpen}
                    quote={editingQuote}
                    clients={clients}
                    shipments={shipments}
                    vehicles={vehicles}
                />
            </div>
        </AppLayout>
    );
}
