// Shared labels and colours for vehicle and container statuses.

// The journey reads grey (Canada side) -> gold (in our hands) -> maple (at sea / Lagos)
// -> green (done), so colour alone tells you roughly where a car is.
export const VEHICLE_STATUS_COLORS = {
    added: 'bg-gray-100 text-gray-600',
    purchased: 'bg-gray-200 text-gray-800',
    picked_up: 'bg-amber-50 text-amber-700',
    at_yard: 'bg-amber-100 text-amber-800',
    loaded: 'bg-red-50 text-red-600',
    sailed: 'bg-red-100 text-red-700',
    arrived: 'bg-red-200 text-red-800',
    offloaded: 'bg-emerald-50 text-emerald-700',
    cleared: 'bg-emerald-100 text-emerald-800',
    delivered: 'bg-emerald-600 text-white',
    on_hold: 'bg-white text-gray-500 ring-1 ring-inset ring-gray-300',
    issue: 'bg-red-700 text-white',
};

export const CONTAINER_STATUS_COLORS = {
    planned: 'bg-gray-100 text-gray-600',
    loaded: 'bg-red-50 text-red-600',
    sailed: 'bg-red-100 text-red-700',
    arrived: 'bg-red-200 text-red-800',
    offloaded: 'bg-emerald-50 text-emerald-700',
    released: 'bg-emerald-600 text-white',
};

export function statusOptions(statuses) {
    return Object.entries(statuses || {}).map(([value, label]) => ({ value, label }));
}

export function formatDate(value, fallback = '—') {
    if (!value) return fallback;
    const [y, m, d] = String(value).slice(0, 10).split('-').map(Number);
    if (!y) return fallback;
    return new Date(y, m - 1, d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

export function todayIso() {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

/** ISO 6346 container check digit; mirrors ContainerNumber.php. */
export function containerNumberCheck(raw) {
    const n = String(raw || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    if (!/^[A-Z]{3}[UJZ]\d{7}$/.test(n)) return { clean: n, wellFormed: false, valid: false };
    const values = {};
    let v = 10;
    for (const letter of 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        if (v % 11 === 0) v++;
        values[letter] = v++;
    }
    let sum = 0;
    for (let i = 0; i < 10; i++) {
        const c = n[i];
        sum += (/\d/.test(c) ? Number(c) : values[c]) * 2 ** i;
    }
    return { clean: n, wellFormed: true, valid: (sum % 11) % 10 === Number(n[10]) };
}

export function guessLine(number, shippingLines = []) {
    const prefix = String(number || '').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 4);
    if (prefix.length < 4) return null;
    return shippingLines.find((l) => (l.container_prefixes || []).includes(prefix)) || null;
}
