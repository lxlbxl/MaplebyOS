import { useEffect } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Mail, Lock, ArrowRight } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { motion } from 'framer-motion';

export default function Login({ status }) {
    const { props } = usePage();
    const appName = props.settings?.app_name || 'Mapleby';

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <div className="flex min-h-screen bg-ivory">
            <Head title="Sign in" />

            {/* Left Panel - Decorative */}
            <div className="hidden lg:flex lg:w-1/2 relative overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-br from-sage via-sage-light to-terracotta-light opacity-90" />
                <div className="absolute inset-0 texture-grain" />

                {/* Ambient shapes */}
                <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl" />
                <div className="absolute bottom-1/4 right-1/4 w-80 h-80 bg-white/10 rounded-full blur-3xl" />

                {/* Content */}
                <div className="relative z-10 flex flex-col justify-between p-16 text-white">
                    <div>
                        <div className="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-6">
                            <span className="font-display text-2xl font-semibold">M</span>
                        </div>
                        <h1 className="font-display text-5xl font-medium tracking-tight leading-tight">
                            {appName}
                        </h1>
                        <p className="mt-2 text-white/70 text-sm uppercase tracking-[0.2em]">
                            Logistics Platform
                        </p>
                    </div>

                    <div className="max-w-sm">
                        <p className="font-display text-2xl leading-relaxed text-white/90">
                            "Simplicity is the ultimate sophistication."
                        </p>
                        <p className="mt-4 text-white/60 text-sm">
                            — Leonardo da Vinci
                        </p>
                    </div>
                </div>
            </div>

            {/* Right Panel - Login Form */}
            <div className="flex-1 flex items-center justify-center p-8 lg:p-16">
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, ease: [0.25, 0.46, 0.45, 0.94] }}
                    className="w-full max-w-md"
                >
                    {/* Mobile Logo */}
                    <div className="lg:hidden mb-12 text-center">
                        <div className="w-14 h-14 rounded-2xl bg-sage mx-auto flex items-center justify-center mb-4">
                            <span className="font-display text-2xl font-semibold text-white">M</span>
                        </div>
                        <h1 className="font-display text-2xl text-void">{appName}</h1>
                    </div>

                    {/* Welcome Text */}
                    <div className="mb-10">
                        <h2 className="text-display-lg mb-2">Welcome back</h2>
                        <p className="text-caption">Sign in to continue to your dashboard</p>
                    </div>

                    {status && (
                        <motion.div
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="mb-6 p-4 rounded-xl bg-success-soft text-success text-sm font-medium"
                        >
                            {status}
                        </motion.div>
                    )}

                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <label htmlFor="email" className="text-label mb-2 block">
                                Email Address
                            </label>
                            <div className="relative">
                                <div className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-pebble">
                                    <Mail className="h-5 w-5" strokeWidth={1.5} />
                                </div>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="name@company.com"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="h-13 pl-12 bg-cream border-0 focus:bg-white"
                                    autoComplete="username"
                                    required
                                />
                            </div>
                            {errors.email && (
                                <p className="mt-2 text-sm text-error">{errors.email}</p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="password" className="text-label mb-2 block">
                                Password
                            </label>
                            <div className="relative">
                                <div className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-pebble">
                                    <Lock className="h-5 w-5" strokeWidth={1.5} />
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    placeholder="Enter your password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className="h-13 pl-12 bg-cream border-0 focus:bg-white"
                                    autoComplete="current-password"
                                    required
                                />
                            </div>
                            {errors.password && (
                                <p className="mt-2 text-sm text-error">{errors.password}</p>
                            )}
                        </div>

                        <div className="flex items-center justify-between pt-2">
                            <label className="flex items-center gap-2 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="w-4 h-4 rounded border-linen text-sage focus:ring-sage focus:ring-offset-0"
                                />
                                <span className="text-sm text-stone group-hover:text-charcoal transition-colors">
                                    Remember me
                                </span>
                            </label>
                        </div>

                        <Button
                            type="submit"
                            disabled={processing}
                            loading={processing}
                            className="w-full h-13 mt-6 text-base rounded-xl btn-sage group"
                        >
                            <span>Sign in</span>
                            <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" strokeWidth={2} />
                        </Button>
                    </form>

                    {/* Footer */}
                    <p className="mt-10 text-center text-caption">
                        Secure login powered by <span className="text-sage font-medium">Mapleby</span>
                    </p>
                </motion.div>
            </div>
        </div>
    );
}
