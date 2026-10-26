import { useEffect, useState } from 'react';
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
import { Trash2, Plus } from "lucide-react";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";

export default function QuoteForm({ open, onOpenChange, quote = null, clients = [], shipments = [], vehicles = [] }) {
    const isEdit = !!quote;
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';

    // Tax rate state local to calculate totals visually
    const [taxRate, setTaxRate] = useState(0);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        client_id: '',
        status: 'draft',
        issue_date: new Date().toISOString().split('T')[0],
        expiry_date: '',
        items: [{ description: '', quantity: 1, unit_price: 0 }],
        notes: '',
        terms_conditions: '',
        tax_rate: 0,
        shipment_id: '',
        inventory_type: '',
        inventory_id: '',
    });

    // Filter shipments based on selected client
    const filteredShipments = data.client_id
        ? shipments.filter(s => String(s.client_id) === String(data.client_id))
        : [];

    // Determine initial type based on existing data
    const [quoteType, setQuoteType] = useState('shipment');

    useEffect(() => {
        if (open) {
            if (quote) {
                setData({
                    client_id: quote.client_id ? String(quote.client_id) : '',
                    status: quote.status || 'draft',
                    issue_date: quote.issue_date?.split('T')[0] || new Date().toISOString().split('T')[0],
                    expiry_date: quote.expiry_date?.split('T')[0] || '',
                    items: quote.items && quote.items.length > 0 ? quote.items.map(i => ({
                        description: i.description,
                        quantity: Number(i.quantity),
                        unit_price: Number(i.unit_price)
                    })) : [{ description: '', quantity: 1, unit_price: 0 }],
                    notes: quote.notes || '',
                    terms_conditions: quote.terms_conditions || '',
                    tax_rate: quote.tax_total && quote.subtotal > 0 ? (Number(quote.tax_total) / Number(quote.subtotal) * 100) : 0,
                    shipment_id: quote.shipment_id ? String(quote.shipment_id) : '',
                    inventory_type: quote.inventory_type || '',
                    inventory_id: quote.inventory_id ? String(quote.inventory_id) : '',
                });
                setTaxRate(quote.tax_total && quote.subtotal > 0 ? (Number(quote.tax_total) / Number(quote.subtotal) * 100) : 0);
            } else {
                reset();
                setTaxRate(0);
            }
        }
    }, [quote, open]);

    useEffect(() => {
        if (data.inventory_id) {
            setQuoteType('inventory');
        } else if (data.shipment_id) {
            setQuoteType('shipment');
        }
        // Default to shipment if neither is set, but only if not already set ? 
        // Actually, just keep current state or default to shipment
    }, [data.inventory_id, data.shipment_id]);


    const updateItem = (index, field, value) => {
        const newItems = [...data.items];
        newItems[index] = { ...newItems[index], [field]: value };
        setData('items', newItems);
    };

    const addItem = () => {
        setData('items', [...data.items, { description: '', quantity: 1, unit_price: 0 }]);
    };

    const removeItem = (index) => {
        const newItems = data.items.filter((_, i) => i !== index);
        setData('items', newItems);
    };

    // Calculations
    const subtotal = data.items.reduce((acc, item) => acc + (Number(item.quantity) * Number(item.unit_price)), 0);
    const taxAmount = subtotal * (Number(data.tax_rate) / 100);
    const total = subtotal + taxAmount;

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            put(route('operations.quotes.update', quote.id), {
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(route('operations.quotes.store'), {
                onSuccess: () => onOpenChange(false),
            });
        }
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="overflow-y-auto bg-white border-l border-linen text-charcoal sm:max-w-2xl shadow-lg">
                <SheetHeader>
                    <SheetTitle className="text-display-sm text-void font-bold">{isEdit ? 'Edit Quote' : 'Create New Quote'}</SheetTitle>
                    <SheetDescription className="text-drift">
                        {isEdit ? 'Update quote details and line items.' : 'Draft a new quote for a client.'}
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={submit} className="space-y-6 mt-6">
                    {/* Header Info */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Client</Label>
                            <Select value={data.client_id} onValueChange={(val) => setData('client_id', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue placeholder="Select Client" />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    {clients.map(client => (
                                        <SelectItem key={client.id} value={String(client.id)} className="focus:bg-cream cursor-pointer">
                                            {client.company_name || client.contact_person}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.client_id && <span className="text-error text-sm">{errors.client_id}</span>}
                        </div>
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Status</Label>
                            <Select value={data.status} onValueChange={(val) => setData('status', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    <SelectItem value="draft" className="focus:bg-cream cursor-pointer">Draft</SelectItem>
                                    <SelectItem value="sent" className="focus:bg-cream cursor-pointer">Sent</SelectItem>
                                    <SelectItem value="approved" className="focus:bg-cream cursor-pointer">Approved</SelectItem>
                                    <SelectItem value="rejected" className="focus:bg-cream cursor-pointer">Rejected</SelectItem>
                                    <SelectItem value="expired" className="focus:bg-cream cursor-pointer">Expired</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    {/* Linking Options */}
                    <div className="space-y-4 border-b border-linen pb-6">
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Quote For</Label>
                            <RadioGroup
                                defaultValue="shipment"
                                value={quoteType}
                                onValueChange={(val) => {
                                    setQuoteType(val);
                                    if (val === 'shipment') {
                                        setData(d => ({ ...d, inventory_id: '', inventory_type: '' }));
                                    } else {
                                        setData(d => ({ ...d, shipment_id: '' }));
                                    }
                                }}
                                className="flex gap-4"
                            >
                                <div className="flex items-center space-x-2">
                                    <RadioGroupItem value="shipment" id="r-shipment" className="border-sage text-sage focus:ring-sage" />
                                    <Label htmlFor="r-shipment" className="font-normal cursor-pointer text-charcoal">Shipment</Label>
                                </div>
                                <div className="flex items-center space-x-2">
                                    <RadioGroupItem value="inventory" id="r-inventory" className="border-sage text-sage focus:ring-sage" />
                                    <Label htmlFor="r-inventory" className="font-normal cursor-pointer text-charcoal">Inventory (Vehicle)</Label>
                                </div>
                            </RadioGroup>
                        </div>

                        {quoteType === 'shipment' && (
                            <div className="space-y-2 animate-in fade-in slide-in-from-top-2">
                                <Label className="text-xs text-pebble">Link Shipment</Label>
                                <Select value={data.shipment_id} onValueChange={(val) => setData('shipment_id', val)}>
                                    <SelectTrigger className="bg-cream border-linen text-charcoal h-9 rounded-lg">
                                        <SelectValue placeholder={data.client_id ? "Select Shipment" : "Select a Client first"} />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                        {filteredShipments.length === 0 && (
                                            <div className="p-2 text-sm text-pebble">No shipments found for this client.</div>
                                        )}
                                        {filteredShipments.map(shp => (
                                            <SelectItem key={shp.id} value={String(shp.id)} className="focus:bg-cream cursor-pointer">
                                                {shp.shipment_number} - {shp.title}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        {quoteType === 'inventory' && (
                            <div className="space-y-2 animate-in fade-in slide-in-from-top-2">
                                <Label className="text-xs text-pebble">Link Inventory</Label>
                                <Select value={data.inventory_id} onValueChange={(val) => {
                                    setData(d => ({ ...d, inventory_id: val, inventory_type: 'Modules\\Inventory\\Entities\\Vehicle' }));
                                }}>
                                    <SelectTrigger className="bg-cream border-linen text-charcoal h-9 rounded-lg">
                                        <SelectValue placeholder="Select Vehicle" />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                        <SelectItem value="none" className="focus:bg-cream cursor-pointer">None</SelectItem>
                                        {vehicles && vehicles.map(v => (
                                            <SelectItem key={v.id} value={String(v.id)} className="focus:bg-cream cursor-pointer">
                                                {v.year} {v.make} {v.model}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Issue Date</Label>
                            <Input type="date" value={data.issue_date} onChange={e => setData('issue_date', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg [color-scheme:light]" />
                            {errors.issue_date && <span className="text-error text-sm">{errors.issue_date}</span>}
                        </div>
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Expiry Date</Label>
                            <Input type="date" value={data.expiry_date} onChange={e => setData('expiry_date', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg [color-scheme:light]" />
                        </div>
                    </div>

                    {/* Line Items */}
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <Label className="text-void font-semibold">Line Items</Label>
                            <Button type="button" variant="outline" size="sm" onClick={addItem} className="h-7 bg-cream border-linen hover:bg-white text-xs text-charcoal">
                                <Plus className="h-3 w-3 mr-1" /> Add Item
                            </Button>
                        </div>

                        <div className="space-y-2">
                            {data.items.map((item, index) => (
                                <div key={index} className="flex gap-2 items-start">
                                    <div className="flex-1">
                                        <Input
                                            placeholder="Description"
                                            value={item.description}
                                            onChange={e => updateItem(index, 'description', e.target.value)}
                                            className="bg-cream border-linen text-charcoal h-9 rounded-lg"
                                        />
                                    </div>
                                    <div className="w-20">
                                        <Input
                                            type="number"
                                            placeholder="Qty"
                                            value={item.quantity}
                                            onChange={e => updateItem(index, 'quantity', e.target.value)}
                                            className="bg-cream border-linen text-charcoal h-9 rounded-lg"
                                        />
                                    </div>
                                    <div className="w-24">
                                        <Input
                                            type="number"
                                            placeholder={`Price (${currency})`}
                                            value={item.unit_price}
                                            onChange={e => updateItem(index, 'unit_price', e.target.value)}
                                            className="bg-cream border-linen text-charcoal h-9 rounded-lg"
                                        />
                                    </div>
                                    <Button type="button" variant="ghost" size="icon" onClick={() => removeItem(index)} className="h-9 w-9 text-pebble hover:text-error hover:bg-error-soft rounded-lg">
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </div>
                            ))}
                            {errors.items && <span className="text-error text-sm">{errors.items}</span>}
                        </div>
                    </div>

                    {/* Totals */}
                    <div className="bg-cream border border-linen rounded-xl p-4 space-y-2">
                        <div className="flex justify-between text-sm">
                            <span className="text-pebble">Subtotal</span>
                            <span className="text-charcoal font-medium">{currency}{subtotal.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between items-center text-sm">
                            <div className="flex items-center gap-2">
                                <span className="text-pebble">Tax Rate (%)</span>
                                <Input
                                    type="number"
                                    value={data.tax_rate}
                                    onChange={e => setData('tax_rate', e.target.value)}
                                    className="w-16 h-7 text-xs bg-white border-linen rounded-md text-charcoal"
                                />
                            </div>
                            <span className="text-charcoal font-medium">{currency}{taxAmount.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between font-bold text-lg pt-2 border-t border-linen text-void">
                            <span>Total</span>
                            <span>{currency}{total.toFixed(2)}</span>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label className="text-charcoal font-medium">Notes</Label>
                        <Textarea value={data.notes} onChange={e => setData('notes', e.target.value)} className="bg-cream border-linen text-charcoal min-h-[60px] rounded-lg" />
                    </div>

                    <div className="space-y-2">
                        <Label className="text-charcoal font-medium">Terms & Conditions</Label>
                        <Textarea value={data.terms_conditions} onChange={e => setData('terms_conditions', e.target.value)} className="bg-cream border-linen text-charcoal min-h-[60px] rounded-lg" placeholder="e.g. Payment due in 30 days." />
                    </div>

                    <SheetFooter>
                        <Button type="submit" className="w-full btn-sage shadow-soft hover:shadow-glow-sage" disabled={processing}>
                            {isEdit ? 'Update Quote' : 'Create Quote'}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}
