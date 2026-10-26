<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Modules\Inventory\Entities\Vehicle;
use Modules\Inventory\Entities\Equipment;
use Modules\Core\Entities\ApiLog;
use Exception;

class InventoryApiController extends Controller
{
    /**
     * Get synchronized inventory data.
     */
    public function sync(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $status = 200;

        try {
            // Cache the inventory response for 5 minutes
            $data = Cache::remember('inventory_sync', 300, function () {
                return [
                    'vehicles' => Vehicle::where('status', 'available')
                        ->select('id', 'vin', 'make', 'model', 'year', 'status')
                        ->get(),
                    'equipment' => Equipment::where('status', 'available')
                        ->select('id', 'serial_number', 'type', 'brand', 'model', 'status')
                        ->get(),
                    'generated_at' => now()->toIso8601String(),
                ];
            });

            $responsePayload = [
                'success' => true,
                'data' => $data,
            ];

        } catch (Exception $e) {
            $status = 500;
            $responsePayload = [
                'success' => false,
                'message' => 'Failed to retrieve inventory',
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
            // Only log summary/metadata for GET requests to avoid filling DB with large payloads
            $logResponse = $response;
            if ($status == 200) {
                $logResponse = ['success' => true, 'count_vehicles' => count($response['data']['vehicles'] ?? []), 'count_equipment' => count($response['data']['equipment'] ?? [])];
            }

            ApiLog::create([
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'ip_address' => $request->ip(),
                'request_payload' => json_encode($request->all()),
                'response_status' => $status,
                'response_payload' => json_encode($logResponse),
                'duration_ms' => round((microtime(true) - $startTime) * 1000),
                'api_key' => $request->header('X-API-KEY'),
            ]);
        } catch (Exception $e) {
            report($e);
        }
    }
}
