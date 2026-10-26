<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Modules\CRM\Entities\Client;
use Modules\Finance\Entities\Invoice;
use Modules\Finance\Entities\ShipmentCharge;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('client:id,company_name,contact_person')->withCount('charges');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn ($q) => $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($c) => $c->where('company_name', 'like', "%{$search}%")->orWhere('contact_person', 'like', "%{$search}%")));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $open = Invoice::whereIn('status', ['sent', 'partial']);

        return Inertia::render('Finance/Invoices/Index', [
            'invoices' => $query->orderByDesc('issue_date')->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
            'statuses' => Invoice::STATUSES,
            'summary' => [
                'outstanding' => round((float) (clone $open)->sum(DB::raw('total - amount_paid')), 2),
                'overdue' => round((float) (clone $open)->whereDate('due_date', '<', now())->sum(DB::raw('total - amount_paid')), 2),
                'open_count' => (clone $open)->count(),
            ],
            'billable' => $this->billableByClient(),
        ]);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'charges.shipment:id,tracking_id,vin,vehicle_year,vehicle_make,vehicle_model', 'payments', 'creator:id,name']);

        return Inertia::render('Finance/Invoices/Show', [
            'invoice' => $invoice,
            'statuses' => Invoice::STATUSES,
        ]);
    }

    /**
     * Create an invoice from a customer's unbilled charge lines.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'charge_ids' => 'required|array|min:1',
            'charge_ids.*' => 'integer|exists:shipment_charges,id',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
        ]);

        $charges = ShipmentCharge::with('shipment')->whereIn('id', $data['charge_ids'])->get();
        $bad = $charges->first(fn ($c) => $c->kind !== 'charge' || $c->invoice_id || $c->shipment?->client_id !== (int) $data['client_id']);
        if ($bad) {
            throw ValidationException::withMessages(['charge_ids' => 'Only unbilled customer charges for vehicles belonging to this customer can be invoiced.']);
        }

        $invoice = DB::transaction(function () use ($data, $charges, $request) {
            $invoice = Invoice::create([
                'client_id' => $data['client_id'],
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? now()->addDays(14)->toDateString(),
                'currency' => strtoupper($data['currency'] ?? $charges->first()->currency ?? 'USD'),
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);
            ShipmentCharge::whereIn('id', $charges->pluck('id'))->update(['invoice_id' => $invoice->id]);
            $invoice->recalculate();

            return $invoice;
        });

        return redirect()->route('finance.invoices.show', $invoice)->with('success', "Invoice {$invoice->invoice_number} created.");
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['draft', 'sent', 'void'])]]);

        if ($data['status'] === 'void') {
            $invoice->charges()->update(['invoice_id' => null]);
        }
        $invoice->update($data);
        $invoice->recalculate();

        return redirect()->back()->with('success', "Invoice marked {$invoice->status}.");
    }

    private function billableByClient(): array
    {
        return ShipmentCharge::with('shipment:id,client_id,tracking_id,vehicle_year,vehicle_make,vehicle_model')
            ->where('kind', 'charge')->whereNull('invoice_id')
            ->whereHas('shipment', fn ($s) => $s->whereNotNull('client_id'))
            ->get()
            ->groupBy(fn ($c) => $c->shipment->client_id)
            ->map(function ($charges, $clientId) {
                $client = Client::find($clientId, ['id', 'company_name', 'contact_person']);

                return [
                    'client' => $client,
                    'total' => round((float) $charges->sum('amount'), 2),
                    'charges' => $charges->values(),
                ];
            })->values()->all();
    }
}
