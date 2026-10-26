<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Quoting\Entities\Quote;
use Modules\Quoting\Entities\QuoteItem;
use Modules\CRM\Entities\Client;
use Modules\CRM\Entities\Lead;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['client', 'lead', 'payments']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q2) use ($search) {
                        $q2->where('company_name', 'like', "%{$search}%")
                            ->orWhere('contact_person', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $quotes = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($quote) {
                return [
                    'id' => $quote->id,
                    'quote_number' => $quote->quote_number,
                    'status' => $quote->status,
                    'issue_date' => $quote->issue_date,
                    'expiry_date' => $quote->expiry_date,
                    'total_amount' => $quote->total_amount,
                    'client' => $quote->client,
                    // Append calculated payment data
                    'paid_amount' => $quote->paid_amount,
                    'payment_status' => $quote->payment_status,
                ];
            });

        // Limit clients to prevent timeouts on large datasets
        $clients = Client::orderBy('company_name')
            ->select('id', 'company_name', 'contact_person')
            ->limit(100)
            ->get();

        // Calculate Stats for Insights
        $stats = Quote::selectRaw('status, count(*) as value')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucfirst($item->status),
                    'value' => $item->value
                ];
            });

        // Fetch data for linking quotes
        $shipments = \Modules\Operations\Entities\Shipment::whereIn('status', ['pending', 'received', 'scheduled'])
            ->orderBy('created_at', 'desc')->select('id', 'shipment_number', 'title', 'client_id', 'status')->get();

        $vehicles = \Modules\Inventory\Entities\Vehicle::orderBy('year', 'desc')->select('id', 'year', 'make', 'model')->get();

        return Inertia::render('Operations/Quotes/Index', [
            'quotes' => $quotes,
            'filters' => $request->only(['search', 'status']),
            'clients' => $clients,
            'shipments' => $shipments,
            'vehicles' => $vehicles,
            'stats' => $stats,
        ]);
    }

    public function show(Quote $quote)
    {
        $quote->load(['client', 'items', 'payments', 'shipment', 'inventory']);

        // Fetch all system settings from the DB to format as key-value pairs
        $settingsData = \Modules\Core\Entities\Setting::all()->pluck('value', 'key')->toArray();

        return Inertia::render('Operations/Quotes/Show', [
            'quote' => $quote,
            'paid_amount' => $quote->paid_amount,
            'payment_status' => $quote->payment_status,
            'settings' => $settingsData,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipment_id' => 'nullable|exists:shipments,id',
            'inventory_type' => 'nullable|string',
            'inventory_id' => 'nullable|integer',
        ]);

        $items = collect($validated['items']);
        $subtotal = $items->sum(fn($item) => $item['quantity'] * $item['unit_price']);

        $taxRate = $request->input('tax_rate', 0);
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $quote = Quote::create([
            'quote_number' => 'Q-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'client_id' => $validated['client_id'],
            'issue_date' => $validated['issue_date'],
            'expiry_date' => $validated['expiry_date'],
            'status' => $validated['status'],
            'subtotal' => $subtotal,
            'tax_total' => $taxAmount,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'],
            'terms_conditions' => $validated['terms_conditions'],
            'shipment_id' => $validated['shipment_id'] ?? null,
            'inventory_type' => $validated['inventory_type'] ?? null,
            'inventory_id' => $validated['inventory_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($items as $item) {
            $quote->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->back()->with('success', 'Quote created successfully.');
    }

    public function update(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'tax_rate' => 'nullable|numeric',
            'shipment_id' => 'nullable|exists:shipments,id',
            'inventory_type' => 'nullable|string',
            'inventory_id' => 'nullable|integer',
        ]);

        $items = collect($validated['items']);
        $subtotal = $items->sum(fn($item) => $item['quantity'] * $item['unit_price']);

        $taxRate = $request->input('tax_rate', 0);
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $quote->update([
            'client_id' => $validated['client_id'],
            'issue_date' => $validated['issue_date'],
            'expiry_date' => $validated['expiry_date'],
            'status' => $validated['status'],
            'subtotal' => $subtotal,
            'tax_total' => $taxAmount,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'],
            'terms_conditions' => $validated['terms_conditions'],
            'shipment_id' => $validated['shipment_id'] ?? null,
            'inventory_type' => $validated['inventory_type'] ?? null,
            'inventory_id' => $validated['inventory_id'] ?? null,
        ]);

        // Sync items
        $quote->items()->delete();
        foreach ($items as $item) {
            $quote->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->back()->with('success', 'Quote updated successfully.');
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->back()->with('success', 'Quote deleted successfully.');
    }
    public function updateStatus(Request $request, Quote $quote)
    {
        $request->validate(['status' => 'required|string']);
        $quote->update(['status' => $request->status]);
        return redirect()->back();
    }
}
