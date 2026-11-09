import { useState, useEffect } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import Pagination from '@/Components/Pagination';
import KanbanBoard from '@/Components/CRM/KanbanBoard';
import LeadForm from '@/Components/CRM/LeadForm';
import StatusSelect from '@/Components/StatusSelect';
import InsightsSection from '@/Components/InsightsSection';
import FilterBar from '@/Components/FilterBar';
import { Plus, LayoutGrid, List as ListIcon, MoreVertical, DollarSign, Target } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";

export default function Index({ leads, filters, clients, users, stats }) {
    const [view, setView] = useState(filters.view || 'list');
    const [isSheetOpen, setIsSheetOpen] = useState(false);
    const [editingLead, setEditingLead] = useState(null);

    const changeView = (newView) => {
        setView(newView);
        // We use router.get to preserve search while changing view, FilterBar handles search
        router.get(route('crm.leads.index'), { search: filters.search, view: newView }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    };

    const openCreate = () => {
        setEditingLead(null);
        setIsSheetOpen(true);
    };

    const openEdit = (lead) => {
        setEditingLead(lead);
        setIsSheetOpen(true);
    };

    const confirmDelete = (lead) => {
        if (confirm('Are you sure you want to delete this lead?')) {
            router.delete(route('crm.leads.destroy', lead.id));
        }
    };

    const handleDragEnd = (result) => {
        const { destination, source, draggableId } = result;

        if (!destination) return;
        if (destination.droppableId === source.droppableId && destination.index === source.index) return;

        const leadId = draggableId;
        const newStatus = destination.droppableId;

        // Optimistic update (optional, but for now we just hit backend)
        router.patch(route('crm.leads.update-status', leadId), { status: newStatus }, {
            preserveScroll: true,
            preserveState: false, // Reload to sync grouping
        });
    };

    return (
        <AppLayout>
            <Head title="Leads" />

            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-6 h-full">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-serif font-bold tracking-tight text-void">Leads</h1>
                        <p className="text-drift">Track your sales pipeline.</p>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="flex items-center bg-white rounded-lg p-1 border border-linen shadow-sm">
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => changeView('kanban')}
                                className={view === 'kanban' ? 'bg-cream text-void' : 'text-drift hover:text-void'}
                            >
                                <LayoutGrid className="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => changeView('list')}
                                className={view === 'list' ? 'bg-cream text-void' : 'text-drift hover:text-void'}
                            >
                                <ListIcon className="h-4 w-4" />
                            </Button>
                        </div>
                        <Button onClick={openCreate} className="bg-sage hover:bg-maple-deep text-white">
                            <Plus className="mr-2 h-4 w-4" /> Add Lead
                        </Button>
                    </div>
                </div>

                {/* Insights & Filters */}
                <InsightsSection
                    icon={Target}
                    stats={stats}
                    title="Pipeline Overview"
                    description="Distribution of leads by status"
                    totalLabel="Total Leads"
                />

                <FilterBar
                    search={filters.search}
                    // Leads doesn't use simple status filtering in Index mainly because of Kanban, 
                    // but we can support it for Search or future List view filtering.
                    // For now, removing status filter as it might conflict with Kanban columns view logic
                    // or adding it if we want to filter within columns.
                    // Let's keep it simple: Search only.
                    statusOptions={[]}
                />

                {/* Content */}
                {view === 'kanban' ? (
                    <KanbanBoard leads={leads} onDragEnd={handleDragEnd} onEdit={openEdit} />
                ) : (
                    <Card className="bg-white border-linen shadow-sm">
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="border-linen hover:bg-cream/50">
                                        <TableHead className="text-drift font-medium">Title / Contact</TableHead>
                                        <TableHead className="text-drift font-medium">Status</TableHead>
                                        <TableHead className="text-drift font-medium">Value</TableHead>
                                        <TableHead className="text-drift font-medium">Priority</TableHead>
                                        <TableHead className="text-right text-drift font-medium">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {leads.data.map((lead) => (
                                        <TableRow key={lead.id} className="border-linen hover:bg-cream transition-colors">
                                            <TableCell className="font-medium">
                                                <div className="flex flex-col">
                                                    <span className="text-base font-semibold text-void">{lead.title}</span>
                                                    <span className="text-sm text-drift">{lead.contact_name || lead.client?.company_name}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <StatusSelect
                                                    status={lead.status}
                                                    options={[
                                                        { value: 'new', label: 'New' },
                                                        { value: 'contacted', label: 'Contacted' },
                                                        { value: 'qualified', label: 'Qualified' },
                                                        { value: 'proposal', label: 'Proposal' },
                                                        { value: 'negotiation', label: 'Negotiation' },
                                                        { value: 'won', label: 'Won' },
                                                        { value: 'lost', label: 'Lost' },
                                                    ]}
                                                    colorMap={{
                                                        new: 'bg-gray-100 text-gray-600 border-gray-200',
                                                        contacted: 'bg-gray-200 text-gray-800 border-gray-300',
                                                        qualified: 'bg-amber-50 text-amber-700 border-amber-200',
                                                        proposal: 'bg-amber-100 text-amber-800 border-amber-300',
                                                        negotiation: 'bg-red-50 text-red-600 border-red-200',
                                                        won: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        lost: 'bg-red-700 text-white border-red-700',
                                                    }}
                                                    onStatusChange={(newStatus) => {
                                                        router.patch(route('crm.leads.update-status', lead.id), { status: newStatus }, {
                                                            preserveScroll: true,
                                                        });
                                                    }}
                                                />
                                            </TableCell>
                                            <TableCell className="text-void">
                                                {lead.estimated_value ? `₦${Number(lead.estimated_value).toLocaleString()}` : '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary" className={
                                                    lead.priority === 'high' ? 'bg-sage-soft text-sage border-sage/20' :
                                                        lead.priority === 'medium' ? 'bg-amber-50 text-amber-600 border-amber-200' :
                                                            'bg-gray-100 text-gray-600 border-gray-200'
                                                }>
                                                    {lead.priority}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" className="h-8 w-8 p-0 text-drift hover:text-void">
                                                            <MoreVertical className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end" className="bg-white border-linen text-void">
                                                        <DropdownMenuItem onClick={() => openEdit(lead)} className="hover:bg-cream cursor-pointer">Edit</DropdownMenuItem>
                                                        <DropdownMenuSeparator className="bg-sage-soft" />
                                                        <DropdownMenuItem onClick={() => confirmDelete(lead)} className="text-red-500 hover:bg-red-50 cursor-pointer">Delete</DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    {leads.data.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={5} className="h-24 text-center text-drift">No leads found.</TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                            <Pagination links={leads.links} />
                        </CardContent>
                    </Card>
                )}

                <LeadForm
                    open={isSheetOpen}
                    onOpenChange={setIsSheetOpen}
                    lead={editingLead}
                    clients={clients}
                    users={users}
                />
            </div>
        </AppLayout>
    );
}
