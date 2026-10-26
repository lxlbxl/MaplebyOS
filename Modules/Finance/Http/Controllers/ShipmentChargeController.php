<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Finance\Entities\ShipmentCharge;
use Modules\Operations\Entities\Shipment;

class ShipmentChargeController extends Controller
{
    public function store(Request $request, Shipment $shipment)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()?->id;
        $shipment->charges()->create($data);

        return redirect()->back()->with('success', ($data['kind'] === 'cost' ? 'Cost' : 'Charge') . ' added.');
    }

    public function update(Request $request, ShipmentCharge $charge)
    {
        abort_if($charge->invoice_id && $charge->invoice?->status !== 'draft', 422, 'This line is on a sent invoice and can no longer be edited.');
        $charge->update($this->validated($request));
        $charge->invoice?->recalculate();

        return redirect()->back()->with('success', 'Line updated.');
    }

    public function destroy(ShipmentCharge $charge)
    {
        abort_if($charge->invoice_id && $charge->invoice?->status !== 'draft', 422, 'This line is on a sent invoice and can no longer be removed.');
        $invoice = $charge->invoice;
        $charge->delete();
        $invoice?->recalculate();

        return redirect()->back()->with('success', 'Line removed.');
    }

    private function validated(Request $request): array
    {
        $kind = $request->input('kind');

        return $request->validate([
            'kind' => 'required|in:cost,charge',
            'category' => ['required', Rule::in(ShipmentCharge::CATEGORIES[$kind] ?? [])],
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'incurred_on' => 'nullable|date',
        ]);
    }
}
