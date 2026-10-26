import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, useForm, router } from '@inertiajs/react';
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
import { Plus, MoreVertical, Mail, Shield, UserCheck, UserX, Pencil, Trash2, Users, UserPlus, Settings2 } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";

export default function Index({ users, filters, stats, roles }) {
    const [activeTab, setActiveTab] = useState('users');
    const [editingUser, setEditingUser] = useState(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'sales',
        is_active: true,
    });

    const tabs = [
        { id: 'users', label: 'All Users', icon: Users },
        { id: 'add', label: 'Add User', icon: UserPlus },
        { id: 'roles', label: 'Manage Roles', icon: Settings2 },
    ];

    const openEdit = (user) => {
        setEditingUser(user);
        setData({
            name: user.name,
            email: user.email,
            password: '',
            password_confirmation: '',
            role: user.role || 'sales',
            is_active: user.is_active,
        });
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
        super_admin: 'bg-purple-100 text-purple-700 border-purple-200',
        admin: 'bg-blue-100 text-blue-700 border-blue-200',
        sales: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        operations: 'bg-orange-100 text-orange-700 border-orange-200',
        finance: 'bg-cyan-100 text-cyan-700 border-cyan-200',
    };

    return (
        <AppLayout>
            <Head title="User Management" />

            <div className="flex flex-col gap-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-serif font-bold tracking-tight text-sage-600">User Management</h1>
                        <p className="text-sage-400">Manage team members, roles, and permissions.</p>
                    </div>
                </div>

                {/* Insights */}
                <InsightsSection
                    stats={stats}
                    title="User Status"
                    description="Active vs inactive users"
                    totalLabel="Total Users"
                />

                {/* Tab Navigation */}
                <div className="flex gap-2 bg-white p-2 rounded-2xl border border-sage-100 shadow-sm">
                    {tabs.map((tab) => {
                        const Icon = tab.icon;
                        return (
                            <button
                                key={tab.id}
                                onClick={() => {
                                    if (tab.id !== 'add') setEditingUser(null);
                                    if (tab.id === 'add') reset();
                                    setActiveTab(tab.id);
                                }}
                                className={`flex items-center gap-2 px-6 py-3 rounded-xl font-medium transition-all ${activeTab === tab.id
                                        ? 'bg-sage-600 text-white shadow-lg shadow-sage-600/20'
                                        : 'text-sage-400 hover:text-sage-600 hover:bg-sage-50'
                                    }`}
                            >
                                <Icon className="w-4 h-4" />
                                {tab.label}
                            </button>
                        );
                    })}
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
                                { value: 'admin', label: 'Admin' },
                                { value: 'sales', label: 'Sales' },
                                { value: 'operations', label: 'Operations' },
                                { value: 'finance', label: 'Finance' },
                            ]}
                        />

                        {/* Data Table */}
                        <Card className="bg-white border-sage-100 shadow-sm overflow-hidden">
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="border-sage-100 hover:bg-sage-50/50 bg-sage-50/30">
                                            <TableHead className="text-sage-400 font-medium">User</TableHead>
                                            <TableHead className="text-sage-400 font-medium">Email</TableHead>
                                            <TableHead className="text-sage-400 font-medium">Role</TableHead>
                                            <TableHead className="text-sage-400 font-medium">Status</TableHead>
                                            <TableHead className="text-right text-sage-400 font-medium">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {users.data.map((user) => (
                                            <TableRow key={user.id} className="border-sage-100 hover:bg-sage-50 transition-colors">
                                                <TableCell className="font-medium">
                                                    <div className="flex items-center gap-3">
                                                        <div className="w-10 h-10 bg-sage-100 rounded-full flex items-center justify-center text-sage-600 font-semibold">
                                                            {user.name.charAt(0).toUpperCase()}
                                                        </div>
                                                        <span className="text-base font-semibold text-sage-600">{user.name}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center text-sm text-sage-500">
                                                        <Mail className="mr-2 h-3 w-3 text-sage-400" />
                                                        {user.email}
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline" className={`${roleColors[user.role] || 'bg-gray-100 text-gray-600'} capitalize`}>
                                                        {user.role?.replace('_', ' ') || 'User'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline" className={user.is_active
                                                        ? "bg-emerald-50 text-emerald-600 border-emerald-200"
                                                        : "bg-gray-100 text-gray-500 border-gray-200"}>
                                                        {user.is_active ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" className="h-8 w-8 p-0 text-sage-400 hover:text-sage-600">
                                                                <span className="sr-only">Open menu</span>
                                                                <MoreVertical className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end" className="bg-white border-sage-100 text-sage-600 shadow-lg">
                                                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                            <DropdownMenuItem onClick={() => openEdit(user)} className="hover:bg-sage-50 cursor-pointer">
                                                                <Pencil className="mr-2 h-4 w-4" /> Edit User
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem onClick={() => toggleStatus(user)} className="hover:bg-sage-50 cursor-pointer">
                                                                {user.is_active ? (
                                                                    <><UserX className="mr-2 h-4 w-4" /> Deactivate</>
                                                                ) : (
                                                                    <><UserCheck className="mr-2 h-4 w-4" /> Activate</>
                                                                )}
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator className="bg-sage-100" />
                                                            <DropdownMenuItem onClick={() => confirmDelete(user)} className="text-red-500 hover:bg-red-50 cursor-pointer">
                                                                <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                        {users.data.length === 0 && (
                                            <TableRow>
                                                <TableCell colSpan={5} className="h-24 text-center text-sage-400">
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
                    <div className="max-w-2xl mx-auto w-full animate-in fade-in duration-300">
                        <Card className="bg-white border-sage-100 shadow-sm overflow-hidden">
                            <CardHeader className="border-b border-sage-100 bg-sage-50/30">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 rounded-xl bg-sage-100 text-sage-600">
                                        <UserPlus className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <CardTitle className="text-sage-700">{editingUser ? 'Edit User' : 'Create New User'}</CardTitle>
                                        <CardDescription className="text-sage-400">
                                            {editingUser ? 'Update user details and permissions.' : 'Add a new team member to the system.'}
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <form onSubmit={submit}>
                                <CardContent className="space-y-6 pt-6">
                                    <div className="grid md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label className="text-sage-600 font-medium">Full Name *</Label>
                                            <Input
                                                value={data.name}
                                                onChange={e => setData('name', e.target.value)}
                                                required
                                                className="border-sage-200 focus:border-sage-400 rounded-xl h-12"
                                                placeholder="John Doe"
                                            />
                                            {errors.name && <span className="text-red-500 text-sm">{errors.name}</span>}
                                        </div>

                                        <div className="space-y-2">
                                            <Label className="text-sage-600 font-medium">Email Address *</Label>
                                            <Input
                                                type="email"
                                                value={data.email}
                                                onChange={e => setData('email', e.target.value)}
                                                required
                                                className="border-sage-200 focus:border-sage-400 rounded-xl h-12"
                                                placeholder="john@example.com"
                                            />
                                            {errors.email && <span className="text-red-500 text-sm">{errors.email}</span>}
                                        </div>
                                    </div>

                                    <div className="grid md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label className="text-sage-600 font-medium">
                                                {editingUser ? 'New Password' : 'Password *'}
                                            </Label>
                                            <Input
                                                type="password"
                                                value={data.password}
                                                onChange={e => setData('password', e.target.value)}
                                                required={!editingUser}
                                                className="border-sage-200 focus:border-sage-400 rounded-xl h-12"
                                                placeholder={editingUser ? 'Leave blank to keep current' : '••••••••'}
                                            />
                                            {errors.password && <span className="text-red-500 text-sm">{errors.password}</span>}
                                        </div>

                                        <div className="space-y-2">
                                            <Label className="text-sage-600 font-medium">Confirm Password</Label>
                                            <Input
                                                type="password"
                                                value={data.password_confirmation}
                                                onChange={e => setData('password_confirmation', e.target.value)}
                                                required={!editingUser && data.password}
                                                className="border-sage-200 focus:border-sage-400 rounded-xl h-12"
                                                placeholder="••••••••"
                                            />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label className="text-sage-600 font-medium">Role *</Label>
                                        <Select value={data.role} onValueChange={val => setData('role', val)}>
                                            <SelectTrigger className="border-sage-200 rounded-xl h-12">
                                                <SelectValue placeholder="Select a role" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="admin">Admin - Full administrative access</SelectItem>
                                                <SelectItem value="sales">Sales - Sales and CRM access</SelectItem>
                                                <SelectItem value="operations">Operations - Shipping and logistics</SelectItem>
                                                <SelectItem value="finance">Finance - Invoicing and payments</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.role && <span className="text-red-500 text-sm">{errors.role}</span>}
                                    </div>

                                    <div className="flex items-center justify-between border p-4 rounded-xl border-sage-200 bg-sage-50">
                                        <div className="space-y-0.5">
                                            <Label className="text-sage-700 font-medium">Active Status</Label>
                                            <div className="text-sm text-sage-400">Allow this user to log in to the system.</div>
                                        </div>
                                        <Switch checked={data.is_active} onCheckedChange={val => setData('is_active', val)} />
                                    </div>
                                </CardContent>
                                <div className="bg-sage-50/30 border-t border-sage-100 p-6 flex justify-between">
                                    {editingUser && (
                                        <Button type="button" variant="outline" onClick={cancelEdit} className="border-sage-200 text-sage-600">
                                            Cancel
                                        </Button>
                                    )}
                                    <Button type="submit" className="bg-sage-600 hover:bg-sage-700 ml-auto" disabled={processing}>
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
                                <Card key={role.id} className="bg-white border-sage-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                                    <CardContent className="p-6">
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-center gap-3">
                                                <div className={`p-2 rounded-xl ${role.id === 'super_admin' ? 'bg-purple-100 text-purple-600' :
                                                        role.id === 'admin' ? 'bg-blue-100 text-blue-600' :
                                                            role.id === 'sales' ? 'bg-emerald-100 text-emerald-600' :
                                                                role.id === 'operations' ? 'bg-orange-100 text-orange-600' :
                                                                    'bg-cyan-100 text-cyan-600'
                                                    }`}>
                                                    <Shield className="w-5 h-5" />
                                                </div>
                                                <div>
                                                    <h3 className="font-semibold text-sage-700">{role.name}</h3>
                                                    <p className="text-sm text-sage-400">{role.description}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="mt-4 pt-4 border-t border-sage-100 flex items-center justify-between">
                                            <div className="text-sm text-sage-500">
                                                <span className="font-semibold text-sage-700">{role.users_count}</span> users
                                            </div>
                                            <Badge variant="outline" className={roleColors[role.id] || 'bg-gray-100'}>
                                                {role.id.replace('_', ' ')}
                                            </Badge>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        <Card className="bg-sage-50 border-sage-200">
                            <CardContent className="p-6">
                                <div className="flex items-center gap-3 text-sage-600">
                                    <Shield className="w-5 h-5" />
                                    <div>
                                        <h3 className="font-semibold">Role Permissions</h3>
                                        <p className="text-sm text-sage-500">
                                            Roles define what actions users can perform. Super Admin has full access to all features.
                                            Other roles have access to their designated modules only.
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
