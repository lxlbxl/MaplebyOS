<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Inventory\Entities\Vehicle;
use Modules\CRM\Entities\Client;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%")
                    ->orWhere('year', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $vehicles = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Limit clients to avoid massive query overhead
        $clients = Client::orderBy('company_name')
            ->select('id', 'company_name', 'contact_person')
            ->limit(100)
            ->get();

        // Calculate Stats for Insights
        $stats = Vehicle::selectRaw('status, count(*) as value')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucfirst(str_replace('_', ' ', $item->status)),
                    'value' => $item->value
                ];
            });

        return Inertia::render('Inventory/Vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => $request->only(['search', 'status']),
            'clients' => $clients,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'vin' => 'required|string|unique:vehicles,vin|max:255',
            'color' => 'nullable|string|max:100',
            'condition' => 'required|in:used,new,salvage',
            'status' => 'required|in:in_stock,sold,shipped,reserved',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:clients,id',
            'buyer_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string',
        ]);

        Vehicle::create($validated);

        return redirect()->back()->with('success', 'Vehicle added to inventory.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'vin' => 'required|string|max:255|unique:vehicles,vin,' . $vehicle->id,
            'color' => 'nullable|string|max:100',
            'condition' => 'required|in:used,new,salvage',
            'status' => 'required|in:in_stock,sold,shipped,reserved',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:clients,id',
            'buyer_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update($validated);

        return redirect()->back()->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->back()->with('success', 'Vehicle deleted successfully.');
    }
    public function updateStatus(Request $request, Vehicle $vehicle)
    {
        $request->validate(['status' => 'required|in:in_stock,sold,shipped,reserved']);
        $vehicle->update(['status' => $request->status]);
        return redirect()->back();
    }
}
