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

            {/* Left Panel - Brand */}
            <div className="hidden lg:flex lg:w-[52%] relative overflow-hidden auth-panel text-white">
                <div className="auth-leaf" />

                <div className="relative z-10 flex flex-col justify-between w-full p-14 xl:p-20">
                    <div className="flex items-center justify-between">
                        <p className="brand-wordmark text-sm text-white/80">Mapleby Inc.</p>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.3em] text-white/50">
                            Toronto <span className="text-maple">&rarr;</span> Lagos
                        </p>
                    </div>

                    <div className="flex flex-col items-start gap-12">
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9, rotate: -8 }}
                            animate={{ opacity: 1, scale: 1, rotate: 0 }}
                            transition={{ duration: 0.9, ease: [0.16, 1, 0.3, 1] }}
                            className="auth-seal ml-7"
                        >
                            <img src="/brand/badge-256.webp" alt="Mapleby" width={168} height={168} className="rounded-full shadow-[0_24px_60px_rgba(237,23,34,0.35)]" />
                        </motion.div>

                        <div>
                            <div className="gold-rule mb-6" />
                            <h1 className="font-display text-6xl xl:text-7xl font-semibold tracking-tight leading-[0.95]">
                                Built on <span className="text-maple italic">Trust.</span>
                            </h1>
                            <p className="mt-6 max-w-md text-white/60 leading-relaxed">
                                The operations platform behind every Mapleby vehicle, from Canadian auction lot to Lagos terminal.
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.3em] text-white/40">
                        <span>{appName}</span>
                        <span className="text-gold">Est. MMXXVI</span>
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
                    <div className="lg:hidden mb-10 flex flex-col items-center text-center">
                        <img src="/brand/badge-192.png" alt="Mapleby" width={96} height={96} className="rounded-full shadow-[0_14px_36px_rgba(237,23,34,0.25)] mb-5" />
                        <p className="brand-wordmark text-xl text-charcoal">Mapleby</p>
                        <p className="brand-tagline">Logistics OS</p>
                    </div>

                    {/* Welcome Text */}
                    <div className="mb-10">
                        <p className="text-[10px] font-bold uppercase tracking-[0.3em] text-maple mb-3">Staff Portal</p>
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
                                    className="h-13 pl-12 bg-white"
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
                                    className="h-13 pl-12 bg-white"
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
                                    className="w-4 h-4 rounded border-linen accent-maple focus:ring-sage focus:ring-offset-0"
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
                        Secure staff access &middot; <a href="/" className="text-maple font-medium hover:underline">maplebyinc.com</a>
                    </p>
                </motion.div>
            </div>
        </div>
    );
}
