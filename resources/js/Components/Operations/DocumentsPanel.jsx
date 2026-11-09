import { useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Camera, Upload, FileText, Image as ImageIcon, Trash2, AlertTriangle, Lock, Paperclip } from 'lucide-react';
import { useCan } from '@/lib/access';
import { cn } from '@/lib/utils';

function DocRow({ doc, canDelete }) {
    const Icon = doc.is_image ? ImageIcon : FileText;
    return (
        <div className="flex items-center gap-3 py-2">
            <a href={doc.url} target="_blank" rel="noreferrer" className="flex items-center gap-3 min-w-0 flex-1 group">
                {doc.is_image ? (
                    <img src={doc.url} alt="" loading="lazy" className="h-11 w-11 rounded-md object-cover border border-linen shrink-0" />
                ) : (
                    <span className="h-11 w-11 rounded-md bg-cream border border-linen flex items-center justify-center shrink-0"><Icon className="h-5 w-5 text-stone" /></span>
                )}
                <span className="min-w-0">
                    <span className="block text-sm font-medium text-void group-hover:text-sage truncate">
                        {doc.type_label} {doc.sensitive && <Lock className="inline h-3 w-3 text-pebble" />}
                    </span>
                    <span className="block text-xs text-pebble truncate">
                        {doc.original_name} · {new Date(doc.created_at).toLocaleDateString()}{doc.uploader ? ` · ${doc.uploader.name}` : ''}
                    </span>
                    {doc.notes && <span className="block text-xs text-stone truncate">{doc.notes}</span>}
                </span>
            </a>
            {canDelete && (
                <button type="button" title="Remove"
                    onClick={() => confirm(`Remove ${doc.type_label}?`) && router.delete(route('documents.destroy', doc.id), { preserveScroll: true })}
                    className="p-2 text-pebble hover:text-red-600"><Trash2 className="h-4 w-4" /></button>
            )}
        </div>
    );
}

/**
 * Scan (phone camera) or upload papers for a car or container, tagged by type
 * and stage. `panel` comes from DocumentController::panel().
 */
export default function DocumentsPanel({ panel }) {
    const can = useCan();
    const [type, setType] = useState(panel?.missing?.[0]?.type || panel?.types?.[0]?.value || 'other');
    const [notes, setNotes] = useState('');
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState(null);
    const scanRef = useRef(null);
    const uploadRef = useRef(null);

    if (!panel) return null;
    const canUpload = can('documents.upload');
    const canDelete = can('records.delete');
    const selected = panel.types.find((t) => t.value === type);

    const send = (fileList) => {
        const files = Array.from(fileList || []);
        if (!files.length) return;
        setError(null);
        setBusy(true);
        router.post(route('documents.store'), {
            documentable: panel.kind,
            documentable_id: panel.id,
            type,
            notes,
            files,
        }, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => setNotes(''),
            onError: (errs) => setError(Object.values(errs)[0]),
            onFinish: () => {
                setBusy(false);
                if (scanRef.current) scanRef.current.value = '';
                if (uploadRef.current) uploadRef.current.value = '';
            },
        });
    };

    return (
        <Card className="bg-white border-linen shadow-sm">
            <CardHeader className="border-b border-linen">
                <CardTitle className="text-void flex items-center gap-2"><Paperclip className="h-5 w-5" /> Documents</CardTitle>
            </CardHeader>
            <CardContent className="p-5 space-y-4">
                {panel.missing.length > 0 && (
                    <div className="rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm text-amber-800">
                        <p className="font-medium flex items-center gap-1"><AlertTriangle className="h-4 w-4" /> Still needed for this stage</p>
                        <div className="mt-2 flex flex-wrap gap-2">
                            {panel.missing.map((m) => (
                                <button key={m.type} type="button" onClick={() => setType(m.type)}
                                    className={cn('text-xs px-2 py-1 rounded-full border', type === m.type ? 'bg-amber-200 border-amber-300' : 'bg-white border-amber-200')}>
                                    {m.label}
                                </button>
                            ))}
                        </div>
                    </div>
                )}

                {canUpload && (
                    <div className="space-y-2">
                        <Select value={type} onValueChange={setType}>
                            <SelectTrigger className="bg-cream border-linen h-11"><SelectValue placeholder="What is it?" /></SelectTrigger>
                            <SelectContent className="bg-white">
                                {panel.types.map((t) => (
                                    <SelectItem key={t.value} value={t.value}>{t.label}{t.stage_label ? ` · ${t.stage_label}` : ''}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Input value={notes} onChange={(e) => setNotes(e.target.value)} placeholder="Note (optional), e.g. BL number" className="bg-cream border-linen" />
                        <div className="grid grid-cols-2 gap-2">
                            <Button type="button" disabled={busy} onClick={() => scanRef.current?.click()} className="btn-sage h-12">
                                <Camera className="mr-2 h-5 w-5" /> Scan
                            </Button>
                            <Button type="button" disabled={busy} variant="outline" onClick={() => uploadRef.current?.click()} className="border-linen h-12">
                                <Upload className="mr-2 h-5 w-5" /> Upload
                            </Button>
                        </div>
                        {/* capture opens the rear camera on phones; on desktop it falls back to a file picker. */}
                        <input ref={scanRef} type="file" accept="image/*" capture="environment" className="hidden" onChange={(e) => send(e.target.files)} />
                        <input ref={uploadRef} type="file" accept="image/*,application/pdf" multiple className="hidden" onChange={(e) => send(e.target.files)} />
                        {busy && <p className="text-xs text-pebble">Uploading {selected?.label || 'document'}…</p>}
                        {error && <p className="text-xs text-red-600">{error}</p>}
                    </div>
                )}

                <div className="divide-y divide-linen">
                    {panel.documents.map((d) => <DocRow key={d.id} doc={d} canDelete={canDelete} />)}
                    {panel.documents.length === 0 && <p className="py-2 text-sm text-pebble">No documents yet.</p>}
                </div>

                {panel.container_documents?.length > 0 && (
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wide text-pebble mt-2">From container {panel.container_number}</p>
                        <div className="divide-y divide-linen">
                            {panel.container_documents.map((d) => <DocRow key={d.id} doc={d} canDelete={false} />)}
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
