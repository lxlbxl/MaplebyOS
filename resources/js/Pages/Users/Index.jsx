import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm, router, usePage } from '@inertiajs/react';
import { UserPermissionOverrides } from '@/Components/Permissions';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Switch } from "@/Components/ui/switch";
import Pagination from '@/Components/Pagination';
import InsightsSection from '@/Components/InsightsSection';
import FilterBar from '@/Components/FilterBar';
import { Plus, MoreVertical, Mail, Shield, UserCheck, UserX, Pencil, Trash2, Users, UserPlus, Settings2, KeyRound, ArrowRight } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";

export default function Index({ users, filters, stats, roles, permissionGroups = [], grantable = [], isSuperAdmin = false, currentUserId }) {
    const [activeTab, setActiveTab] = useState('users');
    const [editingUser, setEditingUser] = useState(null);
    const [showPerms, setShowPerms] = useState(false);
    const { auth } = usePage().props;
    const canManageRoles = !!auth?.can?.manage_roles;
    const assignableRoles = (roles || []).filter((r) => isSuperAdmin || !r.is_super_admin);
    const roleByName = Object.fromEntries((roles || []).map((r) => [r.id, r]));

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'Puncher',
        is_active: true,
        allow: [],
        deny: [],
    });

    const tabs = [
        { id: 'users', label: 'All Users', icon: Users },
        { id: 'add', label: 'Add User', icon: UserPlus },
        ...(canManageRoles ? [] : [{ id: 'roles', label: 'Roles', icon: Settings2 }]),
    ];

    const openEdit = (user) => {
        setEditingUser(user);
        setData({
            name: user.name,
            email: user.email,
            password: '',
            password_confirmation: '',
            role: user.role || 'Puncher',
            is_active: user.is_active,
            allow: user.allow || [],
            deny: user.deny || [],
        });
        setShowPerms((user.allow?.length || 0) + (user.deny?.length || 0) > 0);
        setActiveTab('add');
    };

    const cancelEdit = () => {
        setEditingUser(null);
        reset();
        setActiveTab('users');
    };

    const submit = (e) => {
        e.preventDefault();
        if (editingUser) {
            put(route('users.update', editingUser.id), {
                onSuccess: () => {
                    setEditingUser(null);
                    reset();
                    setActiveTab('users');
                },
            });
        } else {
            post(route('users.store'), {
                onSuccess: () => {
                    reset();
                    setActiveTab('users');
                },
            });
        }
    };

    const confirmDelete = (user) => {
        if (confirm(`Are you sure you want to delete ${user.name}?`)) {
            router.delete(route('users.destroy', user.id));
        }
    };

    const toggleStatus = (user) => {
        router.patch(route('users.toggle-status', user.id));
    };

    const roleColors = {
        'Super Admin': 'bg-purple-100 text-purple-700 border-purple-200',
        'Head of Business': 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'Logistics Officer': 'bg-orange-100 text-orange-700 border-orange-200',
        'Puncher': 'bg-cyan-100 text-cyan-700 border-cyan-200',
    };

    return (
        <AppLayout>
            <Head title="User Management" />

            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-serif font-bold tracking-tight text-void">User Management</h1>
                        <p className="text-drift">Manage team members, roles, and permissions.</p>
                    </div>
                </div>

                {/* Insights */}
                <InsightsSection
                    icon={Users}
                    stats={stats}
                    title="User Status"
                    description="Active vs inactive users"
                    totalLabel="Total Users"
                />

                {/* Tab Navigation */}
                <div className="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-linen shadow-sm">
                    {tabs.map((tab) => {
                        const Icon = tab.icon;
                        return (
                            <button
                                key={tab.id}
                                onClick={() => {
                                    if (tab.id !== 'add') setEditingUser(null);
                                    if (tab.id === 'add') { reset(); setShowPerms(false); }
                                    setActiveTab(tab.id);
                                }}
                                className={`flex items-center gap-2 px-6 py-3 rounded-xl font-medium transition-all ${activeTab === tab.id
                                        ? 'bg-sage text-white shadow-lg shadow-sage/20'
                                        : 'text-drift hover:text-void hover:bg-cream'
                                    }`}
                            >
                                <Icon className="w-4 h-4" />
                                {tab.label}
                            </button>
                        );
                    })}
                    {canManageRoles && (
                        <Link
                            href={route('roles.index')}
                            className="flex items-center gap-2 px-6 py-3 rounded-xl font-medium transition-all text-drift hover:text-void hover:bg-cream"
                        >
                            <Settings2 className="w-4 h-4" />
                            Roles & permissions
                        </Link>
                    )}
                </div>

                {/* Tab Content */}
                {activeTab === 'users' && (
                    <div className="space-y-6 animate-in fade-in duration-300">
                        {/* Filters */}
                        <FilterBar
                            search={filters.search}
                            status={filters.role}
                            statusOptions={[
                                { value: 'all', label: 'All Roles' },
                                ...(roles || []).map((r) => ({ value: r.id, label: r.name })),
                            ]}
                        />

                        {/* Data Table */}
                        <Card className="bg-white border-linen shadow-sm overflow-hidden">
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="border-linen hover:bg-cream/50 bg-cream/30">
                                            <TableHead className="text-drift font-medium">User</TableHead>
                                            <TableHead className="text-drift font-medium">Email</TableHead>
                                            <TableHead className="text-drift font-medium">Role</TableHead>
                                            <TableHead className="text-drift font-medium">Status</TableHead>
                                            <TableHead className="text-right text-drift font-medium">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {users.data.map((user) => (
                                            <TableRow key={user.id} className="border-linen hover:bg-cream transition-colors">
                                                <TableCell className="font-medium">
                                                    <div className="flex items-center gap-3">
                                                        <div className="w-10 h-10 bg-sage-soft rounded-full flex items-center justify-center text-void font-semibold">
                                                            {user.name.charAt(0).toUpperCase()}
                                                        </div>
                                                        <span className="text-base font-semibold text-void">{user.name}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm text-stone">
                                                        <Mail className="mr-2 h-3 w-3 text-drift" />
                                                        {user.email}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline" className={roleColors[user.role] || 'bg-gray-100 text-gray-600'}>
                                                        {user.role_label || 'No role'}
                                                    </Badge>
                                                    {(user.allow.length + user.deny.length) > 0 && (
                                                        <Badge variant="outline" className="ml-1 bg-amber-50 text-amber-700 border-amber-200" title="This person has their own permission changes">
                                                            <KeyRound className="w-3 h-3 mr-1" />{user.allow.length + user.deny.length}
                                                        </Badge>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline" className={user.is_active
                                                        ? "bg-emerald-50 text-emerald-600 border-emerald-200"
                                                        : "bg-gray-100 text-gray-500 border-gray-200"}>
                                                        {user.is_active ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    {(isSuperAdmin || !roleByName[user.role]?.is_super_admin) && (
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" className="h-8 w-8 p-0 text-drift hover:text-void">
                                                                <span className="sr-only">Open menu</span>
                                                                <MoreVertical className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end" className="bg-white border-linen text-void shadow-lg">
                                                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                            <DropdownMenuItem onClick={() => openEdit(user)} className="hover:bg-cream cursor-pointer">
                                                                <Pencil className="mr-2 h-4 w-4" /> Edit User
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem onClick={() => toggleStatus(user)} className="hover:bg-cream cursor-pointer">
                                                                {user.is_active ? (
                                                                    <><UserX className="mr-2 h-4 w-4" /> Deactivate</>
                                                                ) : (
                                                                    <><UserCheck className="mr-2 h-4 w-4" /> Activate</>
                                                                )}
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator className="bg-sage-soft" />
                                                            <DropdownMenuItem onClick={() => confirmDelete(user)} className="text-red-500 hover:bg-red-50 cursor-pointer">
                                                                <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                        {users.data.length === 0 && (
                                            <TableRow>
                                                <TableCell colSpan={5} className="h-24 text-center text-drift">
                                                    No users found.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>

                        {/* Pagination */}
                        <Pagination links={users.links} />
                    </div>
                )}

                {activeTab === 'add' && (
                    <div className="max-w-3xl mx-auto w-full animate-in fade-in duration-300">
                        <Card className="bg-white border-linen shadow-sm overflow-hidden">
                            <CardHeader className="border-b border-linen bg-cream/30">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 rounded-xl bg-sage-soft text-void">
                                        <UserPlus className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <CardTitle className="text-charcoal">{editingUser ? 'Edit User' : 'Create New User'}</CardTitle>
                                        <CardDescription className="text-drift">
                                            {editingUser ? 'Update user details and permissions.' : 'Add a new team member to the system.'}
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <form onSubmit={submit}>
                                <CardContent className="space-y-6 pt-6">
                                    <div className="grid md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label className="text-void font-medium">Full Name *</Label>
                                            <Input
                                                value={data.name}
                                                onChange={e => setData('name', e.target.value)}
                                                required
                                                className="border-linen focus:border-sage rounded-xl h-12"
                                                placeholder="John Doe"
                                            />
                                            {errors.name && <span className="text-red-500 text-sm">{errors.name}</span>}
                                        </div>

                                        <div className="space-y-2">
                                            <Label className="text-void font-medium">Email Address *</Label>
                                            <Input
                                                type="email"
                                                value={data.email}
                                                onChange={e => setData('email', e.target.value)}
                                                required
                                                className="border-linen focus:border-sage rounded-xl h-12"
                                                placeholder="john@example.com"
                                            />
                                            {errors.email && <span className="text-red-500 text-sm">{errors.email}</span>}
                                        </div>
                                    </div>

                                    <div className="grid md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label className="text-void font-medium">
                                                {editingUser ? 'New Password' : 'Password *'}
                                            </Label>
                                            <Input
                                                type="password"
                                                value={data.password}
                                                onChange={e => setData('password', e.target.value)}
                                                required={!editingUser}
                                                className="border-linen focus:border-sage rounded-xl h-12"
                                                placeholder={editingUser ? 'Leave blank to keep current' : '••••••••'}
                                            />
                                            {errors.password && <span className="text-red-500 text-sm">{errors.password}</span>}
                                        </div>

                                        <div className="space-y-2">
                                            <Label className="text-void font-medium">Confirm Password</Label>
                                            <Input
                                                type="password"
                                                value={data.password_confirmation}
                                                onChange={e => setData('password_confirmation', e.target.value)}
                                                required={!editingUser && data.password}
                                                className="border-linen focus:border-sage rounded-xl h-12"
                                                placeholder="••••••••"
                                            />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label className="text-void font-medium">Role *</Label>
                                        <Select value={data.role} onValueChange={val => setData('role', val)}>
                                            <SelectTrigger className="border-linen rounded-xl h-12">
                                                <SelectValue placeholder="Select a role" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {assignableRoles.map((r) => (
                                                    <SelectItem key={r.id} value={r.id}>{r.name}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {roleByName[data.role]?.description && (
                                            <p className="text-xs text-drift">{roleByName[data.role].description}</p>
                                        )}
                                        {errors.role && <span className="text-red-500 text-sm">{errors.role}</span>}
                                    </div>

                                    {roleByName[data.role]?.is_super_admin ? (
                                        <div className="rounded-xl border border-linen bg-cream p-4 text-sm text-stone">
                                            Super Admins have every permission, so there is nothing to adjust per person.
                                        </div>
                                    ) : editingUser && editingUser.id === currentUserId ? (
                                        <div className="rounded-xl border border-linen bg-cream p-4 text-sm text-stone">
                                            You can't change your own permissions. Ask another admin.
                                        </div>
                                    ) : (
                                        <div className="rounded-xl border border-linen">
                                            <button
                                                type="button"
                                                onClick={() => setShowPerms(!showPerms)}
                                                className="w-full flex items-center justify-between gap-3 p-4 text-left"
                                            >
                                                <span className="flex items-center gap-3">
                                                    <KeyRound className="w-5 h-5 text-void" />
                                                    <span>
                                                        <span className="block font-medium text-charcoal">Permissions for this person</span>
                                                        <span className="block text-sm text-drift">
                                                            {data.allow.length + data.deny.length === 0
                                                                ? 'Same as the role'
                                                                : `${data.allow.length} extra allowed, ${data.deny.length} denied`}
                                                        </span>
                                                    </span>
                                                </span>
                                                <ArrowRight className={`w-4 h-4 text-drift transition-transform ${showPerms ? 'rotate-90' : ''}`} />
                                            </button>
                                            {showPerms && (
                                                <div className="border-t border-linen p-4">
                                                    <UserPermissionOverrides
                                                        groups={permissionGroups}
                                                        rolePermissions={roleByName[data.role]?.permissions || []}
                                                        allow={data.allow}
                                                        deny={data.deny}
                                                        grantable={grantable}
                                                        onChange={({ allow, deny }) => setData((d) => ({ ...d, allow, deny }))}
                                                    />
                                                    {(errors.allow || errors.deny) && <span className="text-red-500 text-sm">{errors.allow || errors.deny}</span>}
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    <div className="flex items-center justify-between border p-4 rounded-xl border-linen bg-cream">
                                        <div className="space-y-0.5">
                                            <Label className="text-charcoal font-medium">Active Status</Label>
                                            <div className="text-sm text-drift">Allow this user to log in to the system.</div>
                                        </div>
                                        <Switch checked={data.is_active} onCheckedChange={val => setData('is_active', val)} />
                                    </div>
                                </CardContent>
                                <div className="bg-cream/30 border-t border-linen p-6 flex justify-between">
                                    {editingUser && (
                                        <Button type="button" variant="outline" onClick={cancelEdit} className="border-linen text-void">
                                            Cancel
                                        </Button>
                                    )}
                                    <Button type="submit" className="bg-sage hover:bg-maple-deep ml-auto" disabled={processing}>
                                        {editingUser ? 'Update User' : 'Create User'}
                                    </Button>
                                </div>
                            </form>
                        </Card>
                    </div>
                )}

                {activeTab === 'roles' && (
                    <div className="space-y-6 animate-in fade-in duration-300">
                        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {roles?.map((role) => (
                                <Card key={role.id} className="bg-white border-linen shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                                    <CardContent className="p-6">
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-center gap-3">
                                                <div className={`p-2 rounded-xl ${roleColors[role.id] || 'bg-gray-100'}`}>
                                                    <Shield className="w-5 h-5" />
                                                </div>
                                                <div>
                                                    <h3 className="font-semibold text-charcoal">{role.name}</h3>
                                                    <p className="text-sm text-drift">{role.description}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="mt-4 pt-4 border-t border-linen flex items-center justify-between">
                                            <div className="text-sm text-stone">
                                                <span className="font-semibold text-charcoal">{role.users_count}</span> users
                                            </div>
                                            <Badge variant="outline" className={roleColors[role.id] || 'bg-gray-100'}>
                                                {role.users_count === 1 ? 'person' : 'people'}
                                            </Badge>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        <Card className="bg-cream border-linen">
                            <CardContent className="p-6">
                                <div className="flex items-center gap-3 text-void">
                                    <Shield className="w-5 h-5" />
                                    <div>
                                        <h3 className="font-semibold">Role Permissions</h3>
                                        <p className="text-sm text-stone">
                                            Each person has one role. The CEO / Super Admin sees everything. The other roles only see the screens and data their work needs, and the server enforces it, not just the menus.
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
