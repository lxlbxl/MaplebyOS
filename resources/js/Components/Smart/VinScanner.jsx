import { useEffect, useRef, useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { ScanLine } from 'lucide-react';

const VIN_RE = /[A-HJ-NPR-Z0-9]{17}/;

/** Pull a 17-character VIN out of a barcode payload (door-jamb codes often add a leading I or extra data). */
export function extractVin(text) {
    const cleaned = String(text || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    const m = cleaned.match(VIN_RE);
    if (m) return m[0];
    // Code 39 VIN labels sometimes start with "I" as an import marker.
    if (cleaned.length === 18 && cleaned.startsWith('I')) return cleaned.slice(1);
    return null;
}

/**
 * Reads the VIN barcode (windshield / door jamb) with the rear camera.
 * Uses the browser's BarcodeDetector where it exists (Chrome on Android) and
 * falls back to ZXing (loaded only when needed) on iPhone and elsewhere.
 */
export default function VinScanner({ open, onOpenChange, onVin }) {
    const videoRef = useRef(null);
    const [error, setError] = useState(null);
    const [lastRead, setLastRead] = useState(null);

    useEffect(() => {
        if (!open) return undefined;
        let stopped = false;
        let stream = null;
        let controls = null;
        let timer = null;
        setError(null);
        setLastRead(null);

        const found = (raw) => {
            const vin = extractVin(raw);
            if (vin) {
                stopped = true;
                if (navigator.vibrate) navigator.vibrate(60);
                onVin(vin);
                onOpenChange(false);
            } else if (raw) {
                setLastRead(String(raw).slice(0, 40));
            }
        };

        (async () => {
            try {
                if (!navigator.mediaDevices?.getUserMedia) throw new Error('This browser cannot use the camera. Type the VIN instead.');

                if ('BarcodeDetector' in window) {
                    const detector = new window.BarcodeDetector({ formats: ['code_39', 'code_128', 'data_matrix', 'qr_code', 'pdf417'] });
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
                    if (stopped) return;
                    videoRef.current.srcObject = stream;
                    await videoRef.current.play();
                    const tick = async () => {
                        if (stopped) return;
                        try {
                            const codes = await detector.detect(videoRef.current);
                            codes.forEach((c) => found(c.rawValue));
                        } catch (e) { /* frame not ready */ }
                        if (!stopped) timer = setTimeout(tick, 250);
                    };
                    tick();
                } else {
                    const { BrowserMultiFormatReader } = await import('@zxing/browser');
                    if (stopped) return;
                    const reader = new BrowserMultiFormatReader();
                    controls = await reader.decodeFromConstraints(
                        { video: { facingMode: { ideal: 'environment' } }, audio: false },
                        videoRef.current,
                        (result) => { if (result && !stopped) found(result.getText()); },
                    );
                }
            } catch (e) {
                setError(e?.name === 'NotAllowedError'
                    ? 'Camera permission was refused. Allow the camera for this site in your browser settings, or type the VIN.'
                    : (e?.message || 'Could not start the camera.'));
            }
        })();

        return () => {
            stopped = true;
            clearTimeout(timer);
            controls?.stop();
            stream?.getTracks().forEach((t) => t.stop());
        };
    }, [open]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="bg-white max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2"><ScanLine className="h-5 w-5" /> Scan VIN barcode</DialogTitle>
                    <DialogDescription>Point the camera at the VIN barcode on the windshield or door frame.</DialogDescription>
                </DialogHeader>
                <div className="relative overflow-hidden rounded-xl bg-black aspect-[4/3]">
                    <video ref={videoRef} className="h-full w-full object-cover" muted playsInline />
                    <div className="pointer-events-none absolute inset-x-6 top-1/2 h-16 -translate-y-1/2 rounded-lg border-2 border-white/80" />
                </div>
                {lastRead && <p className="text-xs text-pebble">Read “{lastRead}”, which is not a VIN. Keep the VIN barcode in the box.</p>}
                {error && <p className="text-sm text-red-600">{error}</p>}
                <Button variant="outline" onClick={() => onOpenChange(false)} className="border-linen">Type it instead</Button>
            </DialogContent>
        </Dialog>
    );
}
