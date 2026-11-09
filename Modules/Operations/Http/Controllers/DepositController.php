<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\ContainerDeposit;
use Modules\Operations\Entities\DepositFollowUp;
use Modules\Operations\Entities\DepositRefund;
use Modules\Operations\Entities\ShippingLine;
use Modules\Operations\Services\ClearingLedger;
use Modules\Operations\Services\ContainerNumber;
use Modules\Operations\Services\DepositLedger;

/**
 * Container deposits paid to shipping lines, and their refunds.
 *
 * Punchers / office support record deposits, chase refunds and update them.
 * The CEO and Head of Business oversee: they confirm refunds that landed,
 * mark disputes, write off what will not come back and decide who chases what.
 */
class DepositController extends Controller
{
    public const VIEWS = [
        'open' => 'Not refunded yet',
        'follow_up' => 'Follow-up due',
        'to_claim' => 'Claim not filed',
        'claimed' => 'Claim filed',
        'to_confirm' => 'Refunded, to confirm',
        'disputed' => 'Disputed',
        'closed' => 'Confirmed / written off',
        'all' => 'All',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $query = ContainerDeposit::query()
            ->with([
                'container:id,container_number,cargo_shipment_id,bl_number,status,released_on,empty_returned_on,shipping_line_id',
                'container.cargoShipment:id,tracking_id,cargo_description',
                'shippingLine:id,name',
                'refund.owner:id,name',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $compact = strtoupper(preg_replace('/\W/', '', $search));
            $query->where(function ($q) use ($search, $compact) {
                $q->where('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas('container', fn ($c) => $c->where('container_number', 'like', "%{$compact}%")->orWhere('bl_number', 'like', "%{$compact}%"))
                    ->orWhereHas('shippingLine', fn ($l) => $l->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('refund', fn ($r) => $r->where('claim_reference', 'like', "%{$search}%"));
            });
        }
        if ($request->input('owner') === 'mine') {
            $query->whereHas('refund', fn ($r) => $r->where('owner_id', $user->id));
        }

        $view = $request->input('view', 'open');
        $refund = fn ($callback) => $query->whereHas('refund', $callback);
        match ($view) {
            'open' => $refund(fn ($r) => $r->open()),
            'follow_up' => $refund(fn ($r) => $r->followUpDue()),
            'to_claim' => $refund(fn ($r) => $r->where('status', DepositRefund::STATUS_TO_CLAIM)),
            'claimed' => $refund(fn ($r) => $r->whereIn('status', [DepositRefund::STATUS_CLAIMED, DepositRefund::STATUS_PART_RECEIVED])),
            'to_confirm' => $refund(fn ($r) => $r->where('status', DepositRefund::STATUS_RECEIVED)),
            'disputed' => $refund(fn ($r) => $r->where('status', DepositRefund::STATUS_DISPUTED)),
            'closed' => $refund(fn ($r) => $r->whereIn('status', DepositRefund::CLOSED_STATUSES)),
            default => null,
        };

        $deposits = $query->orderByDesc('paid_on')->orderByDesc('id')->paginate(50)->withQueryString();
        $deposits->getCollection()->transform(fn (ContainerDeposit $d) => $this->row($d));

        return Inertia::render('Operations/Deposits/Index', [
            'deposits' => $deposits,
            'filters' => $request->only(['search', 'view', 'owner']),
            'views' => self::VIEWS,
            'summary' => self::summary(),
            'formOptions' => $this->formOptions(),
        ]);
    }

    public function show(Request $request, ContainerDeposit $deposit)
    {
        $deposit->load([
            'container.shippingLine:id,name,tracking_url', 'container.cargoShipment:id,tracking_id,cargo_description,bl_number',
            'shippingLine:id,name', 'creator:id,name',
            'refund.owner:id,name', 'refund.verifier:id,name', 'refund.followUps.user:id,name',
        ]);
        $clearable = ClearingLedger::clearableFor($deposit->container);
        $current = $clearable ? app(ClearingLedger::class)->current($clearable)?->load('officer:id,name,phone') : null;

        return Inertia::render('Operations/Deposits/Show', [
            'deposit' => $this->row($deposit) + [
                'refund' => $deposit->refund,
                'creator' => $deposit->creator,
                'notes' => $deposit->notes,
            ],
            'clearingOfficer' => $current?->officer,
            'audit' => \Spatie\Activitylog\Models\Activity::where('log_name', DepositLedger::LOG)
                ->where('subject_type', $deposit->getMorphClass())->where('subject_id', $deposit->id)
                ->with('causer')->latest('id')->limit(200)->get()
                ->map(fn ($a) => ClearingLedger::auditRow($a)),
            'statuses' => DepositRefund::STATUSES,
            'channels' => DepositFollowUp::CHANNELS,
            'formOptions' => $this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $this->validatedDeposit($request);
        $container = $this->resolveContainer($request);

        $existing = ContainerDeposit::where('container_id', $container->id)->first();
        if ($existing) {
            throw ValidationException::withMessages(['container_number' => "Container {$container->container_number} already has a deposit recorded. Open it to update it."]);
        }

        $deposit = DB::transaction(function () use ($data, $container, $user, $request) {
            $deposit = ContainerDeposit::create($data + [
                'container_id' => $container->id,
                'shipping_line_id' => $data['shipping_line_id'] ?? $container->shipping_line_id,
                'created_by' => $user->id,
            ]);
            if ($request->filled('owner_id') && $user->can('deposits.oversee')) {
                $deposit->refund->update(['owner_id' => $request->integer('owner_id')]);
            }
            $deposit->load('container', 'refund');

            DepositLedger::log($deposit, 'created', "Recorded deposit of " . self::money($deposit->amount, $deposit->currency) . " on container {$container->container_number}", $user, [], $deposit->only(['amount', 'currency', 'paid_on', 'paid_by', 'payment_reference']));
            app(ClearingLedger::class)->record(ClearingLedger::clearableFor($container), 'deposit', "Container deposit paid on {$container->container_number}", $deposit, $user, $deposit->paid_on);

            return $deposit;
        });

        return redirect()->route('operations.deposits.show', $deposit)->with('success', "Deposit on {$container->container_number} recorded.");
    }

    public function update(Request $request, ContainerDeposit $deposit)
    {
        $user = $request->user();
        $this->ensureEditable($deposit, $user);
        $data = $this->validatedDeposit($request, partial: true);

        $old = $deposit->only(array_keys($data));
        $deposit->update($data);
        $deposit->refund?->unsetRelation('deposit')->syncStatus();

        DepositLedger::log($deposit, 'updated', "Updated the deposit on container {$deposit->container?->container_number}", $user, $old, $deposit->only(array_keys($data)));

        return redirect()->back()->with('success', 'Deposit updated.');
    }

    /**
     * The refund side: empty return, the claim, deductions and money received.
     */
    public function updateRefund(Request $request, ContainerDeposit $deposit)
    {
        $user = $request->user();
        $this->ensureEditable($deposit, $user);
        $refund = $deposit->refund;

        $data = $request->validate([
            'empty_returned_on' => 'nullable|date|before_or_equal:today',
            'claim_submitted_on' => 'nullable|date|before_or_equal:today',
            'claim_reference' => 'nullable|string|max:120',
            'deductions' => 'nullable|numeric|min:0',
            'deduction_reason' => 'nullable|string|max:500',
            'amount_received' => 'nullable|numeric|min:0',
            'received_on' => 'nullable|date|before_or_equal:today',
            'received_reference' => 'nullable|string|max:120',
            'next_follow_up_on' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        $data = array_filter($data, fn ($k) => $request->has($k), ARRAY_FILTER_USE_KEY);

        if (array_key_exists('owner_id', $data) && !$user->can('deposits.oversee')) {
            unset($data['owner_id']);
        }
        foreach (['deductions', 'amount_received'] as $money) {
            if (array_key_exists($money, $data)) {
                $data[$money] = (float) ($data[$money] ?? 0);
            }
        }
        if (($data['deductions'] ?? 0) > (float) $deposit->amount) {
            throw ValidationException::withMessages(['deductions' => 'Deductions cannot be more than the deposit.']);
        }
        if (!empty($data['amount_received']) && empty($data['received_on'] ?? $refund->received_on)) {
            throw ValidationException::withMessages(['received_on' => 'Say when the refund was received.']);
        }
        if (!empty($data['deductions']) && empty($data['deduction_reason'] ?? $refund->deduction_reason)) {
            throw ValidationException::withMessages(['deduction_reason' => 'Say what the shipping line deducted for (demurrage, damage, cleaning...).']);
        }

        $container = $deposit->container;
        $old = $refund->only(array_keys($data)) + ['status' => $refund->status, 'empty_returned_on' => $container?->empty_returned_on?->toDateString()];
        $claimWasFiled = (bool) $refund->claim_submitted_on;
        $receivedBefore = (float) $refund->amount_received;

        DB::transaction(function () use ($data, $refund, $container) {
            if (array_key_exists('empty_returned_on', $data) && $container) {
                $container->update(['empty_returned_on' => $data['empty_returned_on']]);
            }
            unset($data['empty_returned_on']);
            // A newly filed claim gets the default chase date unless one was given.
            if (!$refund->claim_submitted_on && !empty($data['claim_submitted_on']) && empty($data['next_follow_up_on'])) {
                $data['next_follow_up_on'] = null;
            }
            $refund->fill($data);
            $refund->unsetRelation('deposit');
            $refund->save();
            $refund->syncStatus();
        });

        $refund->refresh();
        $new = $refund->only(array_keys($old)) + ['status' => $refund->status, 'empty_returned_on' => $container?->fresh()->empty_returned_on?->toDateString()];
        DepositLedger::log($deposit, 'updated', "Updated the refund on container {$container?->container_number}", $user, $old, $new);

        $ledger = app(ClearingLedger::class);
        $clearable = ClearingLedger::clearableFor($container);
        if (!$claimWasFiled && $refund->claim_submitted_on) {
            $ledger->record($clearable, 'refund', "Deposit refund claim filed for {$container->container_number}", $deposit, $user, $refund->claim_submitted_on);
        }
        if ((float) $refund->amount_received > $receivedBefore) {
            $ledger->record($clearable, 'refund', "Deposit refund received for {$container->container_number}", $deposit, $user, $refund->received_on);
        }

        return redirect()->back()->with('success', "Refund updated: {$refund->status_label}.");
    }

    public function storeFollowUp(Request $request, ContainerDeposit $deposit)
    {
        $user = $request->user();
        $refund = $deposit->refund;
        abort_if($refund->isClosed(), 422, 'This refund is closed. Reopen it first.');

        $data = $request->validate([
            'contacted_on' => 'nullable|date|before_or_equal:today',
            'channel' => ['nullable', Rule::in(array_keys(DepositFollowUp::CHANNELS))],
            'contact_person' => 'nullable|string|max:120',
            'outcome' => 'required|string|max:2000',
            'next_follow_up_on' => 'nullable|date|after_or_equal:today',
        ], [
            'outcome.required' => 'Write what the shipping line said.',
        ]);
        $data['contacted_on'] ??= today()->toDateString();

        $followUp = $refund->followUps()->create($data + ['user_id' => $user->id]);
        $next = $data['next_follow_up_on'] ?? Carbon::parse($data['contacted_on'])->addDays(7)->toDateString();
        $refund->update(['last_followed_up_on' => $data['contacted_on'], 'next_follow_up_on' => $next]);

        DepositLedger::log($deposit, 'followed_up', "Followed up the refund on container {$deposit->container?->container_number}" . ($followUp->channel ? ' by ' . strtolower(DepositFollowUp::CHANNELS[$followUp->channel]) : ''), $user, [], [], [
            'outcome' => $followUp->outcome,
            'next_follow_up_on' => $next,
        ]);

        return redirect()->back()->with('success', 'Follow-up saved. Next one ' . Carbon::parse($next)->format('j M') . '.');
    }

    /**
     * Oversight decisions: confirm a refund landed, mark a dispute, write it
     * off, or reopen a closed one.
     */
    public function review(Request $request, ContainerDeposit $deposit)
    {
        $user = $request->user();
        $refund = $deposit->refund;
        $data = $request->validate([
            'action' => 'required|in:verify,dispute,write_off,reopen',
            'reason' => 'nullable|string|max:1000',
        ]);
        if (in_array($data['action'], ['dispute', 'write_off', 'reopen'], true) && empty($data['reason'])) {
            throw ValidationException::withMessages(['reason' => 'Give a reason; it goes on the audit trail.']);
        }

        $old = $refund->status;
        match ($data['action']) {
            'verify' => (function () use ($refund, $user) {
                if ((float) $refund->amount_received <= 0) {
                    throw ValidationException::withMessages(['action' => 'Nothing has been received yet, so there is nothing to confirm.']);
                }
                $refund->update(['status' => DepositRefund::STATUS_VERIFIED, 'verified_by' => $user->id, 'verified_at' => now(), 'next_follow_up_on' => null]);
            })(),
            'dispute' => $refund->update(['status' => DepositRefund::STATUS_DISPUTED]),
            'write_off' => $refund->update(['status' => DepositRefund::STATUS_WRITTEN_OFF, 'next_follow_up_on' => null]),
            'reopen' => (function () use ($refund) {
                $refund->forceFill(['status' => DepositRefund::STATUS_AWAITING_EMPTY, 'verified_by' => null, 'verified_at' => null])->save();
                $refund->unsetRelation('deposit')->syncStatus();
            })(),
        };
        $refund->refresh();

        $verbs = ['verify' => 'Confirmed the refund', 'dispute' => 'Marked the refund disputed', 'write_off' => 'Wrote off the refund', 'reopen' => 'Reopened the refund'];
        DepositLedger::log($deposit, $data['action'], $verbs[$data['action']] . " on container {$deposit->container?->container_number}", $user,
            ['status' => $old], ['status' => $refund->status], array_filter(['reason' => $data['reason'] ?? null]));

        return redirect()->back()->with('success', "{$verbs[$data['action']]}.");
    }

    public function destroy(Request $request, ContainerDeposit $deposit)
    {
        DepositLedger::log($deposit, 'deleted', "Deleted the deposit on container {$deposit->container?->container_number}", $request->user(), $deposit->only(['amount', 'currency']), []);
        $deposit->delete();

        return redirect()->route('operations.deposits.index')->with('success', 'Deposit deleted.');
    }

    // ---- shared ---------------------------------------------------------

    /**
     * Totals for the oversight tiles, per currency (deposits are usually
     * naira but a line may bill in dollars).
     */
    public static function summary(?User $owner = null): array
    {
        $open = ContainerDeposit::with('refund', 'container:id,empty_returned_on')
            ->whereHas('refund', fn ($r) => $r->open()->when($owner, fn ($q) => $q->where('owner_id', $owner->id)))
            ->get();

        $byCurrency = $open->groupBy(fn ($d) => strtoupper($d->currency ?: 'NGN'))->map(fn ($rows) => [
            'deposits' => $rows->count(),
            'paid' => round((float) $rows->sum('amount'), 2),
            'outstanding' => round((float) $rows->sum(fn ($d) => $d->outstanding()), 2),
        ]);

        $refunds = DepositRefund::query()->when($owner, fn ($q) => $q->where('owner_id', $owner->id));

        return [
            'byCurrency' => $byCurrency,
            'counts' => [
                'open' => $open->count(),
                'follow_up' => (clone $refunds)->followUpDue()->count(),
                'to_claim' => (clone $refunds)->where('status', DepositRefund::STATUS_TO_CLAIM)->count(),
                'to_confirm' => (clone $refunds)->where('status', DepositRefund::STATUS_RECEIVED)->count(),
                'disputed' => (clone $refunds)->where('status', DepositRefund::STATUS_DISPUTED)->count(),
                'unowned' => (clone $refunds)->open()->whereNull('owner_id')->count(),
            ],
        ];
    }

    /** Refunds due a chase, for home screens. */
    public static function dueList(?User $owner = null, int $limit = 8): array
    {
        return DepositRefund::followUpDue()
            ->when($owner, fn ($q) => $q->where(fn ($q) => $q->where('owner_id', $owner->id)->orWhereNull('owner_id')))
            ->with('deposit.container:id,container_number', 'deposit.shippingLine:id,name')
            ->orderBy('next_follow_up_on')->limit($limit)->get()
            ->map(fn (DepositRefund $r) => [
                'deposit_id' => $r->container_deposit_id,
                'container_number' => $r->deposit?->container?->container_number,
                'line' => $r->deposit?->shippingLine?->name,
                'status_label' => $r->status_label,
                'next_follow_up_on' => $r->next_follow_up_on?->toDateString(),
                'outstanding' => $r->deposit?->outstanding(),
                'currency' => $r->deposit?->currency,
            ])->all();
    }

    /** The deposits on a container or clearing job, for its page. */
    public static function panelFor(array $containerIds, ?User $user): ?array
    {
        if (!$user?->can('deposits.view')) {
            return null;
        }

        return [
            'deposits' => ContainerDeposit::whereIn('container_id', $containerIds)
                ->with('container:id,container_number,empty_returned_on', 'refund.owner:id,name')
                ->get()->map(fn ($d) => [
                    'id' => $d->id,
                    'container_number' => $d->container?->container_number,
                    'amount' => (float) $d->amount,
                    'currency' => $d->currency,
                    'paid_on' => $d->paid_on?->toDateString(),
                    'status' => $d->refund?->status,
                    'status_label' => $d->refund?->status_label,
                    'outstanding' => $d->outstanding(),
                    'owner' => $d->refund?->owner?->name,
                ]),
            'containers' => Container::whereIn('id', $containerIds)->whereDoesntHave('deposits')->get(['id', 'container_number']),
        ];
    }

    private function row(ContainerDeposit $d): array
    {
        $container = $d->container;

        return [
            'id' => $d->id,
            'container_id' => $d->container_id,
            'container_number' => $container?->container_number,
            'container_link' => $container ? ($container->cargo_shipment_id ? route('operations.clearing.show', $container->cargo_shipment_id) : route('operations.containers.show', $container->id)) : null,
            'job' => $container?->cargoShipment?->only(['id', 'tracking_id', 'cargo_description']),
            'bl_number' => $container?->bl_number ?? $container?->cargoShipment?->bl_number,
            'empty_returned_on' => $container?->empty_returned_on?->toDateString(),
            'shipping_line_id' => $d->shipping_line_id,
            'shipping_line' => $d->shippingLine?->name,
            'amount' => (float) $d->amount,
            'currency' => $d->currency,
            'paid_on' => $d->paid_on?->toDateString(),
            'paid_by' => $d->paid_by,
            'payment_reference' => $d->payment_reference,
            'status' => $d->refund?->status,
            'status_label' => $d->refund?->status_label,
            'deductions' => (float) ($d->refund?->deductions ?? 0),
            'amount_received' => (float) ($d->refund?->amount_received ?? 0),
            'expected' => $d->expectedRefund(),
            'outstanding' => $d->outstanding(),
            'owner' => $d->refund?->owner?->only(['id', 'name']),
            'next_follow_up_on' => $d->refund?->next_follow_up_on?->toDateString(),
            'follow_up_due' => $d->refund && !$d->refund->isClosed() && $d->refund->status !== DepositRefund::STATUS_AWAITING_EMPTY
                && $d->refund->next_follow_up_on && $d->refund->next_follow_up_on->lte(today()),
            'locked' => $d->refund?->isClosed() ?? false,
        ];
    }

    private function ensureEditable(ContainerDeposit $deposit, User $user): void
    {
        if ($deposit->refund?->isClosed() && !$user->can('deposits.oversee')) {
            abort(403, 'This refund has been confirmed or written off. Ask the CEO or Head of Business to reopen it.');
        }
        abort_unless($user->can('deposits.manage') || $user->can('deposits.oversee'), 403);
    }

    private function resolveContainer(Request $request): Container
    {
        if ($request->filled('container_id')) {
            return Container::findOrFail($request->integer('container_id'));
        }
        $request->validate(['container_number' => 'required|string|max:20'], ['container_number.required' => 'Enter the container number.']);
        $number = ContainerNumber::clean($request->input('container_number'));
        $container = Container::where('container_number', $number)->latest('id')->first();
        if (!$container) {
            throw ValidationException::withMessages(['container_number' => "No container {$number} in the system. Add it under Containers or on its clearing job first."]);
        }

        return $container;
    }

    private function validatedDeposit(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';
        $data = $request->validate([
            'amount' => [$req, 'numeric', 'min:1'],
            'currency' => 'nullable|string|size:3',
            'paid_on' => 'nullable|date|before_or_equal:today',
            'paid_by' => ['nullable', Rule::in(array_keys(ContainerDeposit::PAID_BY))],
            'payment_reference' => 'nullable|string|max:120',
            'shipping_line_id' => 'nullable|exists:shipping_lines,id',
            'notes' => 'nullable|string|max:2000',
        ], [
            'amount.required' => 'Enter the deposit amount.',
        ]);
        if (isset($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }
        if (!$partial) {
            $data['currency'] ??= 'NGN';
            $data['paid_by'] ??= 'mapleby';
            $data['paid_on'] ??= today()->toDateString();
        }

        return $partial ? array_filter($data, fn ($k) => $request->has($k), ARRAY_FILTER_USE_KEY) : $data;
    }

    private function formOptions(): array
    {
        return [
            'shippingLines' => ShippingLine::orderBy('name')->get(['id', 'name']),
            'paidBy' => ContainerDeposit::PAID_BY,
            'owners' => User::role([Access::ROLE_PUNCHER, Access::ROLE_LEGACY_STAFF, Access::ROLE_BUSINESS, Access::ROLE_CEO])->where('is_active', true)
                ->orderBy('name')->get(['id', 'name']),
            // Recent containers without a deposit, as suggestions.
            'containers' => Container::whereDoesntHave('deposits')->where('created_at', '>=', now()->subYear())
                ->orderByDesc('id')->limit(300)->get(['id', 'container_number', 'cargo_shipment_id', 'status']),
        ];
    }

    private static function money($amount, ?string $currency): string
    {
        return strtoupper($currency ?: 'NGN') . ' ' . number_format((float) $amount, 2);
    }
}
