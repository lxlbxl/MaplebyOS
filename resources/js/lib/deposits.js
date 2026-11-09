// Labels and colours for container deposit refunds.

export const REFUND_STATUS_COLORS = {
    awaiting_empty: 'bg-linen text-stone',
    to_claim: 'bg-amber-50 text-amber-700',
    claimed: 'bg-sky-50 text-sky-700',
    part_received: 'bg-indigo-50 text-indigo-700',
    received: 'bg-teal-50 text-teal-700',
    verified: 'bg-emerald-50 text-emerald-700',
    disputed: 'bg-red-50 text-red-700',
    written_off: 'bg-gray-100 text-gray-600',
};

export function money(amount, currency = 'NGN') {
    try {
        return new Intl.NumberFormat('en-NG', { style: 'currency', currency: currency || 'NGN', maximumFractionDigits: 2 }).format(Number(amount || 0));
    } catch (e) {
        return `${currency} ${Number(amount || 0).toLocaleString()}`;
    }
}

export function formatDateTime(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
