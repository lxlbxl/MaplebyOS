<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\CRM\Entities\Lead;
use Modules\Finance\Entities\Invoice;
use Modules\Finance\Entities\Payment;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\Document;
use Modules\Operations\Entities\Shipment;
use Modules\Quoting\Entities\Quote;

/**
 * The staff app's home screen. Each role gets its own: the CEO sees the whole
 * business, Head of Business sees money coming in, the Logistics Officer sees
 * containers, and the Puncher sees their data entry.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $home = Access::home($user);

        $data = match ($home) {
            'ceo' => $this->ceo(),
            'business' => $this->business(),
            'logistics' => $this->logistics(),
            default => $this->puncher($user),
        };

        return Inertia::render('Home/Index', [
            'home' => $home,
            'greeting' => $this->greeting(),
            'roleLabel' => Access::roleLabel($user),
            'data' => $data,
        ]);
    }

    /**
     * Money tiles add up dollar invoices only. Clearing is billed in naira and
     * must not be summed into the same figure.
     */
    private const MONEY_CURRENCY = 'USD';

    private function ceo(): array
    {
        $monthStart = now()->startOfMonth();

        return [
            'money' => [
                'invoiced_month' => (float) Invoice::where('currency', self::MONEY_CURRENCY)->where('status', '!=', 'void')->where('issue_date', '>=', $monthStart)->sum('total'),
                'collected_month' => (float) Payment::whereHas('invoice', fn ($i) => $i->where('currency', self::MONEY_CURRENCY))->where('payment_date', '>=', $monthStart)->sum('amount'),
                'outstanding' => $this->outstanding(),
                'profit_month' => $this->profitFor(Shipment::vehicles()->where('delivered_on', '>=', $monthStart)->pluck('id')->all()),
            ],
            'pipeline' => $this->pipeline(),
            'atSea' => $this->containersAtSea(),
            'containerProfit' => $this->containerProfit(),
            'alerts' => $this->alerts(),
        ];
    }

    private function business(): array
    {
        $monthStart = now()->startOfMonth();

        return [
            'money' => [
                'outstanding' => $this->outstanding(),
                'collected_month' => (float) Payment::whereHas('invoice', fn ($i) => $i->where('currency', self::MONEY_CURRENCY))->where('payment_date', '>=', $monthStart)->sum('amount'),
                'profit_month' => $this->profitFor(Shipment::vehicles()->where('delivered_on', '>=', $monthStart)->pluck('id')->all()),
            ],
            'unpaidInvoices' => Invoice::with('client:id,company_name,contact_person')
                ->whereIn('status', ['sent', 'partial'])
                ->orderBy('due_date')->limit(8)
                ->get(['id', 'invoice_number', 'client_id', 'due_date', 'total', 'amount_paid', 'status'])
                ->map(fn ($i) => [
                    'id' => $i->id,
                    'number' => $i->invoice_number,
                    'client' => $i->client?->company_name ?: $i->client?->contact_person,
                    'due_date' => $i->due_date?->format('Y-m-d'),
                    'balance' => $i->balance,
                    'overdue' => $i->due_date && $i->due_date->isPast(),
                ]),
            'newLeads' => Lead::with('client:id,company_name,contact_person')->where('status', 'new')
                ->latest()->limit(6)->get(['id', 'title', 'client_id', 'created_at'])
                ->map(fn ($l) => [
                    'id' => $l->id,
                    'title' => $l->title,
                    'client' => $l->client?->company_name ?: $l->client?->contact_person,
                    'date' => $l->created_at?->format('M j'),
                ]),
            'newLeadCount' => Lead::where('status', 'new')->count(),
            'quotesAwaiting' => Quote::with('client:id,company_name,contact_person')->where('status', 'sent')
                ->orderBy('issue_date')->limit(6)->get(['id', 'quote_number', 'client_id', 'total_amount', 'issue_date'])
                ->map(fn ($q) => [
                    'id' => $q->id,
                    'number' => $q->quote_number,
                    'client' => $q->client?->company_name ?: $q->client?->contact_person,
                    'total' => (float) $q->total_amount,
                ]),
            'deliveredUnpaid' => Shipment::with('client:id,company_name,contact_person')
                ->where('status', Shipment::STATUS_DELIVERED)
                ->where(fn ($q) => $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'paid'))
                ->orderByDesc('delivered_on')->limit(8)
                ->get(['id', 'tracking_id', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'cargo_type', 'cargo_description', 'title', 'client_id', 'delivered_on', 'payment_status'])
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'tracking_id' => $s->tracking_id,
                    'vehicle' => $s->vehicle_label,
                    'client' => $s->client?->company_name ?: $s->client?->contact_person,
                    'delivered_on' => $s->delivered_on?->format('Y-m-d'),
                    'payment_status' => $s->payment_status ?: 'unpaid',
                ]),
            'pipeline' => $this->pipeline(),
        ];
    }

    private function logistics(): array
    {
        $byStatus = Container::forVehicles()->selectRaw('status, count(*) as total')
            ->where(fn ($q) => $q->where('status', '!=', Container::STATUS_RELEASED)->orWhere('released_on', '>=', now()->subDays(14)))
            ->groupBy('status')->pluck('total', 'status');

        $stages = collect(Container::getStatuses())->map(fn ($label, $status) => [
            'status' => $status,
            'label' => $label,
            'count' => (int) ($byStatus[$status] ?? 0),
        ])->values();

        $waiting = Shipment::vehicles()->whereNull('container_id')
            ->whereIn('status', Shipment::PRE_LOADING_STATUSES)
            ->orderBy('date_added')->limit(12)
            ->get(['id', 'tracking_id', 'vin', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'status', 'date_added', 'has_keys'])
            ->map(fn ($s) => $this->carRow($s) + ['days_waiting' => $s->date_added ? (int) $s->date_added->diffInDays(now()) : null]);

        $arrivals = Container::forVehicles()->with('shippingLine:id,name')->withCount(['shipments' => fn ($q) => $q->vehicles()])
            ->whereIn('status', [Container::STATUS_LOADED, Container::STATUS_SAILED])
            ->whereBetween('eta', [now()->subDays(3)->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('eta')->get()
            ->map(fn ($c) => $this->containerRow($c));

        $problems = Shipment::vehicles()->where(function ($q) {
            $q->whereNull('vin')->orWhere('vin', '')->orWhere('has_keys', false);
        })->whereNotIn('status', [Shipment::STATUS_DELIVERED, Shipment::STATUS_CLEARED])
            ->orderByDesc('date_added')->limit(10)
            ->get(['id', 'tracking_id', 'vin', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'status', 'has_keys'])
            ->map(fn ($s) => $this->carRow($s) + [
                'problem' => empty($s->vin) ? 'No VIN' : 'No keys',
            ]);

        return [
            'stages' => $stages,
            'waitingCount' => Shipment::vehicles()->whereNull('container_id')->whereIn('status', Shipment::PRE_LOADING_STATUSES)->count(),
            'waiting' => $waiting,
            'arrivals' => $arrivals,
            'atSea' => $this->containersAtSea(),
            'problems' => $problems,
            'missingDocs' => $this->containersMissingBillOfLading(),
            'openContainers' => Container::forVehicles()->with('shippingLine:id,name')->withCount(['shipments' => fn ($q) => $q->vehicles()])
                ->whereIn('status', [Container::STATUS_PLANNED, Container::STATUS_LOADED])
                ->orderByDesc('id')->limit(10)->get()
                ->map(fn ($c) => $this->containerRow($c)),
        ];
    }

    private function puncher(?User $user): array
    {
        $mine = Shipment::vehicles()->where('created_by', $user?->id);

        $needsFixing = (clone $mine)
            ->whereIn('status', Shipment::PRE_LOADING_STATUSES)
            ->where(function ($q) {
                $q->whereNull('vin')->orWhere('vin', '')
                    ->orWhereNull('vehicle_make')->orWhereNull('vehicle_model')->orWhereNull('vehicle_year');
            })
            ->orderByDesc('id')->limit(15)
            ->get(['id', 'tracking_id', 'vin', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'status'])
            ->map(fn ($s) => $this->carRow($s) + [
                'problem' => collect([
                    empty($s->vin) ? 'VIN' : null,
                    empty($s->vehicle_make) ? 'make' : null,
                    empty($s->vehicle_model) ? 'model' : null,
                    empty($s->vehicle_year) ? 'year' : null,
                ])->filter()->implode(', ') . ' missing',
            ]);

        return [
            'today' => (clone $mine)->whereDate('created_at', today())->latest('id')->limit(30)
                ->get(['id', 'tracking_id', 'vin', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'status', 'created_at'])
                ->map(fn ($s) => $this->carRow($s) + ['time' => $s->created_at?->format('H:i')]),
            'counts' => [
                'today' => (clone $mine)->whereDate('created_at', today())->count(),
                'week' => (clone $mine)->where('created_at', '>=', now()->startOfWeek())->count(),
                'needs_fixing' => $needsFixing->count(),
            ],
            'needsFixing' => $needsFixing,
        ];
    }

    // ---- shared pieces -------------------------------------------------

    private function pipeline(): array
    {
        $counts = Shipment::vehicles()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return collect(Shipment::getStatuses())->map(fn ($label, $status) => [
            'status' => $status,
            'label' => $label,
            'count' => (int) ($counts[$status] ?? 0),
        ])->values()->all();
    }

    private function outstanding(): float
    {
        return (float) Invoice::where('currency', self::MONEY_CURRENCY)->whereIn('status', ['sent', 'partial'])
            ->selectRaw('COALESCE(SUM(total - COALESCE(amount_paid, 0)), 0) as balance')->value('balance');
    }

    /**
     * Profit = what customers are charged - (purchase price + cost lines + freight share).
     */
    private function profitFor(array $shipmentIds): ?float
    {
        if (!$shipmentIds) {
            return 0.0;
        }
        $lines = ShipmentCharge::whereIn('shipment_id', $shipmentIds)
            ->selectRaw('kind, SUM(amount) as total')->groupBy('kind')->pluck('total', 'kind');
        $purchase = (float) Shipment::whereIn('id', $shipmentIds)->sum('purchase_price');
        $freight = Shipment::with('container')->whereIn('id', $shipmentIds)->get()
            ->sum(fn ($s) => (float) ($s->container?->freightSharePerVehicle() ?? 0));

        return round((float) ($lines['charge'] ?? 0) - (float) ($lines['cost'] ?? 0) - $purchase - $freight, 2);
    }

    private function containerProfit(): array
    {
        return Container::forVehicles()->with('shippingLine:id,name')->withCount(['shipments' => fn ($q) => $q->vehicles()])
            ->whereNotNull('loaded_on')->orderByDesc('loaded_on')->limit(6)->get()
            ->map(fn ($c) => $this->containerRow($c) + [
                'profit' => $this->profitFor($c->shipments()->pluck('id')->all()),
            ])->all();
    }

    private function containersAtSea(): array
    {
        return Container::forVehicles()->with('shippingLine:id,name')->withCount(['shipments' => fn ($q) => $q->vehicles()])
            ->whereIn('status', [Container::STATUS_LOADED, Container::STATUS_SAILED])
            ->orderByRaw('eta IS NULL, eta ASC')->limit(10)->get()
            ->map(fn ($c) => $this->containerRow($c))->all();
    }

    private function containersMissingBillOfLading(): array
    {
        $withBol = Document::where('documentable_type', Container::class)->where('type', 'bill_of_lading')
            ->pluck('documentable_id')->all();

        return Container::forVehicles()->withCount(['shipments' => fn ($q) => $q->vehicles()])
            ->whereIn('status', [Container::STATUS_SAILED, Container::STATUS_ARRIVED, Container::STATUS_OFFLOADED])
            ->whereNotIn('id', $withBol ?: [0])
            ->orderByDesc('id')->limit(8)->get()
            ->map(fn ($c) => $this->containerRow($c))->all();
    }

    private function alerts(): array
    {
        $alerts = [];

        $lateContainers = Container::forVehicles()->whereIn('status', [Container::STATUS_LOADED, Container::STATUS_SAILED])
            ->whereNotNull('eta')->where('eta', '<', today()->toDateString())->count();
        if ($lateContainers) {
            $alerts[] = ['level' => 'warning', 'text' => "{$lateContainers} container(s) past their arrival date but not marked arrived", 'href' => route('operations.containers.index')];
        }

        $stuck = Shipment::vehicles()->whereNull('container_id')->whereIn('status', Shipment::PRE_LOADING_STATUSES)
            ->where('date_added', '<', now()->subDays(30)->toDateString())->count();
        if ($stuck) {
            $alerts[] = ['level' => 'warning', 'text' => "{$stuck} car(s) waiting more than 30 days for a container", 'href' => route('operations.shipments.index', ['view' => 'unassigned'])];
        }

        $noVin = Shipment::vehicles()->where(fn ($q) => $q->whereNull('vin')->orWhere('vin', ''))
            ->whereNotIn('status', [Shipment::STATUS_DELIVERED])->count();
        if ($noVin) {
            $alerts[] = ['level' => 'info', 'text' => "{$noVin} car(s) without a VIN", 'href' => route('operations.shipments.index', ['view' => 'no_vin'])];
        }

        $missingBol = count($this->containersMissingBillOfLading());
        if ($missingBol) {
            $alerts[] = ['level' => 'info', 'text' => "{$missingBol} sailed container(s) with no Bill of Lading uploaded", 'href' => route('operations.containers.index')];
        }

        $overdue = Invoice::whereIn('status', ['sent', 'partial'])->whereNotNull('due_date')->where('due_date', '<', today()->toDateString())->count();
        if ($overdue) {
            $alerts[] = ['level' => 'warning', 'text' => "{$overdue} invoice(s) overdue", 'href' => route('finance.invoices.index')];
        }

        return $alerts;
    }

    private function carRow(Shipment $s): array
    {
        return [
            'id' => $s->id,
            'tracking_id' => $s->tracking_id,
            'vin' => $s->vin,
            'vehicle' => $s->vehicle_label,
            'status' => $s->status,
            'status_label' => $s->status_label,
        ];
    }

    private function containerRow(Container $c): array
    {
        return [
            'id' => $c->id,
            'number' => $c->container_number,
            'line' => $c->shippingLine?->name,
            'status' => $c->status,
            'status_label' => $c->status_label,
            'eta' => $c->eta?->format('Y-m-d'),
            'days_to_eta' => $c->eta ? (int) today()->diffInDays($c->eta, false) : null,
            'cars' => $c->shipments_count ?? null,
            'capacity' => $c->capacity,
        ];
    }

    private function greeting(): string
    {
        $timezone = 'UTC';
        try {
            $timezone = \Modules\Core\Entities\Setting::getGroup('general')['timezone'] ?? 'UTC';
            $hour = now()->setTimezone($timezone)->hour;
        } catch (\Throwable) {
            $hour = now()->hour;
        }

        return match (true) {
            $hour >= 5 && $hour < 12 => 'Good morning',
            $hour >= 12 && $hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };
    }
}
