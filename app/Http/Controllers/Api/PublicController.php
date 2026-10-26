<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $lead = \App\Models\Lead::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'source' => 'web',
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Lead submitted successfully',
            'lead_id' => $lead->id,
        ], 201);
    }

    public function getInventory(Request $request)
    {
        $query = \App\Models\InventoryItem::where('is_published', true)
            ->where('status', 'in_stock');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $items = $query->latest()->get();

        return response()->json([
            'data' => $items,
        ]);
    }
}
