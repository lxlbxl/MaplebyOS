<?php

namespace Modules\CRM\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\CRM\Entities\Lead;
use Modules\CRM\Http\Requests\CaptureLeadRequest;
use Modules\Core\Entities\ApiLog;
use Illuminate\Support\Facades\DB;
use Exception;

class LeadApiController extends Controller
{
    /**
     * List all leads (Protected)
     */
    public function index()
    {
        return response()->json(Lead::latest()->paginate(20));
    }

    /**
     * Capture a new lead from external sources.
     */
    public function capture(CaptureLeadRequest $request): JsonResponse
    {
        $startTime = microtime(true);
        $status = 201;
        $responsePayload = [];

        try {
            $data = $request->validated();

            // Map request data to Lead model attributes
            $leadData = [
                'title' => 'Web Inquiry: ' . ($data['contact_name'] ?? 'Unknown'),
                'contact_name' => $data['contact_name'],
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'service_type' => $data['service_type'],
                'description' => $data['description'] ?? null,
                'source' => $data['source'] ?? 'website',
                'website_form_data' => $data['website_form_data'] ?? [],
                'status' => 'new',
                'priority' => 'medium', // Default priority
            ];

            $lead = Lead::create($leadData);

            $responsePayload = [
                'message' => 'Lead captured successfully',
                'data' => [
                    'lead_id' => $lead->id,
                    'reference' => 'LEAD-' . str_pad($lead->id, 5, '0', STR_PAD_LEFT),
                ]
            ];

        } catch (Exception $e) {
            $status = 500;
            $responsePayload = [
                'message' => 'Failed to capture lead',
                'error' => $e->getMessage(),
            ];
        }

        // Log the API request
        $this->logRequest($request, $status, $responsePayload, $startTime);

        return response()->json($responsePayload, $status);
    }

    private function logRequest($request, $status, $response, $startTime)
    {
        try {
            ApiLog::create([
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'ip_address' => $request->ip(),
                'request_payload' => json_encode($request->all()),
                'response_status' => $status,
                'response_payload' => json_encode($response),
                'duration_ms' => round((microtime(true) - $startTime) * 1000),
                'api_key' => $request->header('X-API-KEY'),
            ]);
        } catch (Exception $e) {
            // Failsafe: Don't break the API response if logging fails
            report($e);
        }
    }
}
