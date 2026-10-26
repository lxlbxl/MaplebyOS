import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, usePage, router, useForm } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import CurrencyDisplay from '@/Components/CurrencyDisplay';
import {
    ArrowUpRight,
    ArrowDownRight,
    CheckCircle2,
    Clock,
    Package,
    Plus,
    ChevronRight,
    Check,
    Trash2,
    Users,
    TrendingUp,
    X
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Badge } from "@/Components/ui/badge";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

export default function Dashboard({ auth, stats, tasks, recentShipments, users, filters }) {
    const { props } = usePage();
    const settings = props.settings || {};
    const currency = settings.currency || 'USD';

    const [timeRange, setTimeRange] = useState(filters?.time_range || '30d');
    const [isTaskModalOpen, setIsTaskModalOpen] = useState(false);

    const handleTimeRangeChange = (range) => {
        setTimeRange(range);
        router.get(route('dashboard'), { time_range: range }, {
            preserveState: true,
            preserveScroll: true,
            only: ['stats', 'filters'],
        });
    };

    const { data: taskData, setData: setTaskData, post: postTask, processing: processingTask, errors: taskErrors, reset: resetTask } = useForm({
        title: '',
        due_date: new Date().toISOString().slice(0, 16),
        priority: 'medium',
        assigned_to: auth.user.id.toString(),
    });

    const submitTask = (e) => {
        e.preventDefault();
        postTask(route('tasks.store'), {
            onSuccess: () => {
                setIsTaskModalOpen(false);
                resetTask();
            },
        });
    };

    const firstName = auth.user.name.split(' ')[0];
    const hour = new Date().getHours();
    const timeGreeting = hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening';
    const currentDate = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });

    // Animation variants
    const containerVariants = {
        hidden: { opacity: 0 },
        visible: {
            opacity: 1,
            transition: {
                staggerChildren: 0.08,
                delayChildren: 0.1
            }
        }
    };

    const itemVariants = {
        hidden: { opacity: 0, y: 16 },
        visible: {
            opacity: 1,
            y: 0,
            transition: {
                duration: 0.5,
                ease: [0.25, 0.46, 0.45, 0.94]
            }
        }
    };

    return (
        <AppLayout>
            <Head title="Dashboard" />

            <motion.div
                variants={containerVariants}
                initial="hidden"
                animate="visible"
                className="p-4 md:p-6 lg:p-10 max-w-[1400px] mx-auto"
            >
                {/* Header Section */}
                <motion.header variants={itemVariants} className="mb-10">
                    <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                        <div>
                            <p className="text-label mb-3">
                                {currentDate}
                            </p>
                            <h1 className="text-display-hero">
                                {timeGreeting}, <span className="gradient-text-sage">{firstName}</span>
                            </h1>
                        </div>
                        <div className="flex gap-2">
                            <div className="inline-flex bg-cream rounded-xl p-1">
                                {['30D', 'YTD', '12M', 'ALL'].map((range) => (
                                    <button
                                        key={range}
                                        onClick={() => handleTimeRangeChange(range.toLowerCase())}
                                        className={cn(
                                            "px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300",
                                            timeRange === range.toLowerCase()
                                                ? "bg-white text-void shadow-soft"
                                                : "text-drift hover:text-stone"
                                        )}
                                    >
                                        {range}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>
                </motion.header>

                {/* Main Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-5">

                    {/* Revenue Card - Hero */}
                    <motion.div
                        variants={itemVariants}
                        className="lg:col-span-5 card-void p-8 relative group"
                    >
                        <div className="relative z-10">
                            <div className="flex items-center gap-3 mb-8">
                                <div className="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                                    <TrendingUp className="w-5 h-5 text-sage-light" strokeWidth={1.5} />
                                </div>
                                <span className="metric-label text-sand">Total Revenue</span>
                            </div>
                            <CurrencyDisplay
                                amount={stats.revenue.value}
                                currency={currency}
                                className="metric-value !text-cream mb-5"
                                symbolClassName="text-sand/60 bg-white/10"
                            />
                            <div className="flex items-center gap-3">
                                {(stats.revenue?.trend || '').startsWith('+') ? (
                                    <span className="flex items-center gap-1.5 text-sage-light text-sm font-medium bg-sage/20 px-2.5 py-1 rounded-full">
                                        <ArrowUpRight className="w-3.5 h-3.5" /> {stats.revenue?.trend}
                                    </span>
                                ) : (stats.revenue?.trend || '').startsWith('-') ? (
                                    <span className="flex items-center gap-1.5 text-terracotta-light text-sm font-medium bg-terracotta/20 px-2.5 py-1 rounded-full">
                                        <ArrowDownRight className="w-3.5 h-3.5" /> {stats.revenue?.trend}
                                    </span>
                                ) : (
                                    <span className="text-sand/60 text-sm">No change</span>
                                )}
                                <span className="text-sand/40 text-sm">vs previous period</span>
                            </div>
                        </div>
                        {/* Ambient glow */}
                        <div className="absolute -top-32 -right-32 w-64 h-64 bg-sage/20 rounded-full blur-3xl opacity-60 group-hover:opacity-100 transition-opacity duration-1000" />
                        <div className="absolute -bottom-20 -left-20 w-48 h-48 bg-terracotta/10 rounded-full blur-3xl opacity-40" />
                    </motion.div>

                    {/* Shipments Overview */}
                    <motion.div
                        variants={itemVariants}
                        className="lg:col-span-4 card-serene p-7"
                    >
                        <div className="flex items-center justify-between mb-6">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-cream flex items-center justify-center">
                                    <Package className="w-5 h-5 text-stone" strokeWidth={1.5} />
                                </div>
                                <span className="metric-label">Shipments</span>
                            </div>
                            <Link
                                href={route('operations.shipments.index')}
                                className="text-pebble hover:text-sage transition-colors duration-200"
                            >
                                <ChevronRight className="w-5 h-5" />
                            </Link>
                        </div>
                        <p className="metric-value mb-6">{stats.projects.total}</p>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="bg-cream rounded-xl p-4 border border-transparent hover:border-sage-soft transition-colors">
                                <p className="metric-value-sm">{stats.projects.running}</p>
                                <p className="text-label mt-1">Active</p>
                            </div>
                            <div className="bg-success-soft rounded-xl p-4 border border-transparent hover:border-success/20 transition-colors">
                                <p className="metric-value-sm text-success">{stats.projects.ended}</p>
                                <p className="text-label mt-1 text-success/70">Delivered</p>
                            </div>
                        </div>
                    </motion.div>

                    {/* Top Clients */}
                    <motion.div
                        variants={itemVariants}
                        className="lg:col-span-3 card-serene p-6"
                    >
                        <div className="flex items-center gap-2 mb-5">
                            <Users className="w-4 h-4 text-pebble" strokeWidth={1.5} />
                            <span className="metric-label">Top Clients</span>
                        </div>
                        <div className="space-y-2">
                            {stats.top_clients && stats.top_clients.length > 0 ? (
                                stats.top_clients.slice(0, 4).map((client, idx) => (
                                    <div
                                        key={client.id}
                                        className="flex items-center justify-between p-3 rounded-xl bg-cream hover:bg-linen transition-colors duration-200 group"
                                    >
                                        <div className="flex items-center gap-3 min-w-0">
                                            <div className={cn(
                                                "w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-semibold",
                                                idx === 0 ? "bg-sage" : idx === 1 ? "bg-terracotta" : "bg-stone"
                                            )}>
                                                {client.name.charAt(0)}
                                            </div>
                                            <span className="font-medium text-charcoal text-sm truncate group-hover:text-void transition-colors" title={client.name}>
                                                {client.name}
                                            </span>
                                        </div>
                                        <span className="text-xs font-bold text-void bg-white px-2 py-1 rounded-md shadow-whisper">
                                            {client.count}
                                        </span>
                                    </div>
                                ))
                            ) : (
                                <p className="text-pebble text-sm text-center py-6">No clients yet</p>
                            )}
                        </div>
                    </motion.div>

                    {/* Tasks Section */}
                    <motion.div
                        variants={itemVariants}
                        className="lg:col-span-7 card-serene p-7"
                    >
                        <div className="flex items-center justify-between mb-6">
                            <div>
                                <h2 className="text-display-md">Tasks</h2>
                                <p className="text-caption mt-1">Your priority items for today</p>
                            </div>
                            <button
                                onClick={() => setIsTaskModalOpen(true)}
                                className="btn-sage"
                            >
                                <Plus className="w-4 h-4" strokeWidth={2} /> New Task
                            </button>
                        </div>

                        <div className="space-y-2">
                            {tasks.length > 0 ? tasks.slice(0, 5).map((task, idx) => (
                                <motion.div
                                    key={task.id}
                                    initial={{ opacity: 0, x: -10 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    transition={{ delay: idx * 0.05 }}
                                    className={cn(
                                        "flex items-center justify-between p-4 rounded-xl border transition-all duration-200 group",
                                        task.is_overdue
                                            ? "bg-error-soft border-error/10"
                                            : "bg-cream border-transparent hover:border-linen hover:bg-linen"
                                    )}
                                >
                                    <div className="flex items-center gap-4 flex-1 min-w-0">
                                        <div className={cn(
                                            "w-2 h-2 rounded-full",
                                            task.priority === 'high' ? "bg-error" : task.priority === 'medium' ? "bg-warning" : "bg-sage"
                                        )} />
                                        <div className="min-w-0">
                                            <p className={cn(
                                                "font-medium truncate",
                                                task.is_overdue ? "text-error" : "text-charcoal"
                                            )}>
                                                {task.title}
                                            </p>
                                            <p className="text-caption flex items-center gap-2 mt-0.5">
                                                <Clock className="w-3 h-3" strokeWidth={1.5} />
                                                {task.due_date || 'No due date'}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            onClick={() => {
                                                if (confirm('Delete this task?')) {
                                                    router.delete(route('tasks.destroy', task.id));
                                                }
                                            }}
                                            className="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-pebble hover:text-error hover:bg-error-soft transition-all"
                                        >
                                            <Trash2 className="w-4 h-4" strokeWidth={1.5} />
                                        </button>
                                        <button
                                            onClick={() => router.put(route('tasks.update', task.id), { status: 'completed' })}
                                            className="w-8 h-8 rounded-lg bg-sage flex items-center justify-center text-white hover:bg-sage-light transition-all"
                                        >
                                            <Check className="w-4 h-4" strokeWidth={2} />
                                        </button>
                                    </div>
                                </motion.div>
                            )) : (
                                <div className="empty-state py-12">
                                    <div className="w-16 h-16 rounded-2xl bg-sage-mist flex items-center justify-center mb-4">
                                        <CheckCircle2 className="w-8 h-8 text-sage" strokeWidth={1.5} />
                                    </div>
                                    <p className="font-display text-lg text-charcoal">All caught up</p>
                                    <p className="text-caption mt-1">No pending tasks to show</p>
                                </div>
                            )}
                        </div>
                    </motion.div>

                    {/* Recent Shipments */}
                    <motion.div
                        variants={itemVariants}
                        className="lg:col-span-5 card-sage p-7 relative overflow-hidden"
                    >
                        <div className="relative z-10">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <h2 className="text-display-md text-void">Recent Shipments</h2>
                                    <p className="text-caption mt-1">Latest delivery activity</p>
                                </div>
                                <Link
                                    href={route('operations.shipments.index')}
                                    className="btn-minimal text-sage"
                                >
                                    View all <ChevronRight className="w-4 h-4" />
                                </Link>
                            </div>

                            <div className="space-y-2">
                                {recentShipments.slice(0, 4).map((shipment, idx) => (
                                    <Link
                                        key={shipment.id}
                                        href={route('operations.shipments.show', shipment.id)}
                                        className="block p-4 rounded-xl bg-white/60 hover:bg-white transition-all duration-200 backdrop-blur-sm border border-white/50 hover:border-sage-soft hover:shadow-soft group"
                                    >
                                        <div className="flex justify-between items-start mb-1.5">
                                            <p className="font-medium text-charcoal truncate max-w-[180px] group-hover:text-void transition-colors">
                                                {shipment.title}
                                            </p>
                                            <span className={cn(
                                                "badge-muted",
                                                shipment.status === 'delivered' && "badge-success",
                                                shipment.status === 'in_transit' && "badge-sage",
                                                shipment.status === 'pending' && "badge-warning"
                                            )}>
                                                {(shipment.status || 'pending').replace('_', ' ')}
                                            </span>
                                        </div>
                                        <p className="text-caption">{shipment.client_name}</p>
                                    </Link>
                                ))}
                            </div>
                        </div>
                    </motion.div>

                </div>

                {/* Task Modal */}
                <AnimatePresence>
                    {isTaskModalOpen && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            transition={{ duration: 0.2 }}
                            className="fixed inset-0 bg-void/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                            onClick={() => setIsTaskModalOpen(false)}
                        >
                            <motion.div
                                initial={{ opacity: 0, scale: 0.96, y: 10 }}
                                animate={{ opacity: 1, scale: 1, y: 0 }}
                                exit={{ opacity: 0, scale: 0.96, y: 10 }}
                                transition={{
                                    duration: 0.3,
                                    ease: [0.25, 0.46, 0.45, 0.94]
                                }}
                                className="bg-white rounded-2xl p-8 w-full max-w-md shadow-lift border border-linen"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <div className="flex items-center justify-between mb-8">
                                    <div>
                                        <h2 className="text-display-md">New Task</h2>
                                        <p className="text-caption mt-1">Create a new task to track</p>
                                    </div>
                                    <button
                                        onClick={() => setIsTaskModalOpen(false)}
                                        className="w-9 h-9 rounded-xl bg-cream flex items-center justify-center text-pebble hover:text-void hover:bg-linen transition-all"
                                    >
                                        <X className="w-4 h-4" strokeWidth={1.5} />
                                    </button>
                                </div>

                                <form onSubmit={submitTask} className="space-y-5">
                                    <div>
                                        <Label className="text-label mb-2 block">Task Title</Label>
                                        <Input
                                            value={taskData.title}
                                            onChange={e => setTaskData('title', e.target.value)}
                                            required
                                            placeholder="What needs to be done?"
                                            className="input-serene h-12"
                                        />
                                        {taskErrors.title && <span className="text-error text-sm mt-1.5 block">{taskErrors.title}</span>}
                                    </div>
                                    <div>
                                        <Label className="text-label mb-2 block">Due Date</Label>
                                        <Input
                                            type="datetime-local"
                                            value={taskData.due_date}
                                            onChange={e => setTaskData('due_date', e.target.value)}
                                            className="input-serene h-12"
                                        />
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label className="text-label mb-2 block">Assigned To</Label>
                                            <Select value={taskData.assigned_to} onValueChange={(val) => setTaskData('assigned_to', val)}>
                                                <SelectTrigger className="h-12 bg-cream border-0">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {users && users.map(user => (
                                                        <SelectItem key={user.id} value={user.id.toString()}>
                                                            {user.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Label className="text-label mb-2 block">Priority</Label>
                                            <Select value={taskData.priority} onValueChange={(val) => setTaskData('priority', val)}>
                                                <SelectTrigger className="h-12 bg-cream border-0">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="low">Low</SelectItem>
                                                    <SelectItem value="medium">Medium</SelectItem>
                                                    <SelectItem value="high">High</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <div className="pt-2">
                                        <Button
                                            type="submit"
                                            disabled={processingTask}
                                            className="w-full h-12 text-base btn-sage rounded-xl font-semibold"
                                        >
                                            Create Task
                                        </Button>
                                    </div>
                                </form>
                            </motion.div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </motion.div>
        </AppLayout>
    );
}
