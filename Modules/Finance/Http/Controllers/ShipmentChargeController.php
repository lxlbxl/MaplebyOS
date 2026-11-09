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
        $data = $this->validated($request, $shipment->cargo_type);
        $data['created_by'] = $request->user()?->id;
        if (empty($data['currency']) && $shipment->isGeneralCargo()) {
            $data['currency'] = 'NGN'; // Clearing is paid and billed in naira unless stated.
        }
        $shipment->charges()->create($data);

        return redirect()->back()->with('success', ($data['kind'] === 'cost' ? 'Cost' : 'Charge') . ' added.');
    }

    public function update(Request $request, ShipmentCharge $charge)
    {
        abort_if($charge->invoice_id && $charge->invoice?->status !== 'draft', 422, 'This line is on a sent invoice and can no longer be edited.');
        $this->authorizeKind($request, $charge->kind);
        $charge->update($this->validated($request, $charge->shipment?->cargo_type));
        $charge->invoice?->recalculate();

        return redirect()->back()->with('success', 'Line updated.');
    }

    public function destroy(Request $request, ShipmentCharge $charge)
    {
        $this->authorizeKind($request, $charge->kind);
        abort_if($charge->invoice_id && $charge->invoice?->status !== 'draft', 422, 'This line is on a sent invoice and can no longer be removed.');
        $invoice = $charge->invoice;
        $charge->delete();
        $invoice?->recalculate();

        return redirect()->back()->with('success', 'Line removed.');
    }

    /**
     * Cost lines (what Mapleby pays) need money.edit_costs; charge lines
     * (what the customer pays) need finance.manage.
     */
    private function authorizeKind(Request $request, ?string $kind): void
    {
        $permission = $kind === ShipmentCharge::KIND_COST ? 'money.edit_costs' : 'finance.manage';
        abort_unless($request->user()?->can($permission), 403, $kind === ShipmentCharge::KIND_COST
            ? 'Only the CEO can add or change costs.'
            : 'You cannot add or change customer charges.');
    }

    private function validated(Request $request, ?string $cargoType = null): array
    {
        $kind = $request->input('kind');
        $this->authorizeKind($request, $kind);

        return $request->validate([
            'kind' => 'required|in:cost,charge',
            'category' => ['required', Rule::in(ShipmentCharge::categoriesFor($cargoType)[$kind] ?? [])],
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'incurred_on' => 'nullable|date',
        ]);
    }
}
