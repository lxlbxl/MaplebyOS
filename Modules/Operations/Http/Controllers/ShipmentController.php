<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Operations\Entities\Shipment;
use Modules\CRM\Entities\Client;
use App\Models\User;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with(['client', 'assignee']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q2) use ($search) {
                        $q2->where('company_name', 'like', "%{$search}%")
                            ->orWhere('contact_person', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $shipments = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Limit clients to avoid massive query overhead causing timeouts
        $clients = Client::orderBy('company_name')
            ->select('id', 'company_name', 'contact_person')
            ->limit(100)
            ->get();

        $users = User::select('id', 'name')->limit(100)->get();

        // Calculate Stats for Insights
        $stats = Shipment::selectRaw('status, count(*) as value')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucfirst(str_replace('_', ' ', $item->status)),
                    'value' => $item->value
                ];
            });

        return Inertia::render('Operations/Shipments/Index', [
            'shipments' => $shipments,
            'filters' => $request->only(['search', 'status']),
            'clients' => $clients,
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['client', 'assignee', 'quotes.payments']);

        // Calculate total associated payments directly via loaded relationship
        // Ensure quotes and their payments are actually loaded
        $totalPaid = $shipment->quotes->sum(function ($quote) {
            return $quote->payments->sum('amount');
        });

        // Also calculate Total Quoted for context
        $totalQuoted = $shipment->quotes->sum('total_amount');

        // Limit clients/users for the edit form if needed
        $clients = Client::orderBy('company_name')->select('id', 'company_name', 'contact_person')->limit(100)->get();
        $users = User::select('id', 'name')->limit(100)->get();

        return Inertia::render('Operations/Shipments/Show', [
            'shipment' => $shipment,
            'clients' => $clients,
            'users' => $users,
            'totalPaid' => $totalPaid,
            'totalQuoted' => $totalQuoted,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|string',
            'service_type' => 'nullable|string',
            'origin' => 'nullable|string',
            'destination' => 'nullable|string',
            'estimated_completion' => 'nullable|date',
            'total_value' => 'nullable|numeric',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'vin' => 'nullable|string|max:255',
        ]);

        $validated['shipment_number'] = 'SHP-' . strtoupper(Str::random(8));

        Shipment::create($validated);

        return redirect()->back()->with('success', 'Shipment created successfully.');
    }

    public function update(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|string',
            'service_type' => 'nullable|string',
            'origin' => 'nullable|string',
            'destination' => 'nullable|string',
            'estimated_completion' => 'nullable|date',
            'total_value' => 'nullable|numeric',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'vin' => 'nullable|string|max:255',
        ]);

        $shipment->update($validated);

        return redirect()->back()->with('success', 'Shipment updated successfully.');
    }

    public function destroy(Shipment $shipment)
    {
        $shipment->delete();
        return redirect()->back()->with('success', 'Shipment deleted successfully.');
    }
    public function updateStatus(Request $request, Shipment $shipment)
    {
        $request->validate(['status' => 'required|string']);
        $shipment->update(['status' => $request->status]);
        return redirect()->back();
    }
}
