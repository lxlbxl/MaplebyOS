<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\CRM\Entities\Client;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Calculate Stats for Insights
        $stats = [
            ['name' => 'Active', 'value' => Client::where('is_active', true)->count()],
            ['name' => 'Inactive', 'value' => Client::where('is_active', false)->count()],
        ];

        return Inertia::render('CRM/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
            'stats' => $stats,
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('query');

        if (empty($search)) {
            return response()->json([]);
        }

        $clients = Client::where('company_name', 'like', "%{$search}%")
            ->orWhere('contact_person', 'like', "%{$search}%")
            ->select('id', 'company_name', 'contact_person')
            ->limit(20)
            ->get();

        return response()->json($clients);
    }

    public function show(Client $client)
    {
        // Load client with shipments
        $client->load([
            'shipments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'shipments.assignee'
        ]);

        // Calculate client-specific stats
        $totalShipments = $client->shipments->count();
        $totalValue = $client->shipments->sum('total_value');
        $currentShipments = $client->shipments->whereIn('status', ['received', 'scheduled', 'shipped', 'arrived'])->count();
        $deliveredShipments = $client->shipments->where('status', 'delivered')->count();

        $stats = [
            'total_shipments' => $totalShipments,
            'total_value' => $totalValue,
            'current_shipments' => $currentShipments,
            'delivered_shipments' => $deliveredShipments,
        ];

        // Shipment status breakdown for chart
        $statusBreakdown = [
            ['name' => 'Received', 'value' => $client->shipments->where('status', 'received')->count()],
            ['name' => 'Scheduled', 'value' => $client->shipments->where('status', 'scheduled')->count()],
            ['name' => 'Shipped', 'value' => $client->shipments->where('status', 'shipped')->count()],
            ['name' => 'Arrived', 'value' => $client->shipments->where('status', 'arrived')->count()],
            ['name' => 'Delivered', 'value' => $client->shipments->where('status', 'delivered')->count()],
        ];

        return Inertia::render('CRM/Clients/Show', [
            'client' => $client,
            'stats' => $stats,
            'statusBreakdown' => $statusBreakdown,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'type' => 'required|in:buyer,supplier,both',
            'status' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->input('status', true);

        Client::create($validated);

        return redirect()->back()->with('success', 'Client created successfully.');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'type' => 'required|in:buyer,supplier,both',
            'status' => 'boolean',
        ]);

        $validated['is_active'] = $request->input('status', true);

        $client->update($validated);

        return redirect()->back()->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->back()->with('success', 'Client deleted successfully.');
    }
}
