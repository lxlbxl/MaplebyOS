import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetFooter } from "@/Components/ui/sheet";
import { Switch } from "@/Components/ui/switch";
import { Textarea } from "@/Components/ui/textarea";

export default function ClientForm({ open, onOpenChange, client = null }) {
    const isEdit = !!client;

    const { data, setData, post, put, processing, errors, reset } = useForm({
        company_name: '',
        contact_person: '',
        email: '',
        phone: '',
        type: 'buyer',
        status: true,
        address: '',
        city: '',
        country: '',
        notes: '',
    });

    useEffect(() => {
        if (client) {
            setData({
                company_name: client.company_name || '',
                contact_person: client.contact_person || '',
                email: client.email || '',
                phone: client.phone || '',
                type: client.type || 'buyer',
                status: client.is_active !== undefined ? client.is_active : true,
                address: client.address || '',
                city: client.city || '',
                country: client.country || '',
                notes: client.notes || '',
            });
        } else {
            reset();
        }
    }, [client, open]);

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            put(route('crm.clients.update', client.id), {
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(route('crm.clients.store'), {
                onSuccess: () => onOpenChange(false),
            });
        }
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="overflow-y-auto bg-white border-l border-linen text-charcoal sm:max-w-md shadow-lg">
                <SheetHeader>
                    <SheetTitle className="text-display-sm text-void font-bold">{isEdit ? 'Edit Client' : 'Add New Client'}</SheetTitle>
                    <SheetDescription className="text-drift">
                        {isEdit ? 'Update client details.' : 'Create a new client record in the database.'}
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={submit} className="space-y-6 mt-6">
                    {/* Type Selection */}
                    <div className="space-y-2">
                        <Label className="text-charcoal font-medium">Client Type</Label>
                        <RadioGroup
                            defaultValue="buyer"
                            value={data.type}
                            onValueChange={(val) => setData('type', val)}
                            className="flex space-x-4"
                        >
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="buyer" id="buyer" className="border-sage text-sage focus:ring-sage" />
                                <Label htmlFor="buyer" className="text-charcoal cursor-pointer">Buyer</Label>
                            </div>
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="supplier" id="supplier" className="border-sage text-sage focus:ring-sage" />
                                <Label htmlFor="supplier" className="text-charcoal cursor-pointer">Supplier</Label>
                            </div>
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="both" id="both" className="border-sage text-sage focus:ring-sage" />
                                <Label htmlFor="both" className="text-charcoal cursor-pointer">Both</Label>
                            </div>
                        </RadioGroup>
                        {errors.type && <span className="text-error text-sm">{errors.type}</span>}
                    </div>

                    {/* Basic Info */}
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="contact_person" className="text-charcoal font-medium">Contact Person *</Label>
                            <Input id="contact_person" value={data.contact_person} onChange={e => setData('contact_person', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            {errors.contact_person && <span className="text-error text-sm">{errors.contact_person}</span>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="company_name" className="text-charcoal font-medium">Company Name</Label>
                            <Input id="company_name" value={data.company_name} onChange={e => setData('company_name', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="email" className="text-charcoal font-medium">Email</Label>
                                <Input id="email" type="email" value={data.email} onChange={e => setData('email', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="phone" className="text-charcoal font-medium">Phone</Label>
                                <Input id="phone" value={data.phone} onChange={e => setData('phone', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                        </div>
                    </div>

                    {/* Location */}
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="address" className="text-charcoal font-medium">Address</Label>
                            <Textarea id="address" value={data.address} onChange={e => setData('address', e.target.value)} className="bg-cream border-linen text-charcoal min-h-[60px] rounded-lg" />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="city" className="text-charcoal font-medium">City</Label>
                                <Input id="city" value={data.city} onChange={e => setData('city', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="country" className="text-charcoal font-medium">Country</Label>
                                <Input id="country" value={data.country} onChange={e => setData('country', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                        </div>
                    </div>

                    {/* Meta */}
                    <div className="flex items-center justify-between border p-3 rounded-xl border-linen bg-cream/50">
                        <div className="space-y-0.5">
                            <Label className="text-charcoal font-medium">Active Status</Label>
                            <div className="text-sm text-pebble">Enable or disable this client.</div>
                        </div>
                        <Switch checked={data.status} onCheckedChange={(val) => setData('status', val)} className="data-[state=checked]:bg-sage data-[state=unchecked]:bg-linen" />
                    </div>

                    <SheetFooter>
                        <Button type="submit" className="w-full btn-sage shadow-soft hover:shadow-glow-sage" disabled={processing}>
                            {isEdit ? 'Update Client' : 'Create Client'}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}
