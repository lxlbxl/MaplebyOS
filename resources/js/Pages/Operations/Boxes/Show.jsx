import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import DocumentsPanel from '@/Components/Operations/DocumentsPanel';
import { CargoForm, ChargesCard, HistoryCard, Pipeline, Row, StatusChanger, clientName } from '@/Components/Operations/CargoParts';
import { ArrowLeft, Pencil, Package, Container as ContainerIcon, ExternalLink, Trash2 } from 'lucide-react';
import { VEHICLE_STATUS_COLORS, CONTAINER_STATUS_COLORS, formatDate } from '@/lib/operations';
import { useCan } from '@/lib/access';
import { formatMoney, cn } from '@/lib/utils';

export default function Show({ box, statuses, trackingUrl, money, chargeCategories, documents, formOptions }) {
    const can = useCan();
    const { props } = usePage();
    const currency = props.settings?.currency || 'USD';
    const [formOpen, setFormOpen] = useState(false);
    const container = box.container;

    return (
        <AppLayout>
            <Head title={`${box.tracking_id} · ${box.cargo_description || 'Box'}`} />

            <div className="p-4 sm:p-6 lg:p-8 flex flex-col gap-6 max-w-7xl mx-auto">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('operations.boxes.index')}><Button variant="outline" size="icon" className="border-linen"><ArrowLeft className="h-4 w-4" /></Button></Link>
                        <div>
                            <div className="flex items-center gap-3 flex-wrap">
                                <h1 className="text-display-sm text-void font-bold">{box.cargo_description || 'Box'}</h1>
                                <span className="font-mono text-sm px-2 py-0.5 rounded bg-cream border border-linen">{box.tracking_id}</span>
                                <span className={cn('text-xs px-2 py-1 rounded-full font-medium', VEHICLE_STATUS_COLORS[box.status])}>{box.status_label}</span>
                            </div>
                            <p className="text-drift text-sm mt-1">{clientName(box.client) || box.consignee || 'No owner yet'}</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {can('cargo.manage') && <Button variant="outline" className="border-linen" onClick={() => setFormOpen(true)}><Pencil className="mr-2 h-4 w-4" /> Edit</Button>}
                        {can('records.delete') && <Button variant="outline" className="border-linen text-red-600" onClick={() => confirm('Delete this box?') && router.delete(route('operations.boxes.destroy', box.id))}><Trash2 className="h-4 w-4" /></Button>}
                    </div>
                </div>

                <Card className="bg-white border-linen shadow-sm">
                    <CardContent className="p-5 space-y-4">
                        <Pipeline statuses={statuses} status={box.status} milestones={box.milestones} />
                        <p className="text-xs text-pebble">Loading, sailing, arrival, offloading and customs release are set on the container and copied here automatically.</p>
                        {can('cargo.status') && <StatusChanger routeName="operations.boxes.update-status" id={box.id} status={box.status} statuses={statuses} />}
                    </CardContent>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card className="bg-white border-linen shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><Package className="h-5 w-5" /> Box</CardTitle></CardHeader>
                                <CardContent className="p-5 divide-y divide-linen">
                                    <Row label="Contents">{box.cargo_description}</Row>
                                    <Row label="Packages">{box.package_count ? `${box.package_count} ${box.package_type || ''}` : null}</Row>
                                    <Row label="Weight">{box.gross_weight_kg ? `${Number(box.gross_weight_kg)} kg` : null}</Row>
                                    <Row label="Volume">{box.volume_cbm ? `${Number(box.volume_cbm)} CBM` : null}</Row>
                                    {'declared_value' in box && <Row label="Declared value">{box.declared_value ? formatMoney(box.declared_value, box.declared_currency || 'USD') : null}</Row>}
                                    <Row label="Owner">{clientName(box.client)}</Row>
                                    <Row label="Receiver">{box.consignee}</Row>
                                    <Row label="Destination">{box.destination_city}</Row>
                                    <Row label="Added">{formatDate(box.date_added)}</Row>
                                    <Row label="Delivered">{box.delivered_on ? formatDate(box.delivered_on) : null}</Row>
                                </CardContent>
                            </Card>
                            <Card className="bg-white border-linen shadow-sm">
                                <CardHeader className="border-b border-linen"><CardTitle className="text-void flex items-center gap-2"><ContainerIcon className="h-5 w-5" /> Container</CardTitle></CardHeader>
                                <CardContent className="p-5">
                                    {container ? (
                                        <div className="divide-y divide-linen">
                                            <Row label="Container">
                                                <Link href={route('operations.containers.show', container.id)} className="font-mono text-sage">{container.container_number}</Link>
                                                {trackingUrl && <a href={trackingUrl} target="_blank" rel="noreferrer" className="ml-2 inline-block text-sage"><ExternalLink className="h-3.5 w-3.5" /></a>}
                                            </Row>
                                            <Row label="Status"><span className={cn('text-xs px-2 py-0.5 rounded-full', CONTAINER_STATUS_COLORS[container.status])}>{container.status_label}</span></Row>
                                            <Row label="Line">{container.shipping_line?.name}</Row>
                                            <Row label="Loaded">{container.loaded_on ? formatDate(container.loaded_on) : null}</Row>
                                            <Row label="ETA">{container.eta ? formatDate(container.eta) : null}</Row>
                                            <Row label="Offloaded">{container.offloaded_on ? formatDate(container.offloaded_on) : null}</Row>
                                            <Row label="Released">{container.released_on ? formatDate(container.released_on) : null}</Row>
                                        </div>
                                    ) : <p className="text-sm text-pebble">Not in a container yet. Edit the box to pick its car container.</p>}
                                </CardContent>
                            </Card>
                        </div>
                        {documents && <DocumentsPanel panel={documents} />}
                    </div>

                    <div className="space-y-6">
                        {money && <ChargesCard shipmentId={box.id} lines={box.charges} totals={money} categories={chargeCategories} defaultCurrency={currency} />}
                        <HistoryCard milestones={box.milestones} statuses={statuses} />
                        {box.description && <Card className="bg-white border-linen shadow-sm"><CardContent className="p-5 text-sm whitespace-pre-line">{box.description}</CardContent></Card>}
                    </div>
                </div>
            </div>

            <CargoForm kind="box" open={formOpen} onOpenChange={setFormOpen} item={box} options={formOptions} />
        </AppLayout>
    );
}
