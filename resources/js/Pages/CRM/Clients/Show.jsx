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
import StatCard from '@/Components/StatCard';
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

            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-6">
                {/* Header with Back Button */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('crm.clients.index')}>
                            <Button variant="outline" size="icon" className="border-linen text-void hover:bg-cream">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-display-md text-void">
                                {client.company_name || client.contact_person}
                            </h1>
                            <p className="text-drift">Client Overview & Shipment History</p>
                        </div>
                    </div>
                    <Button onClick={() => setIsSheetOpen(true)} className="bg-sage hover:bg-maple-deep text-white">
                        <Pencil className="mr-2 h-4 w-4" /> Edit Client
                    </Button>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    <StatCard dark icon={Package} label="Total Shipments" value={stats.total_shipments} hint="All time for this client" />
                    <StatCard icon={DollarSign} label="Total Value" value={formatMoney(stats.total_value, currency)} hint="Sum of shipment values" />
                    <StatCard icon={Ship} label="In Progress" value={stats.current_shipments} hint="Not yet delivered" />
                    <StatCard icon={TrendingUp} tone="success" label="Delivered" value={stats.delivered_shipments} hint="Completed shipments" />
                </div>

                <div className="grid md:grid-cols-3 gap-6">
                    {/* Client Details Card */}
                    <Card className="bg-white border-linen shadow-sm">
                        <CardHeader className="border-b border-linen bg-cream/30">
                            <CardTitle className="text-charcoal flex items-center gap-2">
                                <Building2 className="h-5 w-5" /> Client Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-6 space-y-4">
                            <div className="space-y-3">
                                <div className="flex items-start gap-3">
                                    <User className="h-4 w-4 text-drift mt-1" />
                                    <div>
                                        <p className="text-xs text-drift uppercase">Contact Person</p>
                                        <p className="text-charcoal font-medium">{client.contact_person || '-'}</p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-3">
                                    <Mail className="h-4 w-4 text-drift mt-1" />
                                    <div>
                                        <p className="text-xs text-drift uppercase">Email</p>
                                        <p className="text-charcoal">{client.email || '-'}</p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-3">
                                    <Phone className="h-4 w-4 text-drift mt-1" />
                                    <div>
                                        <p className="text-xs text-drift uppercase">Phone</p>
                                        <a href={`tel:${client.phone}`} className="text-charcoal hover:text-sage hover:underline transition-colors">{client.phone || '-'}</a>
                                    </div>
                                </div>

                                <div className="flex items-start gap-3">
                                    <MapPin className="h-4 w-4 text-drift mt-1" />
                                    <div>
                                        <p className="text-xs text-drift uppercase">Location</p>
                                        <p className="text-charcoal">
                                            {[client.address, client.city, client.country].filter(Boolean).join(', ') || '-'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="pt-4 border-t border-linen">
                                <Badge variant="outline" className={client.is_active
                                    ? "bg-emerald-50 text-emerald-600 border-emerald-200"
                                    : "bg-gray-100 text-gray-500 border-gray-200"}>
                                    {client.is_active ? 'Active Client' : 'Inactive Client'}
                                </Badge>
                                <Badge variant="outline" className="ml-2 bg-cream text-void border-linen capitalize">
                                    {client.type || 'buyer'}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Shipment Status Chart */}
                    <div className="md:col-span-2">
                        <InsightsSection
                            icon={Package}
                            stats={statusBreakdown}
                            title="Shipment Status Breakdown"
                            description="Distribution of shipments by status"
                            totalLabel="Total Shipments"
                            compact
                        />
                    </div>
                </div>

                {/* Shipment History */}
                <Card className="bg-white border-linen shadow-sm">
                    <CardHeader className="border-b border-linen bg-cream/30">
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-charcoal">Shipment History</CardTitle>
                                <CardDescription className="text-drift">All shipments for this client</CardDescription>
                            </div>
                            <Link href={route('operations.shipments.index') + '?create=true&client_id=' + client.id}>
                                <Button variant="outline" className="border-linen text-void hover:bg-cream">
                                    <Package className="mr-2 h-4 w-4" /> New Shipment
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-linen bg-cream/30">
                                    <TableHead className="text-drift font-medium">Shipment #</TableHead>
                                    <TableHead className="text-drift font-medium">Title</TableHead>
                                    <TableHead className="text-drift font-medium">Status</TableHead>
                                    <TableHead className="text-drift font-medium">VIN</TableHead>
                                    <TableHead className="text-drift font-medium">Date</TableHead>
                                    <TableHead className="text-drift font-medium">Value</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {client.shipments?.map((shipment) => (
                                    <TableRow key={shipment.id} className="border-linen hover:bg-cream transition-colors group">
                                        <TableCell className="font-mono text-sm text-sage">
                                            <Link
                                                href={route('operations.shipments.show', shipment.id)}
                                                className="block p-2 -m-2 touch-target hover:underline focus:outline-none"
                                            >
                                                {shipment.tracking_id || shipment.shipment_number}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="font-medium text-void">
                                            <Link
                                                href={route('operations.shipments.show', shipment.id)}
                                                className="block p-2 -m-2 touch-target hover:text-sage transition-colors focus:outline-none"
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
                                        <TableCell className="text-sm text-stone">
                                            {shipment.vin || '-'}
                                        </TableCell>
                                        <TableCell className="text-sm text-stone">
                                            {formatDate(shipment.date_added || shipment.created_at)}
                                        </TableCell>
                                        <TableCell className="font-medium text-charcoal">
                                            {shipment.total_value ? formatMoney(shipment.total_value, currency) : '-'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {(!client.shipments || client.shipments.length === 0) && (
                                    <TableRow>
                                        <TableCell colSpan={6} className="h-24 text-center text-drift">
                                            No shipments yet for this client.
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card className="bg-white border-linen shadow-sm">
                    <CardHeader className="border-b border-linen bg-cream/30 flex flex-row items-center justify-between">
                        <CardTitle className="text-charcoal flex items-center gap-2">
                            <DollarSign className="h-5 w-5" /> Invoices
                        </CardTitle>
                        <span className="text-sm text-stone">
                            Balance due: <span className="font-semibold text-charcoal">{formatMoney(stats.balance_due || 0, currency)}</span>
                        </span>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-linen">
                                    <TableHead className="text-drift font-medium">Invoice</TableHead>
                                    <TableHead className="text-drift font-medium">Issued</TableHead>
                                    <TableHead className="text-drift font-medium">Status</TableHead>
                                    <TableHead className="text-drift font-medium text-right">Total</TableHead>
                                    <TableHead className="text-drift font-medium text-right">Balance</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {invoices.map((invoice) => (
                                    <TableRow key={invoice.id} className="border-linen">
                                        <TableCell>
                                            <Link href={route('finance.invoices.show', invoice.id)} className="font-medium text-charcoal hover:underline">
                                                {invoice.invoice_number}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="text-sm text-stone">{formatDate(invoice.issue_date)}</TableCell>
                                        <TableCell><Badge variant="outline" className="capitalize">{invoice.status}</Badge></TableCell>
                                        <TableCell className="text-right">{formatMoney(invoice.total, invoice.currency)}</TableCell>
                                        <TableCell className="text-right font-medium text-charcoal">{formatMoney(invoice.balance ?? (invoice.total - invoice.amount_paid), invoice.currency)}</TableCell>
                                    </TableRow>
                                ))}
                                {invoices.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="h-16 text-center text-drift">No invoices yet.</TableCell>
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
