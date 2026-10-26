import { useState, useEffect } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import Pagination from '@/Components/Pagination';
import VehicleForm from '@/Components/Inventory/VehicleForm';
import StatusSelect from '@/Components/StatusSelect';
import InsightsSection from '@/Components/InsightsSection';
import FilterBar from '@/Components/FilterBar';
import { Plus, MoreVertical, Car, DollarSign } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { formatMoney } from '@/lib/utils';

const STATUS_COLORS = {
    in_stock: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
    sold: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    shipped: 'bg-purple-500/10 text-purple-500 border-purple-500/20',
    reserved: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
};

export default function Index({ vehicles, filters, clients, stats }) {
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';
    const [isSheetOpen, setIsSheetOpen] = useState(false);
    const [editingVehicle, setEditingVehicle] = useState(null);

    const openCreate = () => {
        setEditingVehicle(null);
        setIsSheetOpen(true);
    };

    const openEdit = (vehicle) => {
        setEditingVehicle(vehicle);
        setIsSheetOpen(true);
    };

    const confirmDelete = (vehicle) => {
        if (confirm('Are you sure you want to delete this vehicle?')) {
            router.delete(route('inventory.vehicles.destroy', vehicle.id));
        }
    };

    return (
        <AppLayout>
            <Head title="Inventory" />

            <div className="flex flex-col gap-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Inventory</h1>
                        <p className="text-drift mt-1">Manage vehicles and equipment.</p>
                    </div>
                    <Button onClick={openCreate} className="btn-sage shadow-soft hover:shadow-glow-sage">
                        <Plus className="mr-2 h-4 w-4" /> Add Vehicle
                    </Button>
                </div>

                {/* Insights & Filters */}
                <InsightsSection
                    stats={stats}
                    title="Inventory Status"
                    description="Current vehicle stock status"
                    totalLabel="Total Vehicles"
                />

                <FilterBar
                    search={filters.search}
                    status={filters.status}
                    statusOptions={[
                        { value: 'all', label: 'All Statuses' },
                        { value: 'in_stock', label: 'In Stock' },
                        { value: 'reserved', label: 'Reserved' },
                        { value: 'sold', label: 'Sold' },
                        { value: 'shipped', label: 'Shipped' },
                    ]}
                />

                {/* Data Table */}
                <Card className="bg-white border-linen shadow-sm overflow-hidden rounded-xl">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-linen hover:bg-cream bg-cream/50">
                                    <TableHead className="text-drift font-semibold">Vehicle Info</TableHead>
                                    <TableHead className="text-drift font-semibold">VIN</TableHead>
                                    <TableHead className="text-drift font-semibold">Details</TableHead>
                                    <TableHead className="text-drift font-semibold">Status</TableHead>
                                    <TableHead className="text-drift font-semibold">Price (Sale)</TableHead>
                                    <TableHead className="text-right text-drift font-semibold">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {vehicles.data.map((vehicle) => (
                                    <TableRow key={vehicle.id} className="border-linen hover:bg-cream transition-colors">
                                        <TableCell className="font-medium">
                                            <div className="flex items-center">
                                                <Car className="h-4 w-4 mr-3 text-pebble" />
                                                <div className="flex flex-col">
                                                    <span className="text-base font-semibold text-charcoal">{vehicle.year} {vehicle.make} {vehicle.model}</span>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <span className="font-mono text-xs text-stone bg-linen px-2 py-1 rounded">{vehicle.vin}</span>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col text-xs text-stone">
                                                <span>Color: {vehicle.color || '-'}</span>
                                                <span className="capitalize">Cond: {vehicle.condition}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <StatusSelect
                                                status={vehicle.status}
                                                options={[
                                                    { value: 'in_stock', label: 'In Stock' },
                                                    { value: 'reserved', label: 'Reserved' },
                                                    { value: 'sold', label: 'Sold' },
                                                    { value: 'shipped', label: 'Shipped' },
                                                ]}
                                                colorMap={{
                                                    in_stock: 'badge-success border-transparent',
                                                    reserved: 'badge-warning border-transparent',
                                                    sold: 'bg-blue-50 text-blue-600 border-transparent',
                                                    shipped: 'badge-sage border-transparent',
                                                }}
                                                onStatusChange={(newStatus) => {
                                                    router.patch(route('inventory.vehicles.update-status', vehicle.id), { status: newStatus }, {
                                                        preserveScroll: true,
                                                    });
                                                }}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="text-charcoal font-bold">
                                                    {vehicle.sale_price ? formatMoney(vehicle.sale_price, currency) : '-'}
                                                </span>
                                                {vehicle.purchase_price && (
                                                    <span className="text-xs text-pebble">Buy: {formatMoney(vehicle.purchase_price, currency)}</span>
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
                                                    <DropdownMenuItem onClick={() => openEdit(vehicle)} className="hover:bg-cream cursor-pointer">Edit Details</DropdownMenuItem>
                                                    <DropdownMenuSeparator className="bg-linen" />
                                                    <DropdownMenuItem onClick={() => confirmDelete(vehicle)} className="text-error hover:bg-error-soft cursor-pointer">Delete</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {vehicles.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={6} className="h-32 text-center text-pebble">
                                            <div className="flex flex-col items-center justify-center">
                                                <p className="font-medium">No vehicles found</p>
                                                <p className="text-xs mt-1">Add vehicles to your inventory</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Pagination links={vehicles.links} />

                <VehicleForm
                    open={isSheetOpen}
                    onOpenChange={setIsSheetOpen}
                    vehicle={editingVehicle}
                    clients={clients}
                />
            </div>
        </AppLayout>
    );
}
