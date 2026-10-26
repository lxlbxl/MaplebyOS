import { useEffect, useRef, useState } from 'react';
import axios from 'axios';
import { Input } from '@/Components/ui/input';
import { cn } from '@/lib/utils';
import { Check, AlertTriangle, Plus, Sparkles } from 'lucide-react';

/**
 * Text input backed by the canonical catalog. It suggests as you type, snaps
 * close misspellings to the canonical value when you leave the field, and only
 * lets you keep an unknown value by explicitly adding it to the catalog.
 *
 * For customers the stored value is the client id; `displayValue` is the label.
 */
export default function SmartInput({
    type,
    value,
    onChange,
    context = {},
    placeholder,
    displayValue,
    allowAdd = true,
    disabled = false,
    className,
    id,
    error,
    autoFocus = false,
}) {
    const isCustomer = type === 'customers';
    const [text, setText] = useState(displayValue ?? value ?? '');
    const [open, setOpen] = useState(false);
    const [items, setItems] = useState([]);
    const [highlight, setHighlight] = useState(0);
    const [notice, setNotice] = useState(null); // {kind: 'fixed'|'unknown'|'ambiguous', ...}
    const [adding, setAdding] = useState(false);
    const boxRef = useRef(null);
    const reqId = useRef(0);
    const contextKey = JSON.stringify(context);

    useEffect(() => {
        setText(displayValue ?? value ?? '');
    }, [value, displayValue]);

    // Clear a model when its make changes, etc.
    useEffect(() => {
        setNotice(null);
    }, [contextKey]);

    useEffect(() => {
        if (!open) return;
        const current = ++reqId.current;
        const t = setTimeout(() => {
            axios.get(route('operations.lookups.suggest', type), { params: { q: text, ...context } })
                .then((res) => {
                    if (current !== reqId.current) return;
                    setItems(res.data.suggestions || []);
                    setHighlight(0);
                })
                .catch(() => {});
        }, 150);
        return () => clearTimeout(t);
    }, [text, open, type, contextKey]);

    useEffect(() => {
        const onDoc = (e) => {
            if (boxRef.current && !boxRef.current.contains(e.target)) setOpen(false);
        };
        document.addEventListener('mousedown', onDoc);
        return () => document.removeEventListener('mousedown', onDoc);
    }, []);

    const choose = (item) => {
        setText(isCustomer ? item.label : item.value);
        onChange(isCustomer ? String(item.id) : item.value, item);
        setNotice(null);
        setOpen(false);
    };

    const settle = async () => {
        const typed = text.trim();
        if (!typed) {
            if (value) onChange(isCustomer ? '' : '', null);
            setNotice(null);
            return;
        }
        const current = isCustomer ? displayValue : value;
        if (typed === current) return;

        try {
            const res = await axios.get(route('operations.lookups.suggest', type), { params: { q: typed, ...context, limit: 5 } });
            const resolved = res.data.resolved;
            if (resolved?.value != null && ['exact', 'alias', 'typo'].includes(resolved.confidence)) {
                const match = resolved.match;
                const canonical = isCustomer ? match.label : resolved.value;
                setText(canonical);
                onChange(isCustomer ? String(match.id) : resolved.value, match);
                setNotice(resolved.confidence === 'exact' ? null : { kind: 'fixed', from: typed, to: canonical });
                return;
            }
            onChange(isCustomer ? '' : typed, null);
            setNotice({
                kind: resolved?.confidence === 'ambiguous' ? 'ambiguous' : 'unknown',
                typed,
                suggestions: resolved?.suggestions || res.data.suggestions || [],
            });
        } catch (e) {
            // Network hiccup: leave the text, the server will validate on save.
        }
    };

    const addToCatalog = async () => {
        const name = notice?.typed || text.trim();
        if (!name) return;
        if (isCustomer) {
            window.open(route('crm.clients.index') + '?create=true', '_blank');
            return;
        }
        if (!confirm(`Add "${name.toUpperCase()}" as a new ${type.replace('_', ' ').replace(/s$/, '')}? Only do this if it is not a misspelling.`)) return;
        setAdding(true);
        try {
            const res = await axios.post(route('operations.lookups.store', type), { name, ...context, force: true }, { headers: { Accept: 'application/json' } });
            choose({ id: res.data.entry.id, value: res.data.entry.name, label: res.data.entry.name });
        } catch (e) {
            const msg = e.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(' ') : 'Could not add it.';
            setNotice({ kind: 'unknown', typed: name, suggestions: [], message: msg });
        } finally {
            setAdding(false);
        }
    };

    const onKeyDown = (e) => {
        if (!open && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
            setOpen(true);
            return;
        }
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setHighlight((h) => Math.min(h + 1, items.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setHighlight((h) => Math.max(h - 1, 0));
        } else if (e.key === 'Enter' && open && items[highlight]) {
            e.preventDefault();
            choose(items[highlight]);
        } else if (e.key === 'Escape') {
            setOpen(false);
        } else if (e.key === 'Tab' && open && items[highlight] && text.trim()) {
            choose(items[highlight]);
        }
    };

    const invalid = notice && notice.kind !== 'fixed';

    return (
        <div className={cn('relative', className)} ref={boxRef}>
            <Input
                id={id}
                value={text}
                disabled={disabled}
                autoFocus={autoFocus}
                autoComplete="off"
                placeholder={placeholder}
                onFocus={() => setOpen(true)}
                onChange={(e) => {
                    setText(e.target.value);
                    setOpen(true);
                    setNotice(null);
                }}
                onBlur={() => setTimeout(() => { setOpen(false); settle(); }, 120)}
                onKeyDown={onKeyDown}
                className={cn(
                    'bg-cream border-linen text-charcoal h-11 rounded-lg',
                    !isCustomer && 'uppercase',
                    (invalid || error) && 'border-red-400 focus-visible:ring-red-300'
                )}
            />

            {open && items.length > 0 && (
                <ul className="absolute z-50 mt-1 w-full max-h-64 overflow-auto rounded-xl border border-linen bg-white shadow-float py-1">
                    {items.map((item, i) => (
                        <li
                            key={`${item.id}-${item.value}`}
                            onMouseDown={(e) => { e.preventDefault(); choose(item); }}
                            onMouseEnter={() => setHighlight(i)}
                            className={cn('px-3 py-2 text-sm cursor-pointer flex items-center justify-between gap-2', i === highlight ? 'bg-cream text-void' : 'text-charcoal')}
                        >
                            <span className="truncate">{item.label}</span>
                            <span className="flex items-center gap-1 text-[11px] text-pebble shrink-0">
                                {item.reason === 'typo' && <><Sparkles className="h-3 w-3" /> did you mean</>}
                                {item.reason === 'alias' && 'also known as'}
                                {item.meta?.make && !context.make && item.meta.make}
                                {item.meta?.country && item.meta.country}
                                {(isCustomer ? String(item.id) === String(value) : item.value === value) && <Check className="h-3.5 w-3.5 text-sage" />}
                            </span>
                        </li>
                    ))}
                </ul>
            )}

            {notice?.kind === 'fixed' && (
                <p className="mt-1 text-xs text-sage flex items-center gap-1">
                    <Sparkles className="h-3 w-3" /> Corrected “{notice.from}” to {notice.to}
                </p>
            )}

            {invalid && (
                <div className="mt-1 text-xs text-red-600 space-y-1">
                    <p className="flex items-center gap-1">
                        <AlertTriangle className="h-3 w-3" />
                        {notice.message || (notice.kind === 'ambiguous' ? `“${notice.typed}” could be several things.` : `“${notice.typed}” isn't in the list.`)}
                        {notice.suggestions?.length > 0 && ' Did you mean:'}
                    </p>
                    <div className="flex flex-wrap gap-1">
                        {notice.suggestions?.slice(0, 4).map((s) => (
                            <button type="button" key={`${s.id}-${s.value}`} onClick={() => choose(s)}
                                className="px-2 py-0.5 rounded-full bg-cream border border-linen text-charcoal hover:border-sage">
                                {s.label}
                            </button>
                        ))}
                        {allowAdd && (
                            <button type="button" onClick={addToCatalog} disabled={adding}
                                className="px-2 py-0.5 rounded-full border border-dashed border-pebble text-stone hover:border-sage flex items-center gap-1">
                                <Plus className="h-3 w-3" /> {isCustomer ? 'New customer' : `Add “${notice.typed.toUpperCase()}”`}
                            </button>
                        )}
                    </div>
                </div>
            )}

            {error && !invalid && <p className="mt-1 text-xs text-red-600">{error}</p>}
        </div>
    );
}
