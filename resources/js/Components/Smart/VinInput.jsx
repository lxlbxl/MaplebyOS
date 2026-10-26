import { useEffect, useRef, useState } from 'react';
import axios from 'axios';
import { Link } from '@inertiajs/react';
import { Input } from '@/Components/ui/input';
import { cn } from '@/lib/utils';
import { AlertTriangle, CheckCircle2, Loader2, Wand2 } from 'lucide-react';

/**
 * VIN field with live checks: 17 characters, no I/O/Q, check digit, duplicate
 * VIN in the system, and decoding (year/make offline, model via NHTSA vPIC).
 */
export default function VinInput({ value, onChange, onDecoded, ignoreId, error }) {
    const [check, setCheck] = useState(null);
    const [loading, setLoading] = useState(false);
    const lastDecoded = useRef(null);

    const clean = (v) => String(v || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    const vin = clean(value);

    useEffect(() => {
        if (vin.length < 11) {
            setCheck(null);
            return;
        }
        const t = setTimeout(async () => {
            setLoading(true);
            try {
                const res = await axios.get(route('operations.vin.check', vin), { params: { ignore: ignoreId } });
                setCheck(res.data);
                if (res.data.valid && vin.length === 17 && lastDecoded.current !== vin) {
                    lastDecoded.current = vin;
                    let model = null;
                    let make = res.data.make;
                    let year = res.data.year;
                    try {
                        const ctrl = new AbortController();
                        const timer = setTimeout(() => ctrl.abort(), 4000);
                        const r = await fetch(`https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/${vin}?format=json`, { signal: ctrl.signal });
                        clearTimeout(timer);
                        const row = (await r.json())?.Results?.[0];
                        if (row) {
                            model = row.Model || null;
                            make = make || row.Make || null;
                            year = year || (row.ModelYear ? Number(row.ModelYear) : null);
                        }
                    } catch (e) {
                        // Offline or blocked: offline decode is enough.
                    }
                    onDecoded?.({ year, make, model });
                }
            } catch (e) {
                setCheck(null);
            } finally {
                setLoading(false);
            }
        }, 300);
        return () => clearTimeout(t);
    }, [vin]);

    const hasError = (check && !check.valid && vin.length >= 17) || error || check?.existing;

    return (
        <div className="space-y-1">
            <div className="relative">
                <Input
                    value={value || ''}
                    onChange={(e) => onChange(clean(e.target.value).slice(0, 18))}
                    placeholder="17-character VIN"
                    autoComplete="off"
                    className={cn('bg-cream border-linen text-charcoal h-11 rounded-lg font-mono uppercase tracking-wider pr-16', hasError && 'border-red-400')}
                />
                <span className={cn('absolute right-3 top-1/2 -translate-y-1/2 text-xs font-mono', vin.length === 17 ? 'text-sage' : 'text-pebble')}>
                    {loading ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : `${vin.length}/17`}
                </span>
            </div>

            {check?.existing && (
                <p className="text-xs text-red-600 flex items-center gap-1">
                    <AlertTriangle className="h-3 w-3" /> Already in the system as{' '}
                    <Link href={route('operations.shipments.show', check.existing.id)} className="underline">{check.existing.tracking_id}</Link>
                    {' '}({check.existing.title}).
                </p>
            )}
            {vin.length >= 17 && check?.errors?.map((e) => (
                <p key={e} className="text-xs text-red-600 flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> {e}</p>
            ))}
            {check?.suggestion && (
                <button type="button" onClick={() => onChange(check.suggestion)} className="text-xs text-sage flex items-center gap-1 hover:underline">
                    <Wand2 className="h-3 w-3" /> Use {check.suggestion}
                </button>
            )}
            {check?.warnings?.map((w) => (
                <p key={w} className="text-xs text-amber-600 flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> {w}</p>
            ))}
            {check?.valid && vin.length === 17 && !check.warnings?.length && !check.existing && (
                <p className="text-xs text-sage flex items-center gap-1">
                    <CheckCircle2 className="h-3 w-3" /> Valid VIN{check.year ? ` · ${check.year}` : ''}{check.make ? ` ${check.make}` : ''}
                </p>
            )}
            {error && <p className="text-xs text-red-600">{error}</p>}
        </div>
    );
}
