import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetFooter } from "@/Components/ui/sheet";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import SmartInput from '@/Components/Smart/SmartInput';
import { containerNumberCheck, guessLine, todayIso } from '@/lib/operations';
import { AlertTriangle, CheckCircle2, Sparkles } from 'lucide-react';

const FIELDS = ['container_number', 'shipping_line', 'size_type', 'seal_number', 'booking_number', 'bl_number', 'vessel', 'voyage',
    'port_of_loading', 'port_of_discharge', 'capacity', 'loaded_on', 'etd', 'eta', 'freight_cost', 'freight_currency', 'notes'];

function Field({ label, error, children, className }) {
    return (
        <div className={`space-y-1.5 ${className || ''}`}>
            <Label className="text-charcoal font-medium">{label}</Label>
            {children}
            {error && <span className="text-error text-xs">{error}</span>}
        </div>
    );
}

export default function ContainerForm({ open, onOpenChange, container = null, shippingLines = [], shipmentIds = [] }) {
    const isEdit = !!container;
    const { data, setData, post, put, processing, errors, reset, clearErrors, transform } = useForm({
        container_number: '', shipping_line: '', size_type: '40HC', seal_number: '', booking_number: '', bl_number: '', vessel: '', voyage: '',
        port_of_loading: '', port_of_discharge: 'LAGOS', capacity: 4, loaded_on: todayIso(), etd: '', eta: '', freight_cost: '', freight_currency: 'USD', notes: '',
    });

    useEffect(() => {
        clearErrors();
        if (container) {
            setData(Object.fromEntries(FIELDS.map((k) => {
                if (k === 'shipping_line') return [k, container.shipping_line?.name || ''];
                const v = container[k];
                return [k, v == null ? '' : String(v).slice(0, /_on$|^et[ad]$/.test(k) ? 10 : undefined)];
            })));
        } else {
            reset();
        }
    }, [container, open]);

    const number = containerNumberCheck(data.container_number);
    const guessed = guessLine(data.container_number, shippingLines);

    useEffect(() => {
        if (!isEdit && guessed && !data.shipping_line) setData('shipping_line', guessed.name);
    }, [guessed?.id]);

    const submit = (e) => {
        e.preventDefault();
        transform((d) => ({ ...d, shipment_ids: shipmentIds }));
        const options = { preserveScroll: true, onSuccess: () => onOpenChange(false) };
        isEdit ? put(route('operations.containers.update', container.id), options) : post(route('operations.containers.store'), options);
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="overflow-y-auto bg-white border-l border-linen sm:max-w-lg">
                <SheetHeader>
                    <SheetTitle className="text-display-sm text-void font-bold">{isEdit ? `Edit ${container.container_number}` : 'New container'}</SheetTitle>
                    <SheetDescription className="text-drift">
                        {shipmentIds.length > 0 ? `${shipmentIds.length} selected vehicle(s) will be loaded into it.` : 'Add cars after creating it.'}
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={submit} className="space-y-4 mt-6">
                    <Field label="Container number" error={errors.container_number}>
                        <Input value={data.container_number} onChange={(e) => setData('container_number', e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 11))}
                            placeholder="MSNU7239445" className="bg-cream border-linen h-11 rounded-lg font-mono tracking-wider" />
                        {number.clean.length === 11 && (number.valid
                            ? <p className="text-xs text-sage flex items-center gap-1"><CheckCircle2 className="h-3 w-3" /> Check digit OK</p>
                            : <p className="text-xs text-amber-700 flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> {number.wellFormed ? 'Check digit does not match. Re-read the number on the box.' : 'Should be 4 letters + 7 digits.'}</p>)}
                        {guessed && <p className="text-xs text-pebble flex items-center gap-1"><Sparkles className="h-3 w-3" /> Prefix {number.clean.slice(0, 4)} belongs to {guessed.name}</p>}
                    </Field>

                    <div className="grid grid-cols-2 gap-3">
                        <Field label="Shipping line"><SmartInput type="shipping_lines" value={data.shipping_line} onChange={(v) => setData('shipping_line', v || '')} error={errors.shipping_line} /></Field>
                        <Field label="Size" error={errors.size_type}>
                            <Select value={data.size_type || 'none'} onValueChange={(v) => setData('size_type', v === 'none' ? '' : v)}>
                                <SelectTrigger className="bg-cream border-linen h-11"><SelectValue /></SelectTrigger>
                                <SelectContent className="bg-white">{['none', '20GP', '40GP', '40HC', '45HC'].map((s) => <SelectItem key={s} value={s}>{s === 'none' ? '—' : s}</SelectItem>)}</SelectContent>
                            </Select>
                        </Field>
                        <Field label="Booking #" error={errors.booking_number}><Input value={data.booking_number} onChange={(e) => setData('booking_number', e.target.value)} className="bg-cream border-linen h-11" /></Field>
                        <Field label="Bill of lading #" error={errors.bl_number}><Input value={data.bl_number} onChange={(e) => setData('bl_number', e.target.value)} className="bg-cream border-linen h-11" /></Field>
                        <Field label="Seal #" error={errors.seal_number}><Input value={data.seal_number} onChange={(e) => setData('seal_number', e.target.value)} className="bg-cream border-linen h-11" /></Field>
                        <Field label="Capacity (cars)" error={errors.capacity}><Input type="number" min="1" max="12" value={data.capacity} onChange={(e) => setData('capacity', e.target.value)} className="bg-cream border-linen h-11" /></Field>
                        <Field label="Vessel" error={errors.vessel}><Input value={data.vessel} onChange={(e) => setData('vessel', e.target.value)} className="bg-cream border-linen h-11" /></Field>
                        <Field label="Voyage" error={errors.voyage}><Input value={data.voyage} onChange={(e) => setData('voyage', e.target.value)} className="bg-cream border-linen h-11" /></Field>
                        <Field label="Port of loading"><SmartInput type="ports" context={{ kind: 'origin' }} value={data.port_of_loading} onChange={(v) => setData('port_of_loading', v || '')} error={errors.port_of_loading} /></Field>
                        <Field label="Port of discharge"><SmartInput type="ports" context={{ kind: 'destination' }} value={data.port_of_discharge} onChange={(v) => setData('port_of_discharge', v || '')} error={errors.port_of_discharge} /></Field>
                        <Field label="Loaded on" error={errors.loaded_on}><Input type="date" value={data.loaded_on} onChange={(e) => setData('loaded_on', e.target.value)} className="bg-cream border-linen h-11 [color-scheme:light]" /></Field>
                        <Field label="ETD" error={errors.etd}><Input type="date" value={data.etd} onChange={(e) => setData('etd', e.target.value)} className="bg-cream border-linen h-11 [color-scheme:light]" /></Field>
                        <Field label="ETA Lagos" error={errors.eta}><Input type="date" value={data.eta} onChange={(e) => setData('eta', e.target.value)} className="bg-cream border-linen h-11 [color-scheme:light]" /></Field>
                        <Field label="Freight cost" error={errors.freight_cost}><Input type="number" step="0.01" value={data.freight_cost} onChange={(e) => setData('freight_cost', e.target.value)} className="bg-cream border-linen h-11" /></Field>
                    </div>
                    <Field label="Notes" error={errors.notes}><Textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="bg-cream border-linen" /></Field>
                    {errors.shipment_ids && <p className="text-sm text-red-600">{errors.shipment_ids}</p>}
                    <SheetFooter><Button type="submit" disabled={processing} className="w-full btn-sage">{isEdit ? 'Save container' : 'Create container'}</Button></SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}
