<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Modules\Inventory\Entities\Vehicle;
use Modules\CRM\Entities\Lead;
use Modules\Operations\Entities\Shipment;
use Modules\CRM\Entities\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    /**
     * Serve static HTML files from the website folder
     */
    public function serveStatic($page = 'index')
    {
        $websitePath = base_path('website');
        $filePath = $websitePath . '/' . $page . '.html';

        if (!File::exists($filePath)) {
            abort(404, 'Page not found');
        }

        $content = File::get($filePath);

        return response($content, 200)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Serve the inventory page with dynamic data injected
     */
    public function inventory()
    {
        $websitePath = base_path('website');
        $filePath = $websitePath . '/inventory.html';

        if (!File::exists($filePath)) {
            abort(404, 'Page not found');
        }

        $content = File::get($filePath);

        return response($content, 200)
            ->header('Content-Type', 'text/html');
    }

    /**
     * API: Get inventory vehicles (JSON)
     */
    public function getInventory(Request $request)
    {
        $query = Vehicle::query()->where('status', 'in_stock');

        // Search functionality
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%");
            });
        }

        // Filter by make
        if ($request->make) {
            $query->where('make', $request->make);
        }

        // Filter by year
        if ($request->year) {
            $query->where('year', $request->year);
        }

        // Filter by condition
        if ($request->condition) {
            $query->where('condition', $request->condition);
        }

        $vehicles = $query->with('media')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 12);

        // Get unique makes and years for filters
        $makes = Vehicle::where('status', 'in_stock')
            ->distinct()
            ->pluck('make')
            ->filter()
            ->sort()
            ->values();

        $years = Vehicle::where('status', 'in_stock')
            ->distinct()
            ->pluck('year')
            ->filter()
            ->sortDesc()
            ->values();

        return response()->json([
            'vehicles' => $vehicles,
            'makes' => $makes,
            'years' => $years,
        ]);
    }

    /**
     * API: Submit contact/enquiry form - creates a lead
     */
    public function submitEnquiry(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:100',
            'service_type' => 'nullable|string|max:100',
            'message' => 'required|string|max:5000',
            'company' => 'nullable|string|max:255',
        ]);

        Lead::create([
            'title' => 'Website Enquiry from ' . $validated['name'],
            'source' => 'website',
            'service_type' => $validated['service_type'] ?? $validated['subject'] ?? 'general',
            'status' => 'new',
            'priority' => 'medium',
            'contact_name' => $validated['name'],
            'contact_email' => $validated['email'],
            'contact_phone' => $validated['phone'] ?? null,
            'description' => $validated['message'],
            'website_form_data' => $validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your enquiry! We will get back to you soon.',
        ]);
    }

    /**
     * API: Submit quote request form - creates a lead
     */
    public function submitQuote(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:100',
            'origin' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'shipping_date' => 'nullable|string|max:50',
            'cargo_details' => 'nullable|array',
        ]);

        $description = "Quote Request\n";
        $description .= "Service: " . ($validated['service_type'] ?? 'Not specified') . "\n";
        $description .= "Origin: " . ($validated['origin'] ?? 'Not specified') . "\n";
        $description .= "Destination: " . ($validated['destination'] ?? 'Not specified') . "\n";
        $description .= "Shipping Date: " . ($validated['shipping_date'] ?? 'Not specified') . "\n";

        if (!empty($validated['cargo_details'])) {
            $description .= "\nCargo Details:\n";
            foreach ($validated['cargo_details'] as $key => $value) {
                $description .= "- " . ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
            }
        }

        Lead::create([
            'title' => 'Quote Request from ' . $validated['name'],
            'source' => 'website',
            'service_type' => $validated['service_type'] ?? 'quote',
            'status' => 'new',
            'priority' => 'high',
            'contact_name' => $validated['name'],
            'contact_email' => $validated['email'],
            'contact_phone' => $validated['phone'] ?? null,
            'description' => $description,
            'website_form_data' => $validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your quote request has been submitted. We will contact you shortly.',
        ]);
    }

    /**
     * Serve the shipment request page
     */
    public function shipmentRequestPage()
    {
        $websitePath = base_path('website');
        $filePath = $websitePath . '/ship.html';

        if (!File::exists($filePath)) {
            // Return a generated ship page if it doesn't exist
            return $this->generateShipmentPage();
        }

        $content = File::get($filePath);

        return response($content, 200)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Generate shipment request page dynamically
     */
    private function generateShipmentPage()
    {
        // Will be created as static HTML file
        abort(404, 'Shipment request page not found');
    }

    /**
     * API: Submit shipment request form - creates a shipment with pending status
     */
    public function submitShipmentRequest(Request $request)
    {
        $validated = $request->validate([
            // Contact info
            'shipper_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',

            // Vehicle info
            'car_make_model' => 'required|string|max:255',
            'colour' => 'nullable|string|max:100',
            'vin' => 'nullable|string|max:50',
            'vehicle_year' => 'nullable|string|max:10',

            // Shipping info
            'mode_of_shipping' => 'required|string|in:ADGOD,CONTAINER,RORO,UNSURE,REPAIR',
            'ready_to_go' => 'required|string|in:YES,NO,NA',
            'if_no_why' => 'nullable|string|max:500',

            // Ownership
            'ownership' => 'required|string|in:YES,NO,BILL_OF_SALES',
            'ownership_location' => 'nullable|string|in:WASSAP,FILE,DRIVE',

            // Additional info
            'extra_notes' => 'nullable|string|max:2000',
            'tow_truck' => 'required|string|in:YES,NO,UNSURE_NA',
            'driver_name' => 'nullable|string|max:255',

            // Documents
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        // Handle file uploads
        $documentPaths = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $filename = Str::uuid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('shipment-documents', $filename, 'public');
                $documentPaths[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        // Parse car make and model
        $carMakeModel = $validated['car_make_model'];
        $makeParts = explode(' ', $carMakeModel, 2);
        $make = $makeParts[0] ?? $carMakeModel;
        $model = $makeParts[1] ?? '';

        // Create or find client by phone
        $client = Client::firstOrCreate(
            ['phone' => $validated['phone_number']],
            [
                'contact_person' => $validated['shipper_name'],
                'type' => 'buyer',
                'source' => 'website',
                'is_active' => true,
            ]
        );

        // Create shipment with pending/awaiting status
        $shipment = Shipment::create([
            'title' => 'Shipment Request: ' . $validated['car_make_model'],
            'client_id' => $client->id,
            'status' => 'pending',
            'service_type' => $validated['mode_of_shipping'],
            'vin' => $validated['vin'] ?? null,
            'vehicle_year' => $validated['vehicle_year'] ?? null,
            'vehicle_make' => $make,
            'vehicle_model' => $model,
            'vehicle_color' => $validated['colour'] ?? null,
            'tow_required' => $validated['tow_truck'] === 'YES' ? true : ($validated['tow_truck'] === 'NO' ? false : null),
            'driver_name' => $validated['driver_name'] ?? null,
            'ownership_documents' => [
                'has_ownership' => $validated['ownership'],
                'location' => $validated['ownership_location'] ?? null,
                'uploaded_files' => $documentPaths,
            ],
            'description' => $this->buildShipmentDescription($validated),
            'location_notes' => $validated['extra_notes'] ?? null,
        ]);

        // Also create a lead for tracking
        Lead::create([
            'client_id' => $client->id,
            'title' => 'Shipment Request: ' . $validated['car_make_model'],
            'source' => 'website',
            'service_type' => $validated['mode_of_shipping'],
            'status' => 'new',
            'priority' => 'high',
            'contact_name' => $validated['shipper_name'],
            'contact_phone' => $validated['phone_number'],
            'description' => $this->buildShipmentDescription($validated),
            'website_form_data' => array_merge($validated, [
                'uploaded_documents' => $documentPaths,
                'shipment_id' => $shipment->id,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your shipment request has been submitted successfully! Our team will review it and contact you soon.',
            'shipment_number' => $shipment->shipment_number,
        ]);
    }

    /**
     * Build a human-readable description for the shipment
     */
    private function buildShipmentDescription(array $data): string
    {
        $lines = [];
        $lines[] = "Vehicle: {$data['car_make_model']}";

        if (!empty($data['colour'])) {
            $lines[] = "Color: {$data['colour']}";
        }
        if (!empty($data['vin'])) {
            $lines[] = "VIN: {$data['vin']}";
        }

        $lines[] = "Mode of Shipping: {$data['mode_of_shipping']}";
        $lines[] = "Ready to Go: {$data['ready_to_go']}";

        if ($data['ready_to_go'] === 'NO' && !empty($data['if_no_why'])) {
            $lines[] = "Reason not ready: {$data['if_no_why']}";
        }

        $lines[] = "Has Ownership: {$data['ownership']}";

        if (!empty($data['ownership_location'])) {
            $lines[] = "Ownership Location: {$data['ownership_location']}";
        }

        $lines[] = "Tow Truck Required: {$data['tow_truck']}";

        if (!empty($data['driver_name'])) {
            $lines[] = "Driver: {$data['driver_name']}";
        }

        if (!empty($data['extra_notes'])) {
            $lines[] = "\nAdditional Notes: {$data['extra_notes']}";
        }

        return implode("\n", $lines);
    }
}
