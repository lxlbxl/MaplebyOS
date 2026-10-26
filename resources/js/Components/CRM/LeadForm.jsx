import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
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

export default function LeadForm({ open, onOpenChange, lead = null, clients = [], users = [] }) {
    const isEdit = !!lead;

    const { data, setData, post, put, processing, errors, reset } = useForm({
        title: '',
        client_id: '',
        contact_name: '',
        contact_email: '',
        status: 'new',
        priority: 'medium',
        estimated_value: '',
        description: '',
        source: 'direct',
    });

    useEffect(() => {
        if (lead) {
            setData({
                title: lead.title || '',
                client_id: lead.client_id ? String(lead.client_id) : '',
                contact_name: lead.contact_name || '',
                contact_email: lead.contact_email || '',
                status: lead.status || 'new',
                priority: lead.priority || 'medium',
                estimated_value: lead.estimated_value || '',
                description: lead.description || '',
                source: lead.source || 'direct',
            });
        } else {
            reset();
        }
    }, [lead, open]);

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            put(route('crm.leads.update', lead.id), {
                onSuccess: () => onOpenChange(false),
            });
        } else {
            post(route('crm.leads.store'), {
                onSuccess: () => onOpenChange(false),
            });
        }
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="overflow-y-auto bg-white border-l border-linen text-charcoal sm:max-w-md shadow-lg">
                <SheetHeader>
                    <SheetTitle className="text-display-sm text-void font-bold">{isEdit ? 'Edit Lead' : 'Create New Lead'}</SheetTitle>
                    <SheetDescription className="text-drift">
                        {isEdit ? 'Update lead details.' : 'Add a new potential opportunity to your pipeline.'}
                    </SheetDescription>
                </SheetHeader>

                <form onSubmit={submit} className="space-y-6 mt-6">
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="title" className="text-charcoal font-medium">Lead Title</Label>
                            <Input id="title" value={data.title} onChange={e => setData('title', e.target.value)} required className="bg-cream border-linen text-charcoal h-11 rounded-lg" placeholder="e.g. Website Redesign" />
                            {errors.title && <span className="text-error text-sm">{errors.title}</span>}
                        </div>

                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Client (Optional)</Label>
                            <Select value={data.client_id} onValueChange={(val) => setData('client_id', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue placeholder="Select a client" />
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

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="estimated_value" className="text-charcoal font-medium">Value ($)</Label>
                                <Input id="estimated_value" type="number" value={data.estimated_value} onChange={e => setData('estimated_value', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                            </div>
                            <div className="space-y-2">
                                <Label className="text-charcoal font-medium">Status</Label>
                                <Select value={data.status} onValueChange={(val) => setData('status', val)}>
                                    <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                        <SelectItem value="new" className="focus:bg-cream cursor-pointer">New</SelectItem>
                                        <SelectItem value="contacted" className="focus:bg-cream cursor-pointer">Contacted</SelectItem>
                                        <SelectItem value="qualified" className="focus:bg-cream cursor-pointer">Qualified</SelectItem>
                                        <SelectItem value="proposal" className="focus:bg-cream cursor-pointer">Proposal</SelectItem>
                                        <SelectItem value="negotiation" className="focus:bg-cream cursor-pointer">Negotiation</SelectItem>
                                        <SelectItem value="won" className="focus:bg-cream cursor-pointer">Won</SelectItem>
                                        <SelectItem value="lost" className="focus:bg-cream cursor-pointer">Lost</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-charcoal font-medium">Priority</Label>
                            <Select value={data.priority} onValueChange={(val) => setData('priority', val)}>
                                <SelectTrigger className="bg-cream border-linen text-charcoal h-11 rounded-lg">
                                    <SelectValue placeholder="Priority" />
                                </SelectTrigger>
                                <SelectContent className="bg-white border-linen text-charcoal shadow-float rounded-xl">
                                    <SelectItem value="low" className="focus:bg-cream cursor-pointer">Low</SelectItem>
                                    <SelectItem value="medium" className="focus:bg-cream cursor-pointer">Medium</SelectItem>
                                    <SelectItem value="high" className="focus:bg-cream cursor-pointer">High</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label className="text-charcoal font-medium">Contact Details (If no Client)</Label>
                        <Input placeholder="Contact Name" value={data.contact_name} onChange={e => setData('contact_name', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg mb-2" />
                        <Input placeholder="Contact Email" type="email" value={data.contact_email} onChange={e => setData('contact_email', e.target.value)} className="bg-cream border-linen text-charcoal h-11 rounded-lg" />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description" className="text-charcoal font-medium">Description</Label>
                        <Textarea id="description" value={data.description} onChange={e => setData('description', e.target.value)} className="bg-cream border-linen text-charcoal min-h-[60px] rounded-lg" />
                    </div>

                    <SheetFooter>
                        <Button type="submit" className="w-full btn-sage shadow-soft hover:shadow-glow-sage" disabled={processing}>
                            {isEdit ? 'Update Lead' : 'Create Lead'}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    );
}
