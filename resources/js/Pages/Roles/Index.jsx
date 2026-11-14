import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { RolePermissionPicker } from '@/Components/Permissions';
import { ArrowLeft, Plus, Shield, ShieldCheck, Pencil, Trash2, Users, Copy } from 'lucide-react';

export default function Index({ roles, permissionGroups, homes, grantable, isSuperAdmin }) {
    const [editing, setEditing] = useState(null); // null = list, 'new' or role object
    const totalPerms = permissionGroups.reduce((n, g) => n + g.permissions.length, 0);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        label: '',
        description: '',
        home: 'puncher',
        permissions: [],
    });

    const openNew = (from = null) => {
        clearErrors();
        setData({
            label: from ? `${from.label} (copy)` : '',
            description: from?.description || '',
            home: from && !from.is_super_admin ? from.home : 'puncher',
            permissions: from ? from.permissions.filter((p) => grantable.includes(p)) : [],
        });
        setEditing('new');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const openEdit = (role) => {
        clearErrors();
        setData({ label: role.label, description: role.description || '', home: role.home, permissions: role.permissions });
        setEditing(role);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const close = () => {
        setEditing(null);
        reset();
    };

    const submit = (e) => {
        e.preventDefault();
        const opts = { preserveScroll: true, onSuccess: close };
        editing === 'new' ? post(route('roles.store'), opts) : put(route('roles.update', editing.id), opts);
    };

    const destroy = (role) => {
        if (confirm(`Delete the role "${role.label}"? This cannot be undone.`)) {
            router.delete(route('roles.destroy', role.id), { preserveScroll: true });
        }
    };

    const isCeo = editing && editing !== 'new' && editing.is_super_admin;
    const canEditRole = (role) => isSuperAdmin || !role.is_super_admin;

    return (
        <AppLayout>
            <Head title="Roles & permissions" />

            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-6">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <Link href={route('users.index')} className="inline-flex items-center gap-1 text-sm text-drift hover:text-void mb-1">
                            <ArrowLeft className="w-4 h-4" /> Staff
                        </Link>
                        <h1 className="text-3xl font-serif font-bold tracking-tight text-void">Roles & permissions</h1>
                        <p className="text-drift">Decide what each role can see and do. Changes apply to everyone with the role straight away.</p>
                    </div>
                    {!editing && (
                        <Button onClick={() => openNew()} className="bg-sage hover:bg-maple-deep self-start md:self-auto">
                            <Plus className="w-4 h-4 mr-2" /> New role
                        </Button>
                    )}
                </div>

                {editing ? (
                    <Card className="bg-white border-linen shadow-sm overflow-hidden">
                        <CardHeader className="border-b border-linen bg-cream/30">
                            <div className="flex items-center gap-3">
                                <div className="p-2 rounded-xl bg-sage-soft text-void">
                                    <Shield className="w-5 h-5" />
                                </div>
                                <div>
                                    <CardTitle className="text-charcoal">{editing === 'new' ? 'New role' : `Edit ${editing.label}`}</CardTitle>
                                    <CardDescription className="text-drift">
                                        {isCeo
                                            ? 'The Super Admin role always has every permission. You can change its name and description.'
                                            : 'Name the role, pick its home screen and tick what it may do.'}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <form onSubmit={submit}>
                            <CardContent className="space-y-6 pt-6">
                                <div className="grid md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label className="text-void font-medium">Role name *</Label>
                                        <Input
                                            value={data.label}
                                            onChange={(e) => setData('label', e.target.value)}
                                            required
                                            maxLength={60}
                                            className="border-linen focus:border-sage rounded-xl h-12"
                                            placeholder="e.g. Accountant"
                                        />
                                        {errors.label && <span className="text-red-500 text-sm">{errors.label}</span>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label className="text-void font-medium">Home screen</Label>
                                        <Select value={data.home} onValueChange={(v) => setData('home', v)} disabled={isCeo}>
                                            <SelectTrigger className="border-linen rounded-xl h-12">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {homes.map((h) => (
                                                    <SelectItem key={h.value} value={h.value}>{h.label}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <p className="text-xs text-drift">Money views only show if the role can see costs and profit.</p>
                                        {errors.home && <span className="text-red-500 text-sm">{errors.home}</span>}
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label className="text-void font-medium">Description</Label>
                                    <Textarea
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        maxLength={500}
                                        rows={2}
                                        className="border-linen focus:border-sage rounded-xl"
                                        placeholder="What this person does"
                                    />
                                </div>

                                <div className="space-y-3">
                                    <div className="flex items-center justify-between">
                                        <Label className="text-void font-medium">Permissions</Label>
                                        <span className="text-sm text-drift">{data.permissions.length} of {totalPerms}</span>
                                    </div>
                                    <RolePermissionPicker
                                        groups={permissionGroups}
                                        value={data.permissions}
                                        onChange={(v) => setData('permissions', v)}
                                        grantable={grantable}
                                        locked={isCeo}
                                    />
                                    {errors.permissions && <span className="text-red-500 text-sm">{errors.permissions}</span>}
                                </div>
                            </CardContent>
                            <div className="sticky bottom-0 bg-white/95 backdrop-blur border-t border-linen p-4 sm:p-6 flex justify-between gap-3">
                                <Button type="button" variant="outline" onClick={close} className="border-linen text-void">Cancel</Button>
                                <Button type="submit" className="bg-sage hover:bg-maple-deep" disabled={processing}>
                                    {editing === 'new' ? 'Create role' : 'Save role'}
                                </Button>
                            </div>
                        </form>
                    </Card>
                ) : (
                    <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                        {roles.map((role) => (
                            <Card key={role.id} className="bg-white border-linen shadow-sm overflow-hidden flex flex-col">
                                <CardContent className="p-6 flex flex-col gap-4 flex-1">
                                    <div className="flex items-start gap-3">
                                        <div className={`p-2 rounded-xl ${role.is_super_admin ? 'bg-purple-100 text-purple-700' : 'bg-sage-soft text-void'}`}>
                                            {role.is_super_admin ? <ShieldCheck className="w-5 h-5" /> : <Shield className="w-5 h-5" />}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <h3 className="font-semibold text-charcoal">{role.label}</h3>
                                                {role.is_system && <Badge variant="outline" className="text-[10px] uppercase tracking-wide">Built-in</Badge>}
                                            </div>
                                            {role.description && <p className="text-sm text-drift mt-1">{role.description}</p>}
                                        </div>
                                    </div>
                                    <div className="flex flex-wrap gap-x-4 gap-y-1 text-sm text-stone mt-auto">
                                        <span className="inline-flex items-center gap-1">
                                            <Users className="w-3.5 h-3.5" />
                                            <b className="text-charcoal">{role.users_count}</b> {role.users_count === 1 ? 'person' : 'people'}
                                        </span>
                                        <span>
                                            <b className="text-charcoal">{role.is_super_admin ? 'All' : role.permissions.length}</b> permissions
                                        </span>
                                    </div>
                                    <div className="flex flex-wrap gap-2 pt-4 border-t border-linen">
                                        {canEditRole(role) && (
                                            <Button size="sm" variant="outline" className="border-linen" onClick={() => openEdit(role)}>
                                                <Pencil className="w-3.5 h-3.5 mr-1.5" /> Edit
                                            </Button>
                                        )}
                                        {!role.is_super_admin && (
                                            <Button size="sm" variant="outline" className="border-linen" onClick={() => openNew(role)}>
                                                <Copy className="w-3.5 h-3.5 mr-1.5" /> Duplicate
                                            </Button>
                                        )}
                                        {!role.is_system && (
                                            <Button size="sm" variant="outline" className="border-linen text-red-600 hover:bg-red-50 ml-auto" onClick={() => destroy(role)}>
                                                <Trash2 className="w-3.5 h-3.5 mr-1.5" /> Delete
                                            </Button>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
