import {
    LayoutDashboard, Users, Sparkles, Car, Container, FileText, Package, Receipt, DollarSign,
    BookOpen, ShieldCheck, Settings, User, PlusCircle, Boxes, Anchor, Landmark, UserCheck, KeyRound } from 'lucide-react';

/**
 * One list of screens, each with the permission it needs. Routes that a
 * branch has not added yet are skipped (route().has), so nav never breaks.
 */
export const NAV_SECTIONS = [
    {
        title: 'Overview',
        items: [{ label: 'Home', route: 'dashboard', active: 'dashboard', icon: LayoutDashboard }],
    },
    {
        title: 'Business',
        items: [
            { label: 'Customers', route: 'crm.clients.index', active: 'crm.clients.*', icon: Users, perm: 'customers.view' },
            { label: 'Leads', route: 'crm.leads.index', active: 'crm.leads.*', icon: Sparkles, perm: 'leads.manage' },
            { label: 'Quotes', route: 'operations.quotes.index', active: 'operations.quotes.*', icon: FileText, perm: 'quotes.manage' },
            { label: 'Cars for sale', route: 'inventory.vehicles.index', active: 'inventory.*', icon: Package, perm: 'inventory.manage' },
        ],
    },
    {
        title: 'Operations',
        items: [
            { label: 'Vehicles', route: 'operations.shipments.index', active: 'operations.shipments.*', icon: Car, perm: 'vehicles.view' },
            { label: 'Containers', route: 'operations.containers.index', active: 'operations.containers.*', icon: Container, perm: 'containers.view' },
            { label: 'Clearing', route: 'operations.clearing.index', active: 'operations.clearing.*', icon: Anchor, perm: 'cargo.view' },
            { label: 'Small cargo', route: 'operations.boxes.index', active: 'operations.boxes.*', icon: Boxes, perm: 'cargo.view' },
            { label: 'Deposits', route: 'operations.deposits.index', active: 'operations.deposits.*', icon: Landmark, perm: 'deposits.view' },
            { label: 'Clearing officers', route: 'operations.clearing-officers.index', active: 'operations.clearing-officers.*', icon: UserCheck, perm: 'clearing.view' },
        ],
    },
    {
        title: 'Finance',
        items: [
            { label: 'Invoices', route: 'finance.invoices.index', active: 'finance.invoices.*', icon: Receipt, perm: 'finance.manage' },
            { label: 'Payments', route: 'finance.payments.index', active: 'finance.payments.*', icon: DollarSign, perm: 'finance.manage' },
        ],
    },
    {
        title: 'System',
        items: [
            { label: 'Catalog', route: 'operations.catalog.index', active: 'operations.catalog.*', icon: BookOpen, perm: 'catalog.manage' },
            { label: 'Staff', route: 'users.index', active: 'users.*', icon: ShieldCheck, perm: 'manage_users' },
            { label: 'Roles & permissions', route: 'roles.index', active: 'roles.*', icon: KeyRound, perm: 'manage_roles' },
        ],
    },
];

export const BOTTOM_ITEMS = [
    { label: 'Settings', route: 'settings.index', active: 'settings.*', icon: Settings, perm: 'manage_settings' },
    { label: 'Profile', route: 'profile.edit', active: 'profile.*', icon: User },
];

const ALL = Object.fromEntries(
    [...NAV_SECTIONS.flatMap((s) => s.items), ...BOTTOM_ITEMS].map((i) => [i.route, i]),
);

const ADD_CAR = { label: 'Add car', route: 'operations.shipments.index', params: { create: 'true' }, active: '__add', icon: PlusCircle, perm: 'vehicles.create', primary: true };

/** Bottom tab bar on phones: the 4–5 things each role does most. */
export const MOBILE_TABS = {
    ceo: ['dashboard', 'operations.shipments.index', 'operations.containers.index', 'finance.invoices.index', 'users.index'],
    business: ['dashboard', 'crm.clients.index', 'operations.quotes.index', 'finance.invoices.index', 'finance.payments.index'],
    logistics: ['dashboard', 'operations.containers.index', 'operations.shipments.index', 'operations.clearing.index', 'profile.edit'],
    puncher: ['dashboard', ADD_CAR, 'operations.shipments.index', 'operations.deposits.index', 'profile.edit'],
};

export function routeExists(name) {
    try {
        return route().has(name);
    } catch (e) {
        return false;
    }
}

export function allowed(item, can) {
    return (!item.perm || can(item.perm)) && routeExists(item.route);
}

export function mobileTabsFor(home, can) {
    return (MOBILE_TABS[home] || MOBILE_TABS.puncher)
        .map((t) => (typeof t === 'string' ? ALL[t] : t))
        .filter((t) => t && allowed(t, can));
}

export function isActivePattern(pattern, currentUrl) {
    if (!pattern || pattern.startsWith('__')) return false;
    if (pattern.endsWith('*')) {
        const prefix = pattern.slice(0, -1);
        return currentUrl.includes(prefix.replace(/\./g, '/'));
    }
    try {
        return currentUrl === route(pattern) || route().current(pattern);
    } catch (e) {
        return false;
    }
}
