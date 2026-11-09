import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import DocumentsPanel from '@/Components/Operations/DocumentsPanel';
import ClearingOfficerPanel from '@/Components/Operations/ClearingOfficerPanel';
import DepositsPanel from '@/Components/Operations/DepositsPanel';
import { CargoForm, ChargesCard, HistoryCard, Pipeline, Row, StatusChanger, clientName } from '@/Components/Operations/CargoParts';
import { ArrowLeft, Pencil, Package, Container as ContainerIcon, ExternalLink, Trash2, FileText } from 'lucide-react';
import { CLEARING_STATUS_COLORS, CONTAINER_STATUS_COLORS, formatDate } from '@/lib/operations';
import { useCan } from '@/lib/access';
import { formatMoney, cn } from '@/lib/utils';

function AddContainers({ jobId }) {
    const { data, setData, post, processing, errors, reset } = useForm({ containers: '' });
    return (
        <form onSubmit={(e) => { e.preventDefault(); post(route('operations.clearing.add-container', jobId), { preserveScroll: true, onSuccess: () => reset() }); }}
            className="flex gap-2 pt-3 border-t border-linen">
            <div className="flex-1">
                <Input value={data.containers} onChange={(e) => setData('containers', e.target.value)} placeholder="MSCU1234565 40HC, TGHU1234567" className="bg-cream border-linen font-mono" />
                {errors.containers && <p className="text-xs text-red-600 mt-1">{errors.containers}</p>}
            </div>
            <Button type="submit" disabled={processing} variant="outline" className="border-linen">Add</Button>
        </form>
    );
}

export default function Show({ job, statuses, money, chargeCategories, documents, clearing, deposits, formOptions }) {
    const can = useCan();
    const [formOpen, setFormOpen] = useState(false);
    const containers = job.cargo_containers || [];

    return (
        <AppLayout>
            <Head title={`${job.tracking_id} · ${job.cargo_description || 'Clearing job'}`} />

            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-6 max-w-7xl mx-auto">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('operations.clearing.index')}><Button variant="outline" size="icon" className="border-linen"><ArrowLeft className="h-4 w-4" /></Button></Link>
                        <div>
                            <div className="flex items-center gap-3 flex-wrap">
                                <h1 className="text-display-sm text-void font-bold">{job.cargo_description || 'Clearing job'}</h1>
                                <span className="font-mono text-sm px-2 py-0.5 rounded bg-cream border border-linen">{job.tracking_id}</span>
                                <span className={cn('text-xs px-2 py-1 rounded-full font-medium', CLEARING_STATUS_COLORS[job.status])}>{job.status_label}</span>
                            </div>
                            <p className="text-drift text-sm mt-1">{clientName(job.client) || job.consignee || 'No client yet'}{job.bl_number ? <> · BL <span className="font-mono">{job.bl_number}</span></> : null}</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {can('cargo.manage') && <Button variant="outline" className="border-linen" onClick={() => setFormOpen(true)}><Pencil className="mr-2 h-4 w-4" /> Edit</Button>}
                        {can('records.delete') && <Button variant="outline" className="border-linen text-red-600" onClick={() => confirm('Delete this clearing job and its containers?') && router.delete(route('operations.clearing.destroy', job.id))}><Trash2 className="h-4 w-4" /></Button>}
                    </div>
                </div>

                <Card className="bg-white border-linen shadow-sm">
                    <CardContent className="p-5 space-y-4">
                        <Pipeline statuses={statuses} status={job.status} milestones={job.milestones} />
                        {can('cargo.status') && <StatusChanger routeName="operations.clearing.update-status" id={job.id} status={job.status} statuses={statuses} />}
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card className="bg-white border-linen shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><Package className="h-5 w-5" /> Cargo</CardTitle></CardHeader>
                                <CardContent className="p-5 divide-y divide-linen">
                                    <Row label="Description">{job.cargo_description}</Row>
                                    <Row label="HS code">{job.hs_code}</Row>
                                    <Row label="Packages">{job.package_count ? `${job.package_count} ${job.package_type || ''}` : null}</Row>
                                    <Row label="Gross weight">{job.gross_weight_kg ? `${Number(job.gross_weight_kg).toLocaleString()} kg` : null}</Row>
                                    <Row label="Volume">{job.volume_cbm ? `${Number(job.volume_cbm)} CBM` : null}</Row>
                                    {'declared_value' in job && <Row label="Declared value">{job.declared_value ? formatMoney(job.declared_value, job.declared_currency || 'USD') : null}</Row>}
                                    <Row label="Client">{clientName(job.client)}</Row>
                                    <Row label="Consignee">{job.consignee}</Row>
                                    <Row label="Handled by">{job.assignee?.name}</Row>
                                </CardContent>
                            </Card>
                            <Card className="bg-white border-linen shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><FileText className="h-5 w-5" /> Customs</CardTitle></CardHeader>
                                <CardContent className="p-5 divide-y divide-linen">
                                    <Row label="Bill of Lading"><span className="font-mono">{job.bl_number}</span></Row>
                                    <Row label="Form M">{job.form_m_number}</Row>
                                    <Row label="PAAR">{job.paar_number}</Row>
                                    <Row label="Customs entry (SGD)">{job.customs_entry_number}</Row>
                                    <Row label="Terminal">{job.terminal}</Row>
                                    {'duty_assessed' in job && <Row label="Duty assessed">{job.duty_assessed ? formatMoney(job.duty_assessed, 'NGN') : null}</Row>}
                                    <Row label="ETA">{job.eta ? formatDate(job.eta) : null}</Row>
                                    <Row label="Arrived">{job.arrived_on ? formatDate(job.arrived_on) : null}</Row>
                                    <Row label="Duty paid">{job.duty_paid_on ? formatDate(job.duty_paid_on) : null}</Row>
                                    <Row label="Released">{job.released_on ? formatDate(job.released_on) : null}</Row>
                                    <Row label="Delivered">{job.delivered_on ? formatDate(job.delivered_on) : null}</Row>
                                    <Row label="Empty returned">{job.empty_returned_on ? formatDate(job.empty_returned_on) : null}</Row>
                                </CardContent>
                            </Card>
                        </div>

                        <Card className="bg-white border-linen shadow-sm">
                            <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><ContainerIcon className="h-5 w-5" /> Containers ({containers.length})</CardTitle></CardHeader>
                            <CardContent className="p-5 space-y-2">
                                {containers.map((c) => (
                                    <div key={c.id} className="flex items-center justify-between gap-2 text-sm">
                                        <span className="font-mono">{c.container_number}{c.size_type ? <span className="text-pebble"> · {c.size_type}</span> : null}{!c.number_is_valid && <span className="ml-2 text-xs text-amber-700">check digit?</span>}</span>
                                        <span className="flex items-center gap-2">
                                            <span className="text-xs text-pebble">{c.shipping_line?.name}</span>
                                            <span className={cn('text-xs px-2 py-0.5 rounded-full', CONTAINER_STATUS_COLORS[c.status])}>{c.status_label}</span>
                                            {c.empty_returned_on && <span className="text-xs text-teal-700">empty back {formatDate(c.empty_returned_on)}</span>}
                                            {c.tracking_link && <a href={c.tracking_link} target="_blank" rel="noreferrer" className="text-sage"><ExternalLink className="h-3.5 w-3.5" /></a>}
                                            {can('cargo.manage') && <button type="button" onClick={() => confirm(`Remove ${c.container_number} from this job?`) && router.delete(route('operations.clearing.remove-container', [job.id, c.id]), { preserveScroll: true })} className="text-pebble hover:text-red-600"><Trash2 className="h-3.5 w-3.5" /></button>}
                                        </span>
                                    </div>
                                ))}
                                {!containers.length && <p className="text-sm text-pebble">No containers on this job yet.</p>}
                                {can('cargo.manage') && <AddContainers jobId={job.id} />}
                            </CardContent>
                        </Card>

                        {documents && <DocumentsPanel panel={documents} />}
                    </div>

                    <div className="space-y-6">
                        <ClearingOfficerPanel panel={clearing} />
                        <DepositsPanel panel={deposits} />
                        {money && <ChargesCard shipmentId={job.id} lines={job.charges} totals={money} categories={chargeCategories} defaultCurrency="NGN" />}
                        <HistoryCard milestones={job.milestones} statuses={statuses} />
                        {job.description && <Card className="bg-white border-linen shadow-sm"><CardContent className="p-5 text-sm whitespace-pre-line">{job.description}</CardContent></Card>}
                    </div>
                </div>
            </div>

            <CargoForm kind="clearing" open={formOpen} onOpenChange={setFormOpen} item={job} options={formOptions} />
        </AppLayout>
    );
}
