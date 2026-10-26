import { useState, useEffect } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import Pagination from '@/Components/Pagination';
import ShipmentForm from '@/Components/Operations/ShipmentForm';
import StatusSelect from '@/Components/StatusSelect';
import InsightsSection from '@/Components/InsightsSection';
import FilterBar from '@/Components/FilterBar';
import { Plus, MoreVertical, MapPin, Calendar, DollarSign } from 'lucide-react';
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
    pending: 'bg-gray-500/10 text-gray-500 border-gray-500/20',
    received: 'bg-purple-500/10 text-purple-500 border-purple-500/20',
    scheduled: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    shipped: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
    arrived: 'bg-orange-500/10 text-orange-500 border-orange-500/20',
    delivered: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
};

const PAYMENT_STATUS_COLORS = {
    paid: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
    partial: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    unpaid: 'bg-red-500/10 text-red-500 border-red-500/20',
};

export default function Index({ shipments, filters, clients, users, stats }) {
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';
    const [isSheetOpen, setIsSheetOpen] = useState(false);
    const [editingShipment, setEditingShipment] = useState(null);

    // Auto-open create modal if requested in URL
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('create') === 'true') {
            openCreate();
            // Clear the param to avoid re-opening on manual refresh or back
            window.history.replaceState({}, '', window.location.pathname);
        }
    }, []);

    const openCreate = () => {
        setEditingShipment(null);
        setIsSheetOpen(true);
    };

    const openEdit = (shipment) => {
        setEditingShipment(shipment);
        setIsSheetOpen(true);
    };

    const confirmDelete = (shipment) => {
        if (confirm('Are you sure you want to delete this shipment?')) {
            router.delete(route('operations.shipments.destroy', shipment.id));
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return format(new Date(dateString), 'MMM d, yyyy');
    };

    return (
        <AppLayout>
            <Head title="Shipments" />

            <div className="flex flex-col gap-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Shipments</h1>
                        <p className="text-drift mt-1">Manage operations and logistics.</p>
                    </div>
                    <Button onClick={openCreate} className="btn-sage shadow-soft hover:shadow-glow-sage">
                        <Plus className="mr-2 h-4 w-4" /> Create Shipment
                    </Button>
                </div>

                {/* Insights & Filters */}
                <InsightsSection
                    stats={stats}
                    title="Shipment Status"
                    description="Current distribution of all shipments"
                    totalLabel="Total Shipments"
                />

                <FilterBar
                    search={filters.search}
                    status={filters.status}
                    statusOptions={[
                        { value: 'all', label: 'All Statuses' },
                        { value: 'pending', label: 'Pending/Awaiting' },
                        { value: 'received', label: 'Received' },
                        { value: 'scheduled', label: 'Scheduled for Loading' },
                        { value: 'shipped', label: 'Shipped' },
                        { value: 'arrived', label: 'Arrived at Port' },
                        { value: 'delivered', label: 'Delivered' },
                    ]}
                />

                {/* Data Table */}
                <Card className="bg-white border-linen shadow-sm overflow-hidden rounded-xl">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-linen hover:bg-cream bg-cream/50">
                                    <TableHead className="text-drift font-semibold">Shipment # / Title</TableHead>
                                    <TableHead className="text-drift font-semibold">VIN</TableHead>
                                    <TableHead className="text-drift font-semibold">Client</TableHead>
                                    <TableHead className="text-drift font-semibold">Status</TableHead>
                                    <TableHead className="text-drift font-semibold">Route</TableHead>
                                    <TableHead className="text-drift font-semibold">Dates</TableHead>
                                    <TableHead className="text-drift font-semibold">Value</TableHead>
                                    <TableHead className="text-right text-drift font-semibold">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {shipments.data.map((shipment) => (
                                    <TableRow key={shipment.id} className="border-linen hover:bg-cream transition-colors">
                                        <TableCell className="font-medium">
                                            <Link
                                                href={route('operations.shipments.show', shipment.id)}
                                                className="block p-2 -m-2 rounded-lg hover:bg-white transition-colors cursor-pointer touch-target group"
                                            >
                                                <div className="flex flex-col">
                                                    <span className="text-sage font-mono text-xs group-hover:underline">{shipment.shipment_number}</span>
                                                    <span className="text-base font-semibold text-charcoal group-hover:text-sage transition-colors">{shipment.title}</span>
                                                    {shipment.assignee && <span className="text-xs text-pebble">Assigned: {shipment.assignee.name}</span>}
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <span className="font-mono text-xs text-stone bg-linen px-2 py-1 rounded">{shipment.vin || '-'}</span>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="text-sm text-charcoal">{shipment.client?.company_name}</span>
                                                <span className="text-xs text-pebble">{shipment.client?.contact_person}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <StatusSelect
                                                status={shipment.status}
                                                options={[
                                                    { value: 'pending', label: 'Pending/Awaiting' },
                                                    { value: 'received', label: 'Received' },
                                                    { value: 'scheduled', label: 'Scheduled for Loading' },
                                                    { value: 'shipped', label: 'Shipped' },
                                                    { value: 'arrived', label: 'Arrived at Port' },
                                                    { value: 'delivered', label: 'Delivered' },
                                                ]}
                                                colorMap={{
                                                    pending: 'bg-linen text-stone border-transparent',
                                                    received: 'bg-purple-50 text-purple-600 border-transparent',
                                                    scheduled: 'bg-blue-50 text-blue-600 border-transparent',
                                                    shipped: 'badge-sage border-transparent',
                                                    arrived: 'badge-warning border-transparent',
                                                    delivered: 'badge-success border-transparent',
                                                }}
                                                onStatusChange={(newStatus) => {
                                                    router.patch(route('operations.shipments.update-status', shipment.id), { status: newStatus }, {
                                                        preserveScroll: true,
                                                    });
                                                }}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col gap-1 text-xs text-stone">
                                                {shipment.origin && (
                                                    <div className="flex items-center">
                                                        <span className="text-sage w-3 mx-1">●</span> {shipment.origin}
                                                    </div>
                                                )}
                                                {shipment.destination && (
                                                    <div className="flex items-center">
                                                        <span className="text-charcoal w-3 mx-1">●</span> {shipment.destination}
                                                    </div>
                                                )}
                                                {!shipment.origin && !shipment.destination && <span className="text-pebble">-</span>}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center text-sm text-stone">
                                                <Calendar className="mr-2 h-3.5 w-3.5 text-pebble" />
                                                {formatDate(shipment.estimated_completion)}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col items-start gap-1">
                                                <div className="flex items-center text-sm text-charcoal font-medium">
                                                    {shipment.total_value ? formatMoney(shipment.total_value, currency) : '-'}
                                                </div>
                                                {shipment.payment_status && (
                                                    <Badge variant="outline" className={`text-[10px] py-0 h-5 border-transparent ${PAYMENT_STATUS_COLORS[shipment.payment_status] || 'bg-linen text-stone'}`}>
                                                        {shipment.payment_status.charAt(0).toUpperCase() + shipment.payment_status.slice(1)}
                                                    </Badge>
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
                                                    <DropdownMenuItem onClick={() => openEdit(shipment)} className="hover:bg-cream cursor-pointer">Edit Details</DropdownMenuItem>
                                                    <DropdownMenuSeparator className="bg-linen" />
                                                    <DropdownMenuItem onClick={() => confirmDelete(shipment)} className="text-error hover:bg-error-soft cursor-pointer">Delete</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {shipments.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={7} className="h-32 text-center">
                                            <div className="flex flex-col items-center justify-center text-pebble">
                                                <p className="font-medium">No shipments found</p>
                                                <p className="text-xs mt-1">Create your first shipment to get started</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <ShipmentForm
                    open={isSheetOpen}
                    onOpenChange={setIsSheetOpen}
                    shipment={editingShipment}
                    clients={clients}
                    users={users}
                />
            </div>
        </AppLayout>
    );
}
