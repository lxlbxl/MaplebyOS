import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Separator } from "@/Components/ui/separator";
import ShipmentForm from '@/Components/Operations/ShipmentForm';
import StatusSelect from '@/Components/StatusSelect';
import {
    ArrowLeft, Package, User, MapPin, Calendar, DollarSign,
    Truck, Ship, Anchor, CheckCircle2, Circle, Clock, MoreVertical,
    FileText, Pencil
} from 'lucide-react';
import { format } from 'date-fns';
import { formatMoney, cn } from '@/lib/utils';

const STATUS_STEPS = [
    { id: 'pending', label: 'Pending', icon: Circle },
    { id: 'received', label: 'Received', icon: Package },
    { id: 'scheduled', label: 'Scheduled', icon: Clock },
    { id: 'shipped', label: 'Shipped', icon: Ship },
    { id: 'arrived', label: 'Arrived', icon: Anchor },
    { id: 'delivered', label: 'Delivered', icon: CheckCircle2 },
];

const STATUS_COLORS = {
    pending: 'bg-gray-500/10 text-gray-500 border-gray-500/20',
    received: 'bg-purple-500/10 text-purple-500 border-purple-500/20',
    scheduled: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    shipped: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
    arrived: 'bg-orange-500/10 text-orange-500 border-orange-500/20',
    delivered: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
};

const STATUS_LABELS = {
    pending: 'Pending/Awaiting',
    received: 'Received',
    scheduled: 'Scheduled for Loading',
    shipped: 'Shipped',
    arrived: 'Arrived at Port',
    delivered: 'Delivered',
};

export default function Show({ shipment, clients, users, totalPaid = 0, totalQuoted = 0 }) {
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';
    const [isSheetOpen, setIsSheetOpen] = useState(false);

    const formatDate = (dateString, formatStr = 'PPP') => {
        if (!dateString) return '-';
        return format(new Date(dateString), formatStr);
    };

    const currentStepIndex = STATUS_STEPS.findIndex(s => s.id === shipment.status);

    const handleStatusChange = (newStatus) => {
        router.patch(route('operations.shipments.update-status', shipment.id), { status: newStatus }, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout>
            <Head title={`Shipment ${shipment.shipment_number}`} />

            <div className="flex flex-col gap-6 max-w-7xl mx-auto">
                {/* Header Navigation */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('operations.shipments.index')}>
                            <Button variant="outline" size="icon" className="border-sage-200 text-sage-600 hover:bg-sage-50">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-3xl font-serif font-bold tracking-tight text-sage-600">
                                    {shipment.title}
                                </h1>
                                <Badge variant="outline" className="font-mono text-coral-500 border-coral-200 bg-coral-50">
                                    {shipment.shipment_number}
                                </Badge>
                            </div>
                            <p className="text-sage-400">Detailed overview and tracking</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button onClick={() => setIsSheetOpen(true)} className="bg-sage-600 hover:bg-sage-700 text-white">
                            <Pencil className="mr-2 h-4 w-4" /> Edit Details
                        </Button>
                    </div>
                </div>

                {/* Journey / Timeline Visualization */}
                <Card className="bg-white border-sage-100 shadow-sm overflow-hidden">
                    <CardHeader className="border-b border-sage-100 bg-sage-50/30 pb-4">
                        <CardTitle className="text-sage-700 flex items-center gap-2">
                            <Ship className="h-5 w-5" /> Shipment Journey
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="p-8">
                        <div className="relative flex items-center justify-between w-full">
                            {/* Connecting Line */}
                            <div className="absolute top-1/2 left-0 w-full h-1 bg-sage-100 -z-10 -translate-y-1/2 rounded-full" />

                            {/* Active Line Progress */}
                            <div
                                className="absolute top-1/2 left-0 h-1 bg-sage-400 -z-10 -translate-y-1/2 rounded-full transition-all duration-500 ease-in-out"
                                style={{ width: `${Math.max(0, Math.min(100, (currentStepIndex / (STATUS_STEPS.length - 1)) * 100))}%` }}
                            />

                            {/* Steps */}
                            {STATUS_STEPS.map((step, index) => {
                                const isActive = index <= currentStepIndex;
                                const isCurrent = index === currentStepIndex;
                                const Icon = step.icon;

                                return (
                                    <div key={step.id} className="flex flex-col items-center gap-2 group">
                                        <div className={cn(
                                            "relative flex items-center justify-center w-12 h-12 rounded-full border-4 transition-all duration-300 z-10",
                                            isActive ? "bg-white border-sage-400 shadow-lg scale-110" : "bg-sage-50 border-sage-200 text-sage-300",
                                            isCurrent && "ring-4 ring-sage-400/20"
                                        )}>
                                            <Icon className={cn(
                                                "w-5 h-5 transition-colors",
                                                isActive ? "text-sage-600" : "text-sage-300"
                                            )} />
                                        </div>
                                        <span className={cn(
                                            "text-sm font-medium transition-colors absolute -bottom-8 whitespace-nowrap",
                                            isActive ? "text-sage-700" : "text-sage-300"
                                        )}>
                                            {step.label}
                                        </span>
                                    </div>
                                );
                            })}
                        </div>

                        <div className="mt-16 flex justify-between items-center bg-sage-50/50 p-4 rounded-xl border border-sage-100">
                            <div className="flex flex-col">
                                <span className="text-xs text-sage-400 uppercase tracking-wider mb-1">Origin</span>
                                <div className="flex items-center gap-2 text-sage-700 font-semibold text-lg">
                                    <MapPin className="h-5 w-5 text-coral-500" />
                                    {shipment.origin || 'Not Specified'}
                                </div>
                            </div>
                            <Truck className="h-6 w-6 text-sage-300 animate-pulse" />
                            <div className="flex flex-col items-end">
                                <span className="text-xs text-sage-400 uppercase tracking-wider mb-1">Destination</span>
                                <div className="flex items-center gap-2 text-sage-700 font-semibold text-lg">
                                    {shipment.destination || 'Not Specified'}
                                    <MapPin className="h-5 w-5 text-emerald-500" />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left Column: Shipment Details & Status */}
                    <div className="lg:col-span-2 flex flex-col gap-6">
                        {/* Status & Dates Card */}
                        <Card className="bg-white border-sage-100 shadow-sm">
                            <CardHeader className="border-b border-sage-100 bg-sage-50/30">
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-sage-700">Shipment Insight</CardTitle>
                                    <Badge variant="outline" className={cn("px-3 py-1 capitalize", STATUS_COLORS[shipment.status])}>
                                        {STATUS_LABELS[shipment.status]}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="p-6 grid md:grid-cols-2 gap-8">
                                <div className="space-y-4">
                                    <h3 className="text-sm font-medium text-sage-400 uppercase tracking-wide">Current Status</h3>
                                    <div className="bg-sage-50 p-4 rounded-lg border border-sage-100">
                                        <p className="text-sm text-sage-500 mb-2">Update Shipment Status:</p>
                                        <StatusSelect
                                            status={shipment.status}
                                            options={[
                                                { value: 'received', label: 'Received' },
                                                { value: 'scheduled', label: 'Scheduled for Loading' },
                                                { value: 'shipped', label: 'Shipped' },
                                                { value: 'arrived', label: 'Arrived at Port' },
                                                { value: 'delivered', label: 'Delivered' },
                                            ]}
                                            colorMap={STATUS_COLORS}
                                            onStatusChange={handleStatusChange}
                                        />
                                    </div>
                                    <div className="space-y-1">
                                        <span className="text-xs text-sage-400 block">Service Type</span>
                                        <span className="text-base text-sage-700 font-medium">{shipment.service_type || 'Standard Logistics'}</span>
                                    </div>
                                </div>
                                <div className="space-y-4">
                                    <h3 className="text-sm font-medium text-sage-400 uppercase tracking-wide">Key Dates</h3>
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between p-2 hover:bg-sage-50 rounded-md transition-colors">
                                            <div className="flex items-center gap-3">
                                                <div className="bg-emerald-100 p-2 rounded-full">
                                                    <Package className="h-4 w-4 text-emerald-600" />
                                                </div>
                                                <span className="text-sage-600">Created Date</span>
                                            </div>
                                            <span className="font-mono text-sage-700">{formatDate(shipment.created_at)}</span>
                                        </div>
                                        <div className="flex items-center justify-between p-2 hover:bg-sage-50 rounded-md transition-colors">
                                            <div className="flex items-center gap-3">
                                                <div className="bg-blue-100 p-2 rounded-full">
                                                    <Calendar className="h-4 w-4 text-blue-600" />
                                                </div>
                                                <span className="text-sage-600">Est. Completion</span>
                                            </div>
                                            <span className="font-mono text-sage-700">{formatDate(shipment.estimated_completion)}</span>
                                        </div>
                                        <div className="flex items-center justify-between p-2 hover:bg-sage-50 rounded-md transition-colors">
                                            <div className="flex items-center gap-3">
                                                <div className="bg-sage-100 p-2 rounded-full">
                                                    <Clock className="h-4 w-4 text-sage-600" />
                                                </div>
                                                <span className="text-sage-600">Last Updated</span>
                                            </div>
                                            <span className="font-mono text-sage-700">{formatDate(shipment.updated_at)}</span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Description / Notes */}
                        <Card className="bg-white border-sage-100 shadow-sm">
                            <CardHeader className="border-b border-sage-100 bg-sage-50/30">
                                <CardTitle className="text-sage-700 flex items-center gap-2">
                                    <FileText className="h-5 w-5" /> Details & Notes
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                <div className="prose prose-sage max-w-none text-sage-600">
                                    {shipment.description ? (
                                        <p className="whitespace-pre-wrap">{shipment.description}</p>
                                    ) : (
                                        <p className="text-sage-400 italic">No additional notes or details provided for this shipment.</p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Right Column: Client & Value */}
                    <div className="flex flex-col gap-6">
                        {/* Financials */}
                        <Card className="bg-white border-sage-100 shadow-sm">
                            <CardHeader className="border-b border-sage-100 bg-sage-50/30">
                                <CardTitle className="text-sage-700 flex items-center gap-2">
                                    <DollarSign className="h-5 w-5" /> Financials
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-6 space-y-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between items-center text-sm">
                                        <span className="text-sage-500">Total Quoted</span>
                                        <span className="font-semibold text-sage-700">{formatMoney(totalQuoted, currency)}</span>
                                    </div>
                                    <div className="flex justify-between items-center text-sm">
                                        <span className="text-sage-500">Amount Paid</span>
                                        <span className="font-semibold text-emerald-600">-{formatMoney(totalPaid, currency)}</span>
                                    </div>
                                    <Separator className="bg-sage-100" />
                                    <div className="flex justify-between items-center">
                                        <span className="text-sage-700 font-bold">Balance Due</span>
                                        <span className={cn(
                                            "font-bold text-lg",
                                            (totalQuoted - totalPaid) > 0.01 ? "text-coral-500" : "text-emerald-500"
                                        )}>
                                            {formatMoney(Math.max(0, totalQuoted - totalPaid), currency)}
                                        </span>
                                    </div>
                                </div>

                                <div className="pt-2">
                                    {shipment.payment_status && (
                                        <div className={cn(
                                            "w-full py-2 rounded-lg text-center font-bold text-sm border",
                                            shipment.payment_status === 'paid' ? "bg-emerald-50 text-emerald-600 border-emerald-200" :
                                                shipment.payment_status === 'partial' ? "bg-blue-50 text-blue-600 border-blue-200" :
                                                    "bg-red-50 text-red-500 border-red-200"
                                        )}>
                                            {shipment.payment_status.toUpperCase()}
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Client Card */}
                        <Card className="bg-white border-sage-100 shadow-sm">
                            <CardHeader className="border-b border-sage-100 bg-sage-50/30">
                                <CardTitle className="text-sage-700 flex items-center gap-2">
                                    <User className="h-5 w-5" /> Client Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                {shipment.client ? (
                                    <div className="space-y-4">
                                        <div className="flex items-center gap-3">
                                            <div className="bg-sage-100 h-10 w-10 rounded-full flex items-center justify-center text-sage-600 font-bold">
                                                {shipment.client.company_name?.[0] || shipment.client.contact_person?.[0]}
                                            </div>
                                            <div>
                                                <Link href={route('crm.clients.show', shipment.client.id)} className="font-semibold text-sage-700 hover:text-coral-500 transition-colors">
                                                    {shipment.client.company_name}
                                                </Link>
                                                <div className="text-sm text-sage-400">{shipment.client.contact_person}</div>
                                            </div>
                                        </div>
                                        <Separator className="bg-sage-100" />
                                        <div className="space-y-2 text-sm">
                                            <div className="flex justify-between">
                                                <span className="text-sage-400">Email:</span>
                                                <span className="text-sage-600">{shipment.client.email || '-'}</span>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-sage-400">Phone:</span>
                                                <a href={`tel:${shipment.client.phone}`} className="text-sage-600 hover:text-coral-500 hover:underline transition-colors">{shipment.client.phone || '-'}</a>
                                            </div>
                                        </div>
                                    </div>
                                ) : (
                                    <p className="text-sage-400">No client assigned.</p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Assignee Card */}
                        <Card className="bg-white border-sage-100 shadow-sm">
                            <CardHeader className="border-b border-sage-100 bg-sage-50/30">
                                <CardTitle className="text-sage-700 flex items-center gap-2">
                                    <User className="h-5 w-5" /> Assigned Staff
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                {shipment.assignee ? (
                                    <div className="flex items-center gap-3">
                                        <div className="bg-coral-100 h-10 w-10 rounded-full flex items-center justify-center text-coral-600 font-bold">
                                            {shipment.assignee.name[0]}
                                        </div>
                                        <div>
                                            <p className="font-semibold text-sage-700">{shipment.assignee.name}</p>
                                            <p className="text-xs text-sage-400">Staff Member</p>
                                        </div>
                                    </div>
                                ) : (
                                    <p className="text-sage-400 italic">Unassigned</p>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <ShipmentForm
                    open={isSheetOpen}
                    onOpenChange={setIsSheetOpen}
                    shipment={shipment}
                    clients={clients}
                    users={users}
                />
            </div>
        </AppLayout >
    );
}
