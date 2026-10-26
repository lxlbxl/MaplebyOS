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

export default function VehicleForm({ open, onOpenChange, vehicle = null, clients = [] }) {
    const isEdit = !!vehicle;
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';

    const { data, setData, post, put, processing, errors, reset } = useForm({
        year: '',
        make: '',
        model: '',
        vin: '',
        color: '',
        condition: 'used',
        status: 'in_stock',
        purchase_price: '',
        sale_price: '',
        supplier_id: '',
        buyer_id: '',
        notes: '',
    });

    useEffect(() => {
        if (vehicle) {
            setData({
                year: vehicle.year || '',
                make: vehicle.make || '',
                model: vehicle.model || '',
                vin: vehicle.vin || '',
                color: vehicle.color || '',
                condition: vehicle.condition || 'used',
                status: vehicle.status || 'in_stock',
                purchase_price: vehicle.purchase_price || '',
                sale_price: vehicle.sale_price || '',
                supplier_id: vehicle.supplier_id ? String(vehicle.supplier_id) : '',
                buyer_id: vehicle.buyer_id ? String(vehicle.buyer_id) : '',
                notes: vehicle.notes || '',
            });
        } else {
            reset();
        }
    }, [vehicle, open]);

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            put(route('inventory.vehicles.update', vehicle.id), {
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(route('inventory.vehicles.store'), {
                onSuccess: () => onOpenChange(false),
            });
        }
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="overflow-y-auto bg-white border-l border-linen text-charcoal sm:max-w-md shadow-lg">
                <SheetHeader>
                    <SheetTitle className="text-display-sm text-void font-bold">{isEdit ? 'Edit Vehicle' : 'Add Vehicle'}</SheetTitle>
                    <SheetDescription className="text-drift">
                        {isEdit ? 'Update vehicle inventory details.' : 'Add a new vehicle to inventory.'}
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={submit} className="space-y-6 mt-6">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">VIN *</Label>
                            <Input value={data.vin} onChange={e => setData('vin', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg font-mono text-sm uppercase" />
                            {errors.vin && <span className="text-error text-sm">{errors.vin}</span>}
                        </div>
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Year *</Label>
                            <Input type="number" value={data.year} onChange={e => setData('year', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Make *</Label>
                            <Input value={data.make} onChange={e => setData('make', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                        </div>
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Model *</Label>
                            <Input value={data.model} onChange={e => setData('model', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                        </div>
                    </div>

                    <div className="grid grid-cols-3 gap-4">
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Color</Label>
                            <Input value={data.color} onChange={e => setData('color', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                        </div>
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Condition</Label>
                            <Select value={data.condition} onValueChange={(val) => setData('condition', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    <SelectItem value="new" className="focus:bg-cream cursor-pointer">New</SelectItem>
                                    <SelectItem value="used" className="focus:bg-cream cursor-pointer">Used</SelectItem>
                                    <SelectItem value="salvage" className="focus:bg-cream cursor-pointer">Salvage</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Status</Label>
                            <Select value={data.status} onValueChange={(val) => setData('status', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    <SelectItem value="in_stock" className="focus:bg-cream cursor-pointer">In Stock</SelectItem>
                                    <SelectItem value="sold" className="focus:bg-cream cursor-pointer">Sold</SelectItem>
                                    <SelectItem value="shipped" className="focus:bg-cream cursor-pointer">Shipped</SelectItem>
                                    <SelectItem value="reserved" className="focus:bg-cream cursor-pointer">Reserved</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="space-y-4 pt-4 border-t border-linen">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Purchase Price ({currency})</Label>
                                <Input type="number" value={data.purchase_price} onChange={e => setData('purchase_price', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Sale Price ({currency})</Label>
                                <Input type="number" value={data.sale_price} onChange={e => setData('sale_price', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Supplier (Source)</Label>
                            <Select value={data.supplier_id} onValueChange={(val) => setData('supplier_id', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue placeholder="Select Supplier" />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    {clients.map(client => (
                                        <SelectItem key={client.id} value={String(client.id)} className="focus:bg-cream cursor-pointer">
                                            {client.company_name || client.contact_person}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Buyer (If Sold)</Label>
                            <Select value={data.buyer_id} onValueChange={(val) => setData('buyer_id', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue placeholder="Select Buyer" />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    {clients.map(client => (
                                        <SelectItem key={client.id} value={String(client.id)} className="focus:bg-cream cursor-pointer">
                                            {client.company_name || client.contact_person}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label className="text-charcoal font-medium">Notes</Label>
                        <Textarea value={data.notes} onChange={e => setData('notes', e.target.value)} className="bg-cream border-linen text-charcoal min-h-[60px] rounded-lg" />
                    </div>

                    <SheetFooter>
                        <Button type="submit" className="w-full btn-sage shadow-soft hover:shadow-glow-sage" disabled={processing}>
                            {isEdit ? 'Update Vehicle' : 'Add Vehicle'}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}
