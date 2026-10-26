import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import ClientForm from '@/Components/CRM/ClientForm';
import StatusSelect from '@/Components/StatusSelect';
import InsightsSection from '@/Components/InsightsSection';
import {
    ArrowLeft, Building2, User, Mail, Phone, MapPin, Package,
    DollarSign, TrendingUp, Ship, Calendar, Pencil, ExternalLink
} from 'lucide-react';
import { format } from 'date-fns';
import { VEHICLE_STATUS_COLORS } from '@/lib/operations';
import { formatMoney } from '@/lib/utils';

export default function Show({ client, stats, statusBreakdown, statuses = {}, invoices = [] }) {
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';
    const [isSheetOpen, setIsSheetOpen] = useState(false);

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return format(new Date(dateString), 'MMM d, yyyy');
    };

    return (
        <AppLayout>
            <Head title={`Client - ${client.company_name || client.contact_person}`} />

            <div className="flex flex-col gap-6">
                {/* Header with Back Button */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('crm.clients.index')}>
                            <Button variant="outline" size="icon" className="border-sage-200 text-sage-600 hover:bg-sage-50">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-serif font-bold tracking-tight text-sage-600">
                                {client.company_name || client.contact_person}
                            </h1>
                            <p className="text-sage-400">Client Overview & Shipment History</p>
                        </div>
                    </div>
                    <Button onClick={() => setIsSheetOpen(true)} className="bg-sage-600 hover:bg-sage-700 text-white">
                        <Pencil className="mr-2 h-4 w-4" /> Edit Client
                    </Button>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card className="bg-sage-600 text-white border-0 shadow-lg shadow-sage-600/10">
                        <CardContent className="p-6">
                            <div className="flex items-center gap-3 mb-2">
                                <Package className="h-5 w-5 text-sage-200" />
                                <span className="text-sm text-sage-200 uppercase tracking-wider">Total Shipments</span>
                            </div>
                            <p className="text-4xl font-serif font-bold">{stats.total_shipments}</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-white border-sage-100 shadow-sm">
                        <CardContent className="p-6">
                            <div className="flex items-center gap-3 mb-2">
                                <DollarSign className="h-5 w-5 text-sage-400" />
                                <span className="text-sm text-sage-400 uppercase tracking-wider">Total Value</span>
                            </div>
                            <p className="text-3xl font-serif font-bold text-sage-700">{formatMoney(stats.total_value, currency)}</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-white border-sage-100 shadow-sm">
                        <CardContent className="p-6">
                            <div className="flex items-center gap-3 mb-2">
                                <Ship className="h-5 w-5 text-sage-400" />
                                <span className="text-sm text-sage-400 uppercase tracking-wider">In Progress</span>
                            </div>
                            <p className="text-3xl font-serif font-bold text-blue-600">{stats.current_shipments}</p>
                        </CardContent>
                    </Card>

                    <Card className="bg-white border-sage-100 shadow-sm">
                        <CardContent className="p-6">
                            <div className="flex items-center gap-3 mb-2">
                                <TrendingUp className="h-5 w-5 text-sage-400" />
                                <span className="text-sm text-sage-400 uppercase tracking-wider">Delivered</span>
                            </div>
                            <p className="text-3xl font-serif font-bold text-emerald-600">{stats.delivered_shipments}</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid md:grid-cols-3 gap-6">
                    {/* Client Details Card */}
                    <Card className="bg-white border-sage-100 shadow-sm">
                        <CardHeader className="border-b border-sage-100 bg-sage-50/30">
                            <CardTitle className="text-sage-700 flex items-center gap-2">
                                <Building2 className="h-5 w-5" /> Client Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-6 space-y-4">
                            <div className="space-y-3">
                                <div className="flex items-start gap-3">
                                    <User className="h-4 w-4 text-sage-400 mt-1" />
                                    <div>
                                        <p className="text-xs text-sage-400 uppercase">Contact Person</p>
                                        <p className="text-sage-700 font-medium">{client.contact_person || '-'}</p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-3">
                                    <Mail className="h-4 w-4 text-sage-400 mt-1" />
                                    <div>
                                        <p className="text-xs text-sage-400 uppercase">Email</p>
                                        <p className="text-sage-700">{client.email || '-'}</p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-3">
                                    <Phone className="h-4 w-4 text-sage-400 mt-1" />
                                    <div>
                                        <p className="text-xs text-sage-400 uppercase">Phone</p>
                                        <a href={`tel:${client.phone}`} className="text-sage-700 hover:text-coral-500 hover:underline transition-colors">{client.phone || '-'}</a>
                                    </div>
                                </div>

                                <div className="flex items-start gap-3">
                                    <MapPin className="h-4 w-4 text-sage-400 mt-1" />
                                    <div>
                                        <p className="text-xs text-sage-400 uppercase">Location</p>
                                        <p className="text-sage-700">
                                            {[client.address, client.city, client.country].filter(Boolean).join(', ') || '-'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="pt-4 border-t border-sage-100">
                                <Badge variant="outline" className={client.is_active
                                    ? "bg-emerald-50 text-emerald-600 border-emerald-200"
                                    : "bg-gray-100 text-gray-500 border-gray-200"}>
                                    {client.is_active ? 'Active Client' : 'Inactive Client'}
                                </Badge>
                                <Badge variant="outline" className="ml-2 bg-sage-50 text-sage-600 border-sage-200 capitalize">
                                    {client.type || 'buyer'}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Shipment Status Chart */}
                    <div className="md:col-span-2">
                        <InsightsSection
                            stats={statusBreakdown}
                            title="Shipment Status Breakdown"
                            description="Distribution of shipments by status"
                            totalLabel="Total Shipments"
                        />
                    </div>
                </div>

                {/* Shipment History */}
                <Card className="bg-white border-sage-100 shadow-sm">
                    <CardHeader className="border-b border-sage-100 bg-sage-50/30">
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-sage-700">Shipment History</CardTitle>
                                <CardDescription className="text-sage-400">All shipments for this client</CardDescription>
                            </div>
                            <Link href={route('operations.shipments.index') + '?create=true&client_id=' + client.id}>
                                <Button variant="outline" className="border-sage-200 text-sage-600 hover:bg-sage-50">
                                    <Package className="mr-2 h-4 w-4" /> New Shipment
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-sage-100 bg-sage-50/30">
                                    <TableHead className="text-sage-400 font-medium">Shipment #</TableHead>
                                    <TableHead className="text-sage-400 font-medium">Title</TableHead>
                                    <TableHead className="text-sage-400 font-medium">Status</TableHead>
                                    <TableHead className="text-sage-400 font-medium">VIN</TableHead>
                                    <TableHead className="text-sage-400 font-medium">Date</TableHead>
                                    <TableHead className="text-sage-400 font-medium">Value</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {client.shipments?.map((shipment) => (
                                    <TableRow key={shipment.id} className="border-sage-100 hover:bg-sage-50 transition-colors group">
                                        <TableCell className="font-mono text-sm text-coral-500">
                                            <Link
                                                href={route('operations.shipments.show', shipment.id)}
                                                className="block p-2 -m-2 touch-target hover:underline focus:outline-none"
                                            >
                                                {shipment.tracking_id || shipment.shipment_number}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="font-medium text-sage-600">
                                            <Link
                                                href={route('operations.shipments.show', shipment.id)}
                                                className="block p-2 -m-2 touch-target hover:text-coral-500 transition-colors focus:outline-none"
                                            >
                                                {shipment.vehicle_label || shipment.title}
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <StatusSelect
                                                status={shipment.status}
                                                options={Object.entries(statuses).map(([value, label]) => ({ value, label }))}
                                                colorMap={VEHICLE_STATUS_COLORS}
                                                onStatusChange={(newStatus) => {
                                                    router.patch(route('operations.shipments.update-status', shipment.id), { status: newStatus }, {
                                                        preserveScroll: true,
                                                    });
                                                }}
                                            />
                                        </TableCell>
                                        <TableCell className="text-sm text-sage-500">
                                            {shipment.vin || '-'}
                                        </TableCell>
                                        <TableCell className="text-sm text-sage-500">
                                            {formatDate(shipment.date_added || shipment.created_at)}
                                        </TableCell>
                                        <TableCell className="font-medium text-sage-700">
                                            {shipment.total_value ? formatMoney(shipment.total_value, currency) : '-'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {(!client.shipments || client.shipments.length === 0) && (
                                    <TableRow>
                                        <TableCell colSpan={6} className="h-24 text-center text-sage-400">
                                            No shipments yet for this client.
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card className="bg-white border-sage-100 shadow-sm">
                    <CardHeader className="border-b border-sage-100 bg-sage-50/30 flex flex-row items-center justify-between">
                        <CardTitle className="text-sage-700 flex items-center gap-2">
                            <DollarSign className="h-5 w-5" /> Invoices
                        </CardTitle>
                        <span className="text-sm text-sage-500">
                            Balance due: <span className="font-semibold text-sage-700">{formatMoney(stats.balance_due || 0, currency)}</span>
                        </span>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-sage-100">
                                    <TableHead className="text-sage-400 font-medium">Invoice</TableHead>
                                    <TableHead className="text-sage-400 font-medium">Issued</TableHead>
                                    <TableHead className="text-sage-400 font-medium">Status</TableHead>
                                    <TableHead className="text-sage-400 font-medium text-right">Total</TableHead>
                                    <TableHead className="text-sage-400 font-medium text-right">Balance</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {invoices.map((invoice) => (
                                    <TableRow key={invoice.id} className="border-sage-100">
                                        <TableCell>
                                            <Link href={route('finance.invoices.show', invoice.id)} className="font-medium text-sage-700 hover:underline">
                                                {invoice.invoice_number}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="text-sm text-sage-500">{formatDate(invoice.issue_date)}</TableCell>
                                        <TableCell><Badge variant="outline" className="capitalize">{invoice.status}</Badge></TableCell>
                                        <TableCell className="text-right">{formatMoney(invoice.total, invoice.currency)}</TableCell>
                                        <TableCell className="text-right font-medium text-sage-700">{formatMoney(invoice.balance ?? (invoice.total - invoice.amount_paid), invoice.currency)}</TableCell>
                                    </TableRow>
                                ))}
                                {invoices.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="h-16 text-center text-sage-400">No invoices yet.</TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <ClientForm
                    open={isSheetOpen}
                    onOpenChange={setIsSheetOpen}
                    client={client}
                />
            </div>
        </AppLayout>
    );
}
