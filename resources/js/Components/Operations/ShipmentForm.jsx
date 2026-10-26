import { useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetFooter } from "@/Components/ui/sheet";
import { Textarea } from "@/Components/ui/textarea";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { format } from "date-fns";
import { cn } from "@/lib/utils";
import { Check, ChevronsUpDown, Search as SearchIcon } from "lucide-react";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/Components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import axios from 'axios';
import { useState } from 'react';

export default function ShipmentForm({ open, onOpenChange, shipment = null, clients = [], users = [] }) {
    const isEdit = !!shipment;
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';
    const [clientOpen, setClientOpen] = useState(false);
    const [clientsList, setClientsList] = useState(clients);
    const [searchQuery, setSearchQuery] = useState('');

    useEffect(() => {
        if (!searchQuery) {
            setClientsList(clients);
            return;
        }

        const timer = setTimeout(() => {
            axios.get(route('crm.clients.search'), { params: { query: searchQuery } })
                .then(res => {
                    setClientsList(res.data);
                })
                .catch(err => console.error(err));
        }, 300);

        return () => clearTimeout(timer);
    }, [searchQuery, clients]);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        title: '',
        client_id: '',
        status: 'received',
        service_type: '',
        origin: '',
        destination: '',
        estimated_completion: '',
        total_value: '',
        assigned_to: '',
        description: '',
        vin: '',
    });

    const selectedClient = clientsList.find(c => String(c.id) === data.client_id) || clients.find(c => String(c.id) === data.client_id);

    useEffect(() => {
        if (shipment) {
            setData({
                title: shipment.title || '',
                client_id: shipment.client_id ? String(shipment.client_id) : '',
                status: shipment.status || 'received',
                service_type: shipment.service_type || '',
                origin: shipment.origin || '',
                destination: shipment.destination || '',
                estimated_completion: shipment.estimated_completion ? shipment.estimated_completion.split('T')[0] : '',
                total_value: shipment.total_value || '',
                assigned_to: shipment.assigned_to ? String(shipment.assigned_to) : '',
                description: shipment.description || '',
                vin: shipment.vin || '',
            });
        } else {
            reset();
        }
    }, [shipment, open]);

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            put(route('operations.shipments.update', shipment.id), {
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(route('operations.shipments.store'), {
                onSuccess: () => onOpenChange(false),
            });
        }
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="overflow-y-auto bg-white border-l border-linen text-charcoal sm:max-w-md shadow-lg">
                <SheetHeader>
                    <SheetTitle className="text-display-sm text-void font-bold">{isEdit ? 'Edit Shipment' : 'Create New Shipment'}</SheetTitle>
                    <SheetDescription className="text-drift">
                        {isEdit ? 'Update shipment details.' : 'Create a new operations shipment.'}
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={submit} className="space-y-6 mt-6">
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="title" className="text-charcoal font-medium">Shipment Title</Label>
                            <Input id="title" value={data.title} onChange={e => setData('title', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg" placeholder="e.g. Shipment #1234" />
                            {errors.title && <span className="text-error text-sm">{errors.title}</span>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="vin" className="text-charcoal font-medium">VIN (Optional)</Label>
                            <Input id="vin" value={data.vin} onChange={e => setData('vin', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg font-mono uppercase" placeholder="Vehicle Identification Number" />
                            {errors.vin && <span className="text-error text-sm">{errors.vin}</span>}
                        </div>

                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Client</Label>
                            <Popover open={clientOpen} onOpenChange={setClientOpen}>
                                <PopoverTrigger asChild>
                                    <Button
                                        variant="outline"
                                        role="combobox"
                                        aria-expanded={clientOpen}
                                        className="w-full justify-between bg-cream border-linen text-charcoal h-11 rounded-lg hover:bg-cream/80"
                                    >
                                        {selectedClient
                                            ? (selectedClient.company_name || selectedClient.contact_person)
                                            : "Select a client..."}
                                        <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 text-pebble" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-[300px] p-0 bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    <Command className="bg-white text-charcoal" shouldFilter={false}>
                                        <CommandInput placeholder="Search clients..." value={searchQuery} onValueChange={setSearchQuery} className="text-charcoal placeholder:text-pebble" />
                                        <CommandList>
                                            <CommandEmpty className="py-6 text-center text-sm text-pebble">No client found.</CommandEmpty>
                                            <CommandGroup>
                                                {clientsList.map((client) => (
                                                    <CommandItem
                                                        key={client.id}
                                                        value={String(client.id)}
                                                        onSelect={() => {
                                                            setData('client_id', String(client.id));
                                                            setClientOpen(false);
                                                        }}
                                                        className="cursor-pointer text-charcoal aria-selected:bg-cream aria-selected:text-void pl-8 relative"
                                                    >
                                                        <Check
                                                            className={cn(
                                                                "absolute left-2 h-4 w-4 text-sage",
                                                                data.client_id === String(client.id) ? "opacity-100" : "opacity-0"
                                                            )}
                                                        />
                                                        {client.company_name || client.contact_person}
                                                    </CommandItem>
                                                ))}
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            {errors.client_id && <span className="text-error text-sm">{errors.client_id}</span>}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Status</Label>
                                <Select value={data.status} onValueChange={(val) => setData('status', val)}>
                                    <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                        <SelectItem value="pending" className="focus:bg-cream cursor-pointer">Pending/Awaiting</SelectItem>
                                        <SelectItem value="received" className="focus:bg-cream cursor-pointer">Received</SelectItem>
                                        <SelectItem value="scheduled" className="focus:bg-cream cursor-pointer">Scheduled for Loading</SelectItem>
                                        <SelectItem value="shipped" className="focus:bg-cream cursor-pointer">Shipped</SelectItem>
                                        <SelectItem value="arrived" className="focus:bg-cream cursor-pointer">Arrived at Port</SelectItem>
                                        <SelectItem value="delivered" className="focus:bg-cream cursor-pointer">Delivered</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Service Type</Label>
                                <Input value={data.service_type} onChange={e => setData('service_type', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" placeholder="e.g. Logistics" />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Origin</Label>
                                <Input value={data.origin} onChange={e => setData('origin', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Destination</Label>
                                <Input value={data.destination} onChange={e => setData('destination', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Est. Completion</Label>
                                <Input type="date" value={data.estimated_completion} onChange={e => setData('estimated_completion', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg [color-scheme:light]" />
                            </div>
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Value ({currency})</Label>
                                <Input type="number" value={data.total_value} onChange={e => setData('total_value', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Assigned To</Label>
                            <Select value={data.assigned_to} onValueChange={(val) => setData('assigned_to', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue placeholder="Select staff member" />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    {users.map(user => (
                                        <SelectItem key={user.id} value={String(user.id)} className="focus:bg-cream cursor-pointer">
                                            {user.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description" className="text-charcoal font-medium">Details / Notes</Label>
                        <Textarea id="description" value={data.description} onChange={e => setData('description', e.target.value)} className="bg-cream border-linen text-charcoal min-h-[80px] rounded-lg" />
                    </div>

                    <SheetFooter>
                        <Button type="submit" className="w-full btn-sage shadow-soft hover:shadow-glow-sage" disabled={processing}>
                            {isEdit ? 'Update Shipment' : 'Create Shipment'}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}
