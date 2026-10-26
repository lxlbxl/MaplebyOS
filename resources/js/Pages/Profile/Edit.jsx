import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui/card';
import { User, Lock, Trash2, CheckCircle, Mail, Shield } from 'lucide-react';

export default function Edit({ mustVerifyEmail, status }) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing: profileProcessing } = useForm({
        name: user.name,
        email: user.email,
    });

    const { data: passwordData, setData: setPasswordData, put, errors: passwordErrors, processing: passwordProcessing, reset: resetPassword } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submitProfile = (e) => {
        e.preventDefault();
        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    const submitPassword = (e) => {
        e.preventDefault();
        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => resetPassword(),
        });
    };

    return (
        <AppLayout>
            <Head title="Profile" />

            <div className="flex flex-col gap-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-display-md text-void">My Profile</h1>
                        <p className="text-drift mt-1">Manage your account information and security settings.</p>
                    </div>
                </div>

                <div className="grid lg:grid-cols-3 gap-6">
                    {/* Profile Summary Card */}
                    <Card className="bg-void text-cream border-none shadow-gentle overflow-hidden relative rounded-2xl">
                        <div className="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                        <CardContent className="pt-8 pb-8 flex flex-col items-center relative z-10">
                            <div className="w-20 h-20 bg-white/10 rounded-full flex items-center justify-center mb-4 backdrop-blur-sm border border-white/10">
                                <User className="w-10 h-10 text-white" />
                            </div>
                            <h2 className="text-xl font-serif font-medium text-white">{user.name}</h2>
                            <div className="flex items-center gap-2 text-pebble/80 text-sm mt-1">
                                <Mail className="w-3 h-3" />
                                <span>{user.email}</span>
                            </div>
                            <div className="mt-4 px-3 py-1 bg-white/10 rounded-full text-xs flex items-center gap-1.5 backdrop-blur-sm border border-white/5">
                                <Shield className="w-3 h-3 text-sage" />
                                <span>{user.role || 'User'}</span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Profile Information */}
                        <Card className="bg-white border-linen shadow-sm overflow-hidden rounded-xl">
                            <CardHeader className="border-b border-linen bg-cream/30">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 rounded-xl bg-cream text-charcoal">
                                        <User className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <CardTitle className="text-charcoal font-serif">Profile Information</CardTitle>
                                        <CardDescription className="text-pebble">Update your account's profile information and email address.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <form onSubmit={submitProfile}>
                                <CardContent className="space-y-6 pt-6">
                                    <div className="grid gap-2">
                                        <Label htmlFor="name" className="text-charcoal font-medium">Name</Label>
                                        <Input
                                            id="name"
                                            className="bg-white border-linen text-charcoal focus:border-sage focus:ring-sage/20 rounded-xl h-12"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            required
                                            autoComplete="name"
                                        />
                                        {errors.name && <p className="text-sm text-error">{errors.name}</p>}
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="email" className="text-charcoal font-medium">Email Address</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            className="bg-white border-linen text-charcoal focus:border-sage focus:ring-sage/20 rounded-xl h-12"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            required
                                            autoComplete="username"
                                        />
                                        {errors.email && <p className="text-sm text-error">{errors.email}</p>}
                                    </div>
                                </CardContent>
                                <CardFooter className="bg-cream/30 border-t border-linen py-4 flex items-center justify-between">
                                    <p className="text-xs text-pebble flex items-center gap-1.5">
                                        <CheckCircle className="w-3.5 h-3.5 text-sage" />
                                        Your information is secure.
                                    </p>
                                    <Button
                                        type="submit"
                                        className="btn-sage"
                                        disabled={profileProcessing}
                                    >
                                        {profileProcessing ? 'Saving...' : 'Save Changes'}
                                    </Button>
                                </CardFooter>
                            </form>
                        </Card>

                        {/* Password Update */}
                        <Card className="bg-white border-linen shadow-sm overflow-hidden rounded-xl">
                            <CardHeader className="border-b border-linen bg-cream/30">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 rounded-xl bg-cream text-charcoal">
                                        <Lock className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <CardTitle className="text-charcoal font-serif">Update Password</CardTitle>
                                        <CardDescription className="text-pebble">Ensure your account is using a long, random password to stay secure.</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <form onSubmit={submitPassword}>
                                <CardContent className="space-y-6 pt-6">
                                    <div className="grid gap-2">
                                        <Label htmlFor="current_password" className="text-charcoal font-medium">Current Password</Label>
                                        <Input
                                            id="current_password"
                                            type="password"
                                            className="bg-white border-linen text-charcoal focus:border-sage focus:ring-sage/20 rounded-xl h-12"
                                            value={passwordData.current_password}
                                            onChange={(e) => setPasswordData('current_password', e.target.value)}
                                            autoComplete="current-password"
                                        />
                                        {passwordErrors.current_password && <p className="text-sm text-error">{passwordErrors.current_password}</p>}
                                    </div>

                                    <div className="grid md:grid-cols-2 gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="password" className="text-charcoal font-medium">New Password</Label>
                                            <Input
                                                id="password"
                                                type="password"
                                                className="bg-white border-linen text-charcoal focus:border-sage focus:ring-sage/20 rounded-xl h-12"
                                                value={passwordData.password}
                                                onChange={(e) => setPasswordData('password', e.target.value)}
                                                autoComplete="new-password"
                                            />
                                            {passwordErrors.password && <p className="text-sm text-error">{passwordErrors.password}</p>}
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="password_confirmation" className="text-charcoal font-medium">Confirm Password</Label>
                                            <Input
                                                id="password_confirmation"
                                                type="password"
                                                className="bg-white border-linen text-charcoal focus:border-sage focus:ring-sage/20 rounded-xl h-12"
                                                value={passwordData.password_confirmation}
                                                onChange={(e) => setPasswordData('password_confirmation', e.target.value)}
                                                autoComplete="new-password"
                                            />
                                            {passwordErrors.password_confirmation && <p className="text-sm text-error">{passwordErrors.password_confirmation}</p>}
                                        </div>
                                    </div>
                                </CardContent>
                                <CardFooter className="bg-cream/30 border-t border-linen py-4 flex items-center justify-end">
                                    <Button
                                        type="submit"
                                        className="btn-sage"
                                        disabled={passwordProcessing}
                                    >
                                        {passwordProcessing ? 'Updating...' : 'Update Password'}
                                    </Button>
                                </CardFooter>
                            </form>
                        </Card>

                        {/* Delete Account */}
                        <Card className="bg-error-soft/30 border-error-soft shadow-sm overflow-hidden rounded-xl">
                            <CardHeader>
                                <div className="flex items-center gap-3">
                                    <div className="p-2 rounded-xl bg-error-soft text-error">
                                        <Trash2 className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <CardTitle className="text-error font-serif">Delete Account</CardTitle>
                                        <CardDescription className="text-error/80">
                                            Once your account is deleted, all of its resources and data will be permanently deleted.
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardFooter className="justify-end border-t border-error-soft/50 bg-error-soft/20 py-4">
                                <Button variant="destructive" className="bg-error hover:bg-error/90 rounded-xl">
                                    Delete Account
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
