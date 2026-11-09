import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import InstallAppButton from '@/Components/App/InstallAppButton';
import VinScanner from '@/Components/Smart/VinScanner';
import { useCan } from '@/lib/access';
import { VEHICLE_STATUS_COLORS, CONTAINER_STATUS_COLORS, formatDate } from '@/lib/operations';
import { formatMoney, cn } from '@/lib/utils';
import {
    Search, ScanLine, PlusCircle, AlertTriangle, Info, Ship, Car, Container as ContainerIcon,
    Receipt, Sparkles, FileText, ChevronRight, Wrench, CalendarClock, FileWarning, Landmark,
} from 'lucide-react';
import { money } from '@/lib/deposits';

// ---- small building blocks ------------------------------------------------

function Stat({ label, value, tone = 'default', hint }) {
    return (
        <div className={cn('rounded-2xl border p-4', tone === 'good' ? 'bg-emerald-50 border-emerald-100' : tone === 'bad' ? 'bg-red-50 border-red-100' : 'bg-white border-linen')}>
            <p className="text-xs text-pebble">{label}</p>
            <p className={cn('text-xl font-semibold mt-1', tone === 'good' ? 'text-emerald-700' : tone === 'bad' ? 'text-red-700' : 'text-void')}>{value}</p>
            {hint && <p className="text-[11px] text-pebble mt-0.5">{hint}</p>}
        </div>
    );
}

function Section({ title, icon: Icon, action, children }) {
    return (
        <Card className="bg-white border-linen shadow-sm">
            <CardHeader className="border-b border-linen py-3 px-4 flex flex-row items-center justify-between space-y-0">
                <CardTitle className="text-void text-base flex items-center gap-2">{Icon && <Icon className="h-4 w-4 text-sage" />} {title}</CardTitle>
                {action}
            </CardHeader>
            <CardContent className="p-0">{children}</CardContent>
        </Card>
    );
}

function Empty({ children }) {
    return <p className="px-4 py-6 text-sm text-pebble text-center">{children}</p>;
}

function SeeAll({ href, label = 'See all' }) {
    return <Link href={href} className="text-xs text-sage hover:underline">{label}</Link>;
}

function CarRow({ car, right }) {
    return (
        <Link href={route('operations.shipments.show', car.id)} className="flex items-center gap-3 px-4 py-3 hover:bg-cream/60">
            <div className="min-w-0 flex-1">
                <p className="text-sm font-medium text-void truncate">{car.vehicle || 'Vehicle'}</p>
                <p className="text-xs text-stone font-mono truncate">{car.tracking_id} · {car.vin || 'no VIN'}</p>
            </div>
            {right ?? <span className={cn('text-[11px] px-2 py-0.5 rounded-full', VEHICLE_STATUS_COLORS[car.status])}>{car.status_label}</span>}
            <ChevronRight className="h-4 w-4 text-pebble" />
        </Link>
    );
}

function ContainerRow({ c, right }) {
    return (
        <Link href={route('operations.containers.show', c.id)} className="flex items-center gap-3 px-4 py-3 hover:bg-cream/60">
            <div className="min-w-0 flex-1">
                <p className="text-sm font-mono font-medium text-void">{c.number}</p>
                <p className="text-xs text-stone truncate">{c.line || 'Line unknown'} · {c.cars ?? '?'}/{c.capacity} cars</p>
            </div>
            {right ?? <span className={cn('text-[11px] px-2 py-0.5 rounded-full', CONTAINER_STATUS_COLORS[c.status])}>{c.status_label}</span>}
            <ChevronRight className="h-4 w-4 text-pebble" />
        </Link>
    );
}

function Eta({ c }) {
    if (!c.eta) return <span className="text-xs text-pebble">no ETA</span>;
    const d = c.days_to_eta;
    const text = d === 0 ? 'today' : d > 0 ? `in ${d}d` : `${-d}d late`;
    return <span className={cn('text-xs font-medium', d < 0 ? 'text-red-600' : d <= 7 ? 'text-amber-700' : 'text-stone')}>ETA {formatDate(c.eta)} · {text}</span>;
}

function Pipeline({ pipeline }) {
    const main = pipeline.filter((p) => !['on_hold', 'issue'].includes(p.status));
    const side = pipeline.filter((p) => ['on_hold', 'issue'].includes(p.status) && p.count > 0);
    return (
        <Section title="Cars by stage" icon={Car} action={<SeeAll href={route('operations.shipments.index')} />}>
            <div className="grid grid-cols-5 sm:grid-cols-10 gap-px bg-linen">
                {main.map((p) => (
                    <Link key={p.status} href={route('operations.shipments.index', { status: p.status })} className="bg-white p-2 text-center hover:bg-cream/60">
                        <p className="text-lg font-semibold text-void">{p.count}</p>
                        <p className="text-[10px] leading-tight text-pebble">{p.label}</p>
                    </Link>
                ))}
            </div>
            {side.length > 0 && (
                <div className="px-4 py-2 flex gap-3 text-xs">
                    {side.map((p) => <Link key={p.status} href={route('operations.shipments.index', { status: p.status })} className="text-red-700 underline">{p.count} {p.label.toLowerCase()}</Link>)}
                </div>
            )}
        </Section>
    );
}

function QuickFind() {
    const can = useCan();
    const [q, setQ] = useState('');
    const [scan, setScan] = useState(false);
    if (!can('vehicles.view')) return null;
    const go = (term) => term && router.get(route('operations.shipments.index'), { search: term });

    return (
        <form onSubmit={(e) => { e.preventDefault(); go(q.trim()); }} className="flex gap-2">
            <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-pebble" />
                <Input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Find a car: VIN, tracking ID, container" className="pl-9 h-12 bg-white border-linen rounded-xl" />
            </div>
            <Button type="button" variant="outline" onClick={() => setScan(true)} className="h-12 border-linen rounded-xl" title="Scan VIN"><ScanLine className="h-5 w-5" /></Button>
            <VinScanner open={scan} onOpenChange={setScan} onVin={(vin) => go(vin)} />
        </form>
    );
}

// ---- per-role homes ---------------------------------------------------------

function CeoHome({ data, currency }) {
    const m = data.money;
    return (
        <>
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <Stat label="Profit on cars delivered this month" value={formatMoney(m.profit_month ?? 0, currency)} tone={m.profit_month >= 0 ? 'good' : 'bad'} />
                <Stat label="Customers owe" value={formatMoney(m.outstanding, currency)} />
                <Stat label="Invoiced this month" value={formatMoney(m.invoiced_month, currency)} />
                <Stat label="Collected this month" value={formatMoney(m.collected_month, currency)} />
            </div>

            {data.alerts.length > 0 && (
                <Section title="Needs attention" icon={AlertTriangle}>
                    <div className="divide-y divide-linen">
                        {data.alerts.map((a) => (
                            <Link key={a.text} href={a.href} className="flex items-center gap-3 px-4 py-3 hover:bg-cream/60 text-sm">
                                {a.level === 'warning' ? <AlertTriangle className="h-4 w-4 text-amber-600" /> : <Info className="h-4 w-4 text-sky-600" />}
                                <span className="flex-1">{a.text}</span>
                                <ChevronRight className="h-4 w-4 text-pebble" />
                            </Link>
                        ))}
                    </div>
                </Section>
            )}

            <Pipeline pipeline={data.pipeline} />

            <div className="grid lg:grid-cols-2 gap-4">
                <Section title="At sea" icon={Ship} action={<SeeAll href={route('operations.containers.index')} />}>
                    <div className="divide-y divide-linen">
                        {data.atSea.map((c) => <ContainerRow key={c.id} c={c} right={<Eta c={c} />} />)}
                        {data.atSea.length === 0 && <Empty>No containers at sea.</Empty>}
                    </div>
                </Section>
                <Section title="Profit by container (latest loaded)" icon={ContainerIcon}>
                    <div className="divide-y divide-linen">
                        {data.containerProfit.map((c) => (
                            <ContainerRow key={c.id} c={c} right={<span className={cn('text-sm font-semibold', c.profit >= 0 ? 'text-emerald-700' : 'text-red-700')}>{formatMoney(c.profit ?? 0, currency)}</span>} />
                        ))}
                        {data.containerProfit.length === 0 && <Empty>No loaded containers yet.</Empty>}
                    </div>
                </Section>
            </div>
        </>
    );
}

function BusinessHome({ data, currency }) {
    const m = data.money;
    return (
        <>
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <Stat label="Customers owe" value={formatMoney(m.outstanding, currency)} />
                <Stat label="Collected this month" value={formatMoney(m.collected_month, currency)} />
                <Stat label="Profit on cars delivered this month" value={formatMoney(m.profit_month ?? 0, currency)} tone={m.profit_month >= 0 ? 'good' : 'bad'} />
                <Stat label="New leads" value={data.newLeadCount} />
            </div>

            <div className="grid lg:grid-cols-2 gap-4">
                <Section title="Unpaid invoices" icon={Receipt} action={<SeeAll href={route('finance.invoices.index')} />}>
                    <div className="divide-y divide-linen">
                        {data.unpaidInvoices.map((i) => (
                            <Link key={i.id} href={route('finance.invoices.show', i.id)} className="flex items-center gap-3 px-4 py-3 hover:bg-cream/60">
                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-medium text-void">{i.client || 'No customer'}</p>
                                    <p className="text-xs text-stone">{i.number} · due {formatDate(i.due_date)}</p>
                                </div>
                                <span className={cn('text-sm font-semibold', i.overdue ? 'text-red-700' : 'text-void')}>{formatMoney(i.balance, currency)}</span>
                            </Link>
                        ))}
                        {data.unpaidInvoices.length === 0 && <Empty>Nothing outstanding.</Empty>}
                    </div>
                </Section>

                <Section title="Delivered, not fully paid" icon={Car}>
                    <div className="divide-y divide-linen">
                        {data.deliveredUnpaid.map((c) => (
                            <CarRow key={c.id} car={c} right={<span className="text-xs text-amber-700">{c.client || 'no customer'} · {c.payment_status}</span>} />
                        ))}
                        {data.deliveredUnpaid.length === 0 && <Empty>All delivered cars are paid.</Empty>}
                    </div>
                </Section>

                <Section title="Quotes waiting on a reply" icon={FileText} action={<SeeAll href={route('operations.quotes.index')} />}>
                    <div className="divide-y divide-linen">
                        {data.quotesAwaiting.map((q) => (
                            <Link key={q.id} href={route('operations.quotes.show', q.id)} className="flex items-center gap-3 px-4 py-3 hover:bg-cream/60">
                                <div className="flex-1 min-w-0"><p className="text-sm font-medium text-void">{q.client}</p><p className="text-xs text-stone">{q.number}</p></div>
                                <span className="text-sm">{formatMoney(q.total, currency)}</span>
                            </Link>
                        ))}
                        {data.quotesAwaiting.length === 0 && <Empty>No quotes waiting.</Empty>}
                    </div>
                </Section>

                <Section title="New leads" icon={Sparkles} action={<SeeAll href={route('crm.leads.index')} />}>
                    <div className="divide-y divide-linen">
                        {data.newLeads.map((l) => (
                            <Link key={l.id} href={route('crm.leads.index')} className="flex items-center gap-3 px-4 py-3 hover:bg-cream/60">
                                <div className="flex-1 min-w-0"><p className="text-sm font-medium text-void truncate">{l.title || l.client || 'Lead'}</p><p className="text-xs text-stone">{l.client}</p></div>
                                <span className="text-xs text-pebble">{l.date}</span>
                            </Link>
                        ))}
                        {data.newLeads.length === 0 && <Empty>No new leads.</Empty>}
                    </div>
                </Section>
            </div>

            <Pipeline pipeline={data.pipeline} />
        </>
    );
}

function LogisticsHome({ data }) {
    const can = useCan();
    return (
        <>
            <div className="grid grid-cols-3 sm:grid-cols-6 gap-2">
                {data.stages.map((s) => (
                    <Link key={s.status} href={route('operations.containers.index')} className="rounded-xl border border-linen bg-white p-3 text-center hover:border-sage/40">
                        <p className="text-xl font-semibold text-void">{s.count}</p>
                        <p className="text-[11px] text-pebble leading-tight">{s.label}</p>
                    </Link>
                ))}
            </div>

            <div className="grid lg:grid-cols-2 gap-4">
                <Section title="Arriving this week" icon={CalendarClock}>
                    <div className="divide-y divide-linen">
                        {data.arrivals.map((c) => <ContainerRow key={c.id} c={c} right={<Eta c={c} />} />)}
                        {data.arrivals.length === 0 && <Empty>No arrivals due in the next 7 days.</Empty>}
                    </div>
                </Section>

                <Section title={`Waiting for a container (${data.waitingCount})`} icon={Car}
                    action={<SeeAll href={route('operations.shipments.index', { view: 'unassigned' })} />}>
                    <div className="divide-y divide-linen">
                        {data.waiting.map((c) => (
                            <CarRow key={c.id} car={c} right={<span className={cn('text-xs', c.days_waiting > 30 ? 'text-red-600 font-medium' : 'text-stone')}>{c.days_waiting ?? '?'}d</span>} />
                        ))}
                        {data.waiting.length === 0 && <Empty>Every car is in a container.</Empty>}
                    </div>
                </Section>

                <Section title="Loading now" icon={ContainerIcon}
                    action={can('containers.manage') ? <SeeAll href={route('operations.containers.index')} label="New container" /> : null}>
                    <div className="divide-y divide-linen">
                        {data.openContainers.map((c) => <ContainerRow key={c.id} c={c} />)}
                        {data.openContainers.length === 0 && <Empty>No containers being loaded.</Empty>}
                    </div>
                </Section>

                <Section title="At sea" icon={Ship}>
                    <div className="divide-y divide-linen">
                        {data.atSea.map((c) => <ContainerRow key={c.id} c={c} right={<Eta c={c} />} />)}
                        {data.atSea.length === 0 && <Empty>No containers at sea.</Empty>}
                    </div>
                </Section>

                <Section title="Sailed without a Bill of Lading" icon={FileWarning}>
                    <div className="divide-y divide-linen">
                        {data.missingDocs.map((c) => <ContainerRow key={c.id} c={c} right={<span className="text-xs text-amber-700">upload BL</span>} />)}
                        {data.missingDocs.length === 0 && <Empty>All sailed containers have their Bill of Lading.</Empty>}
                    </div>
                </Section>

                <Section title="Missing VIN or keys" icon={Wrench}>
                    <div className="divide-y divide-linen">
                        {data.problems.map((c) => <CarRow key={c.id} car={c} right={<span className="text-xs text-red-600">{c.problem}</span>} />)}
                        {data.problems.length === 0 && <Empty>No problems.</Empty>}
                    </div>
                </Section>
            </div>
        </>
    );
}

function PuncherHome({ data }) {
    const can = useCan();
    return (
        <>
            {can('vehicles.create') && (
                <Link href={route('operations.shipments.index', { create: 'true' })}
                    className="flex items-center justify-center gap-3 rounded-2xl bg-sage text-white py-6 text-lg font-semibold shadow-soft active:scale-[0.99] transition">
                    <PlusCircle className="h-7 w-7" /> Add car
                </Link>
            )}

            <div className="grid grid-cols-3 gap-3">
                <Stat label="Added today" value={data.counts.today} />
                <Stat label="This week" value={data.counts.week} />
                <Stat label="Need fixing" value={data.counts.needs_fixing} tone={data.counts.needs_fixing ? 'bad' : 'default'} />
            </div>

            <Section title="Your entries that need fixing" icon={AlertTriangle}>
                <div className="divide-y divide-linen">
                    {data.needsFixing.map((c) => <CarRow key={c.id} car={c} right={<span className="text-xs text-red-600">{c.problem}</span>} />)}
                    {data.needsFixing.length === 0 && <Empty>Nothing to fix. Nice work.</Empty>}
                </div>
            </Section>

            <Section title="Added today" icon={Car}>
                <div className="divide-y divide-linen">
                    {data.today.map((c) => <CarRow key={c.id} car={c} right={<span className="text-xs text-pebble">{c.time}</span>} />)}
                    {data.today.length === 0 && <Empty>You haven’t added a car today.</Empty>}
                </div>
            </Section>
        </>
    );
}

function DepositsSection({ deposits }) {
    if (!deposits) return null;
    const c = deposits.summary.counts;
    const totals = Object.entries(deposits.summary.byCurrency);
    if (!c.open && !deposits.due.length) return null;
    return (
        <Section title={deposits.oversees ? 'Container deposits' : 'Deposit refunds to chase'} icon={Landmark} action={<SeeAll href={route('operations.deposits.index', { view: 'follow_up' })} />}>
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 p-4 border-b border-linen">
                {totals.map(([cur, t]) => <Stat key={cur} label={`Still to come back (${cur})`} value={money(t.outstanding, cur)} hint={`${t.deposits} deposit${t.deposits === 1 ? '' : 's'}`} />)}
                <Stat label="Follow-up due" value={c.follow_up} tone={c.follow_up ? 'bad' : 'default'} />
                <Stat label="Claim not filed" value={c.to_claim} tone={c.to_claim ? 'bad' : 'default'} />
                {deposits.oversees && <Stat label="Refunded, to confirm" value={c.to_confirm} />}
            </div>
            <div className="divide-y divide-linen">
                {deposits.due.map((d) => (
                    <Link key={d.deposit_id} href={route('operations.deposits.show', d.deposit_id)} className="flex items-center gap-3 px-4 py-3 hover:bg-cream/60">
                        <div className="min-w-0 flex-1">
                            <p className="text-sm font-medium text-void font-mono">{d.container_number}</p>
                            <p className="text-xs text-pebble">{d.line || 'Line not set'} · {d.status_label}</p>
                        </div>
                        <span className="text-xs text-red-600 whitespace-nowrap">{d.outstanding > 0 ? money(d.outstanding, d.currency) : ''} · due {formatDate(d.next_follow_up_on)}</span>
                        <ChevronRight className="h-4 w-4 text-pebble" />
                    </Link>
                ))}
                {deposits.due.length === 0 && <Empty>No refunds to chase today.</Empty>}
            </div>
        </Section>
    );
}

const HOMES = { ceo: CeoHome, business: BusinessHome, logistics: LogisticsHome, puncher: PuncherHome };

export default function Index({ home, greeting, roleLabel, data }) {
    const { props } = usePage();
    const currency = props.settings?.currency || 'USD';
    const Home = HOMES[home] || PuncherHome;

    return (
        <AppLayout>
            <Head title="Home" />
            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-4 max-w-6xl mx-auto pb-24">
                <div>
                    <h1 className="text-display-sm text-void font-bold">{greeting}, {props.auth?.user?.name?.split(' ')[0]}</h1>
                    <p className="text-sm text-pebble">{roleLabel}</p>
                </div>
                <InstallAppButton variant="banner" />
                <QuickFind />
                <Home data={data} currency={currency} />
                <DepositsSection deposits={data.deposits} />
            </div>
        </AppLayout>
    );
}
