import { useState, useEffect } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import Pagination from '@/Components/Pagination';
import { Plus, MoreVertical, Phone, Mail } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import ClientForm from '@/Components/CRM/ClientForm';
import InsightsSection from '@/Components/InsightsSection';
import FilterBar from '@/Components/FilterBar';

export default function Index({ clients, filters, stats }) {
    const [isSheetOpen, setIsSheetOpen] = useState(false);
    const [editingClient, setEditingClient] = useState(null);

    const openCreate = () => {
        setEditingClient(null);
        setIsSheetOpen(true);
    };

    const openEdit = (client) => {
        setEditingClient(client);
        setIsSheetOpen(true);
    };

    const confirmDelete = (client) => {
        if (confirm('Are you sure you want to delete this client?')) {
            router.delete(route('crm.clients.destroy', client.id));
        }
    };

    return (
        <AppLayout>
            <Head title="Clients" />

            <div className="flex flex-col gap-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">Clients</h1>
                        <p className="text-drift mt-1">Manage your customer database.</p>
                    </div>
                    <Button onClick={openCreate} className="btn-sage shadow-soft hover:shadow-glow-sage">
                        <Plus className="mr-2 h-4 w-4" /> Add Client
                    </Button>
                </div>

                {/* Insights & Filters */}
                <InsightsSection
                    stats={stats}
                    title="Client Status"
                    description="Distribution of active vs inactive clients"
                    totalLabel="Total Clients"
                />

                <FilterBar
                    search={filters.search}
                    status={filters.status}
                    statusOptions={[
                        { value: 'all', label: 'All Statuses' },
                    ]}
                />

                {/* Data Table */}
                <Card className="bg-white border-linen shadow-sm overflow-hidden rounded-xl">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="border-linen hover:bg-cream bg-cream/50">
                                    <TableHead className="text-drift font-semibold">Company / Contact</TableHead>
                                    <TableHead className="text-drift font-semibold">Contact Info</TableHead>
                                    <TableHead className="text-drift font-semibold">Location</TableHead>
                                    <TableHead className="text-drift font-semibold">Status</TableHead>
                                    <TableHead className="text-right text-drift font-semibold">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {clients.data.map((client) => (
                                    <TableRow key={client.id} className="border-linen hover:bg-cream transition-colors">
                                        <TableCell className="font-medium">
                                            <Link
                                                href={route('crm.clients.show', client.id)}
                                                className="block p-2 -m-2 rounded-lg hover:bg-white transition-colors cursor-pointer touch-target"
                                            >
                                                <div className="flex flex-col">
                                                    <span className="text-base font-semibold text-charcoal hover:text-void transition-colors">
                                                        {client.company_name || client.contact_person || 'Unnamed Client'}
                                                    </span>
                                                    {client.company_name && client.contact_person && (
                                                        <span className="text-sm text-pebble">{client.contact_person}</span>
                                                    )}
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex flex-col gap-1">
                                                <div className="flex items-center text-sm text-stone">
                                                    <Mail className="mr-2 h-3.5 w-3.5 text-pebble" />
                                                    {client.email}
                                                </div>
                                                <a href={`tel:${client.phone}`} className="flex items-center text-sm text-stone hover:text-charcoal transition-colors touch-target">
                                                    <Phone className="mr-2 h-3.5 w-3.5 text-pebble" />
                                                    {client.phone}
                                                </a>
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-stone">
                                            {client.city}, {client.country}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline" className={client.is_active
                                                ? "badge-success border-transparent"
                                                : "badge-muted border-transparent"}>
                                                {client.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" className="h-8 w-8 p-0 text-pebble hover:text-charcoal">
                                                        <span className="sr-only">Open menu</span>
                                                        <MoreVertical className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                                    <DropdownMenuLabel className="text-drift">Actions</DropdownMenuLabel>
                                                    <DropdownMenuItem onClick={() => openEdit(client)} className="hover:bg-cream cursor-pointer">Edit Details</DropdownMenuItem>
                                                    <DropdownMenuItem className="hover:bg-cream cursor-pointer">View History</DropdownMenuItem>
                                                    <DropdownMenuSeparator className="bg-linen" />
                                                    <DropdownMenuItem onClick={() => confirmDelete(client)} className="text-error hover:bg-error-soft cursor-pointer">Delete</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {clients.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="h-32 text-center">
                                            <div className="flex flex-col items-center justify-center text-pebble">
                                                <p className="font-medium">No clients found</p>
                                                <p className="text-xs mt-1">Get started by creating a new client</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* Pagination */}
                <Pagination links={clients.links} />

                <ClientForm
                    open={isSheetOpen}
                    onOpenChange={setIsSheetOpen}
                    client={editingClient}
                />
            </div>
        </AppLayout>
    );
}
