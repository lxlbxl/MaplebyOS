<?php

namespace Modules\Operations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Operations\Entities\Shipment;
use Modules\CRM\Entities\Client;
use Illuminate\Support\Facades\Validator;

class WebsiteShipmentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_name' => 'required|string',
            'contact_phone' => 'required|string',
            'contact_email' => 'nullable|email',

            // Vehicle
            'vehicle_year' => 'required|string',
            'vehicle_make' => 'required|string',
            'vehicle_model' => 'required|string',
            'vehicle_vin' => 'required|string',
            'vehicle_color' => 'nullable|string',

            // Shipping
            'shipping_mode' => 'nullable|string',
            'ready_to_go' => 'nullable|string',
            'not_ready_reason' => 'nullable|string',

            // Ownership & Docs
            'ownership_status' => 'nullable|string',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max

            // Logistics
            'tow_required' => 'nullable|string',
            'driver_name' => 'nullable|string',

            'notes' => 'nullable|string',
        ]);

        // Flexible validation for website
        if ($validator->fails() && $request->wantsJson()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle File Upload
        $documentPaths = [];
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store in a specific folder
            $path = $file->storeAs('uploads/shipment_docs/' . date('Y-m'), $filename, 'public');
            $documentPaths[] = '/storage/' . $path;
        }

        // Client Handling
        $client = Client::firstOrCreate(
            ['phone' => $request->contact_phone],
            [
                'contact_person' => $request->contact_name,
                'email' => $request->contact_email,
                'company_name' => $request->contact_name,
                'type' => 'shipper',
                'source' => 'website'
            ]
        );

        // Prepare Description / Notes
        $description = $request->notes . "\n";
        $description .= "Shipping Mode: " . $request->shipping_mode . "\n";
        $description .= "Ready to Go: " . $request->ready_to_go . ($request->ready_to_go === 'No' ? " (Reason: {$request->not_ready_reason})" : "") . "\n";
        $description .= "Ownership Status: " . $request->ownership_status . "\n";

        // Map Fields
        $shipment = Shipment::create([
            'client_id' => $client->id,
            'status' => 'pending', // Pending/Awaiting
            'title' => "Web Request: {$request->vehicle_year} {$request->vehicle_make} {$request->vehicle_model}",
            'vin' => $request->vehicle_vin,
            'vehicle_year' => $request->vehicle_year,
            'vehicle_make' => $request->vehicle_make,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_color' => $request->vehicle_color,
            'tow_required' => $request->tow_required === 'Yes',
            'driver_name' => $request->driver_name,
            'description' => trim($description),
            'ownership_documents' => $documentPaths,
            'origin' => null,
            'destination' => null,
        ]);

        return response()->json([
            'message' => 'Shipment request received.',
            'shipment_id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number,
        ], 201);
    }
}
