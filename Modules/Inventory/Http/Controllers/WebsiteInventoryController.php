<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Inventory\Entities\Vehicle;

class WebsiteInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Default to available only, unless specified otherwise
        // Assuming 'available' is a status. Adjust if needed.
        $query->where('status', '!=', 'sold');

        if ($request->filled('make')) {
            $query->where('make', $request->make);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%");
            });
        }

        // Get filter options based on current data (or all data)
        $makes = Vehicle::where('status', '!=', 'sold')->distinct()->pluck('make')->sort()->values();
        $years = Vehicle::where('status', '!=', 'sold')->distinct()->pluck('year')->sort()->values();

        // Sorting
        if ($request->filled('sort_by')) {
            $sortField = $request->sort_by;
            $sortDir = $request->get('sort_dir', 'desc');
            $allowedSorts = ['sale_price', 'year', 'make', 'created_at'];

            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDir);
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $vehicles = $query->with('media')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'makes' => $makes,
            'years' => $years,
            'vehicles' => $vehicles
        ]);
    }
}
