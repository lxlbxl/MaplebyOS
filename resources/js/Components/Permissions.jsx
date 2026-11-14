import { Checkbox } from '@/Components/ui/checkbox';
import { Lock } from 'lucide-react';

/**
 * Grouped checklist of permissions for a role.
 * value: array of permission names; grantable: names the current admin may change.
 */
export function RolePermissionPicker({ groups, value, onChange, grantable, locked = false }) {
    const selected = new Set(value);
    const canChange = (name) => !locked && grantable.includes(name);

    const toggle = (name, on) => {
        const next = new Set(selected);
        on ? next.add(name) : next.delete(name);
        onChange([...next]);
    };

    const toggleGroup = (perms, on) => {
        const next = new Set(selected);
        perms.filter((p) => canChange(p.name)).forEach((p) => (on ? next.add(p.name) : next.delete(p.name)));
        onChange([...next]);
    };

    return (
        <div className="space-y-4">
            {groups.map(({ group, permissions }) => {
                const changeable = permissions.filter((p) => canChange(p.name));
                const allOn = permissions.every((p) => selected.has(p.name));
                return (
                    <div key={group} className="rounded-xl border border-linen overflow-hidden">
                        <div className="flex items-center justify-between gap-3 bg-cream/60 px-4 py-2.5">
                            <h4 className="text-sm font-semibold text-charcoal">{group}</h4>
                            {changeable.length > 0 && (
                                <button
                                    type="button"
                                    onClick={() => toggleGroup(permissions, !allOn)}
                                    className="text-xs font-medium text-sage hover:underline"
                                >
                                    {allOn ? 'Clear all' : 'Select all'}
                                </button>
                            )}
                        </div>
                        <div className="divide-y divide-linen">
                            {permissions.map((p) => {
                                const disabled = !canChange(p.name);
                                return (
                                    <label
                                        key={p.name}
                                        className={`flex items-start gap-3 px-4 py-3 ${disabled ? 'opacity-70' : 'cursor-pointer hover:bg-cream/40'}`}
                                    >
                                        <Checkbox
                                            className="mt-0.5"
                                            checked={selected.has(p.name)}
                                            disabled={disabled}
                                            onCheckedChange={(on) => toggle(p.name, on === true)}
                                        />
                                        <span className="flex-1 min-w-0">
                                            <span className="block text-sm text-void">{p.label}</span>
                                            <span className="block text-xs text-drift font-mono">{p.name}</span>
                                        </span>
                                        {disabled && !locked && <Lock className="w-3.5 h-3.5 text-drift mt-1" title="You don't have this permission yourself" />}
                                    </label>
                                );
                            })}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

const STATES = [
    { id: 'role', label: 'Role' },
    { id: 'allow', label: 'Allow' },
    { id: 'deny', label: 'Deny' },
];

/**
 * Per-user overrides on top of the role: each permission is either left to the
 * role, explicitly allowed, or explicitly denied.
 */
export function UserPermissionOverrides({ groups, rolePermissions, allow, deny, onChange, grantable, disabled = false }) {
    const fromRole = new Set(rolePermissions);
    const allowSet = new Set(allow);
    const denySet = new Set(deny);

    const stateOf = (name) => (allowSet.has(name) ? 'allow' : denySet.has(name) ? 'deny' : 'role');

    const set = (name, state) => {
        const a = new Set(allowSet);
        const d = new Set(denySet);
        a.delete(name);
        d.delete(name);
        if (state === 'allow') a.add(name);
        if (state === 'deny') d.add(name);
        onChange({ allow: [...a], deny: [...d] });
    };

    const overrides = allowSet.size + denySet.size;

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <p className="text-sm text-drift">
                    Leave on <b>Role</b> to follow the role. <b>Allow</b> adds a permission the role doesn't have; <b>Deny</b> takes one away for this person only.
                </p>
                {overrides > 0 && !disabled && (
                    <button type="button" onClick={() => onChange({ allow: [], deny: [] })} className="text-xs font-medium text-sage hover:underline">
                        Reset all to role ({overrides})
                    </button>
                )}
            </div>
            {groups.map(({ group, permissions }) => (
                <div key={group} className="rounded-xl border border-linen overflow-hidden">
                    <div className="bg-cream/60 px-4 py-2.5">
                        <h4 className="text-sm font-semibold text-charcoal">{group}</h4>
                    </div>
                    <div className="divide-y divide-linen">
                        {permissions.map((p) => {
                            const state = stateOf(p.name);
                            const effective = state === 'allow' || (state === 'role' && fromRole.has(p.name));
                            const locked = disabled || !grantable.includes(p.name);
                            return (
                                <div key={p.name} className="flex flex-col sm:flex-row sm:items-center gap-2 px-4 py-3">
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2">
                                            <span
                                                className={`inline-block w-2 h-2 rounded-full shrink-0 ${effective ? 'bg-emerald-500' : 'bg-gray-300'}`}
                                                title={effective ? 'Has this permission' : 'Does not have this permission'}
                                            />
                                            <span className="text-sm text-void">{p.label}</span>
                                        </div>
                                        <span className="block text-xs text-drift pl-4">
                                            Role: {fromRole.has(p.name) ? 'yes' : 'no'}
                                            {state !== 'role' && <> · <b className={state === 'allow' ? 'text-emerald-700' : 'text-red-600'}>{state === 'allow' ? 'allowed for this person' : 'denied for this person'}</b></>}
                                        </span>
                                    </div>
                                    <div className={`inline-flex rounded-lg border border-linen p-0.5 bg-white self-start sm:self-auto ${locked ? 'opacity-60' : ''}`}>
                                        {STATES.map((s) => (
                                            <button
                                                key={s.id}
                                                type="button"
                                                disabled={locked}
                                                onClick={() => set(p.name, s.id)}
                                                className={`px-3 py-1 text-xs font-medium rounded-md transition-colors ${
                                                    state === s.id
                                                        ? s.id === 'allow'
                                                            ? 'bg-emerald-600 text-white'
                                                            : s.id === 'deny'
                                                              ? 'bg-red-600 text-white'
                                                              : 'bg-charcoal text-white'
                                                        : 'text-drift hover:text-void'
                                                }`}
                                            >
                                                {s.label}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            ))}
        </div>
    );
}
