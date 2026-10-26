import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import axios from 'axios';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetFooter } from "@/Components/ui/sheet";
import { Textarea } from "@/Components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import SmartInput from '@/Components/Smart/SmartInput';
import VinInput from '@/Components/Smart/VinInput';
import { todayIso } from '@/lib/operations';

const EMPTY = {
    vin: '',
    vehicle_year: '',
    vehicle_make: '',
    vehicle_model: '',
    vehicle_color: '',
    client_id: '',
    status: 'added',
    date_added: '',
    destination_city: 'LAGOS',
    purchase_platform: '',
    lot_number: '',
    purchase_date: '',
    purchase_price: '',
    assigned_to: '',
    description: '',
};

async function canonical(type, q, context = {}) {
    if (!q) return null;
    try {
        const res = await axios.get(route('operations.lookups.suggest', type), { params: { q, ...context, limit: 1 } });
        const r = res.data.resolved;
        return r && ['exact', 'alias', 'typo'].includes(r.confidence) ? r.value : null;
    } catch (e) {
        return null;
    }
}

function Field({ label, error, children, className }) {
    return (
        <div className={`space-y-1.5 ${className || ''}`}>
            <Label className="text-charcoal font-medium">{label}</Label>
            {children}
            {error && <span className="text-error text-xs">{error}</span>}
        </div>
    );
}

export default function ShipmentForm({ open, onOpenChange, shipment = null, statuses = {}, users = [] }) {
    const isEdit = !!shipment;
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({ ...EMPTY, date_added: todayIso() });

    useEffect(() => {
        clearErrors();
        if (shipment) {
            setData(Object.fromEntries(Object.keys(EMPTY).map((k) => [k, shipment[k] == null ? '' : String(shipment[k]).slice(0, k.endsWith('date') || k.startsWith('date') ? 10 : undefined)])));
        } else {
            reset();
            setData((d) => ({ ...d, date_added: todayIso() }));
        }
    }, [shipment, open]);

    const onDecoded = async ({ year, make, model }) => {
        const updates = {};
        if (year && !data.vehicle_year) updates.vehicle_year = String(year);
        const canonicalMake = !data.vehicle_make && make ? await canonical('makes', make) : null;
        if (canonicalMake) updates.vehicle_make = canonicalMake;
        const theMake = canonicalMake || data.vehicle_make;
        if (model && !data.vehicle_model && theMake) {
            const canonicalModel = await canonical('models', model, { make: theMake });
            if (canonicalModel) updates.vehicle_model = canonicalModel;
        }
        if (Object.keys(updates).length) setData((d) => ({ ...d, ...updates }));
    };

    const submit = (e) => {
        e.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => onOpenChange(false) };
        isEdit ? put(route('operations.shipments.update', shipment.id), options) : post(route('operations.shipments.store'), options);
    };

    const customerLabel = shipment?.client ? (shipment.client.company_name || shipment.client.contact_person) : undefined;

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="overflow-y-auto bg-white border-l border-linen text-charcoal sm:max-w-lg shadow-lg">
                <SheetHeader>
                    <SheetTitle className="text-display-sm text-void font-bold">
                        {isEdit ? `Edit ${shipment.tracking_id}` : 'Add vehicle'}
                    </SheetTitle>
                    <SheetDescription className="text-drift">
                        {isEdit ? 'Update this car.' : 'A tracking ID (MAPYY-MM-NNN) is issued automatically.'} Make, model, platform and destination come from the catalog, so typos are corrected as you go.
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={submit} className="space-y-5 mt-6">
                    <Field label="VIN" error={null}>
                        <VinInput value={data.vin} onChange={(v) => setData('vin', v)} onDecoded={onDecoded} ignoreId={shipment?.id} error={errors.vin} />
                    </Field>

                    <div className="grid grid-cols-3 gap-3">
                        <Field label="Year" error={errors.vehicle_year}>
                            <Input type="number" value={data.vehicle_year} onChange={(e) => setData('vehicle_year', e.target.value)} className="bg-cream border-linen h-11 rounded-lg" placeholder="2015" />
                        </Field>
                        <Field label="Make" className="col-span-2">
                            <SmartInput type="makes" value={data.vehicle_make} error={errors.vehicle_make}
                                onChange={(v) => setData((d) => ({ ...d, vehicle_make: v || '', vehicle_model: v !== d.vehicle_make ? '' : d.vehicle_model }))}
                                placeholder="TOYOTA" />
                        </Field>
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                        <Field label="Model" className="col-span-2">
                            <SmartInput type="models" value={data.vehicle_model} context={data.vehicle_make ? { make: data.vehicle_make } : {}}
                                onChange={(v) => setData('vehicle_model', v || '')} error={errors.vehicle_model}
                                placeholder={data.vehicle_make ? 'Start typing…' : 'Pick a make first'} disabled={!data.vehicle_make} />
                        </Field>
                        <Field label="Colour" error={errors.vehicle_color}>
                            <Input value={data.vehicle_color} onChange={(e) => setData('vehicle_color', e.target.value)} className="bg-cream border-linen h-11 rounded-lg" />
                        </Field>
                    </div>

                    <Field label="Customer">
                        <SmartInput type="customers" value={data.client_id} displayValue={customerLabel}
                            onChange={(v) => setData('client_id', v || '')} error={errors.client_id} placeholder="Search name, company or phone" />
                    </Field>

                    <div className="grid grid-cols-2 gap-3">
                        <Field label="Date added" error={errors.date_added}>
                            <Input type="date" value={data.date_added} onChange={(e) => setData('date_added', e.target.value)} className="bg-cream border-linen h-11 rounded-lg [color-scheme:light]" />
                        </Field>
                        <Field label="Status" error={errors.status}>
                            <Select value={data.status} onValueChange={(v) => setData('status', v)}>
                                <SelectTrigger className="bg-cream border-linen h-11 rounded-lg"><SelectValue /></SelectTrigger>
                                <SelectContent className="bg-white border-linen shadow-float rounded-xl">
                                    {Object.entries(statuses).map(([value, label]) => (
                                        <SelectItem key={value} value={value} className="focus:bg-cream cursor-pointer">{label}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>
                    </div>

                    <Field label="Destination">
                        <SmartInput type="ports" value={data.destination_city} context={{ kind: 'destination' }} onChange={(v) => setData('destination_city', v || '')} error={errors.destination_city} />
                    </Field>

                    <div className="rounded-xl border border-linen p-4 space-y-4">
                        <p className="text-sm font-semibold text-void">Purchase</p>
                        <div className="grid grid-cols-2 gap-3">
                            <Field label="Platform">
                                <SmartInput type="platforms" value={data.purchase_platform} onChange={(v) => setData('purchase_platform', v || '')} error={errors.purchase_platform} placeholder="COPART, IAA…" />
                            </Field>
                            <Field label="Lot / stock #" error={errors.lot_number}>
                                <Input value={data.lot_number} onChange={(e) => setData('lot_number', e.target.value)} className="bg-cream border-linen h-11 rounded-lg" />
                            </Field>
                            <Field label="Purchase date" error={errors.purchase_date}>
                                <Input type="date" value={data.purchase_date} onChange={(e) => setData('purchase_date', e.target.value)} className="bg-cream border-linen h-11 rounded-lg [color-scheme:light]" />
                            </Field>
                            <Field label="Purchase price" error={errors.purchase_price}>
                                <Input type="number" step="0.01" value={data.purchase_price} onChange={(e) => setData('purchase_price', e.target.value)} className="bg-cream border-linen h-11 rounded-lg" />
                            </Field>
                        </div>
                    </div>

                    <Field label="Assigned to" error={errors.assigned_to}>
                        <Select value={data.assigned_to || 'none'} onValueChange={(v) => setData('assigned_to', v === 'none' ? '' : v)}>
                            <SelectTrigger className="bg-cream border-linen h-11 rounded-lg"><SelectValue placeholder="Nobody" /></SelectTrigger>
                            <SelectContent className="bg-white border-linen shadow-float rounded-xl">
                                <SelectItem value="none" className="focus:bg-cream cursor-pointer">Nobody</SelectItem>
                                {users.map((u) => <SelectItem key={u.id} value={String(u.id)} className="focus:bg-cream cursor-pointer">{u.name}</SelectItem>)}
                            </SelectContent>
                        </Select>
                    </Field>

                    <Field label="Notes" error={errors.description}>
                        <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="bg-cream border-linen min-h-[80px] rounded-lg" />
                    </Field>

                    <SheetFooter>
                        <Button type="submit" className="w-full btn-sage shadow-soft" disabled={processing}>
                            {isEdit ? 'Save changes' : 'Add vehicle'}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}
