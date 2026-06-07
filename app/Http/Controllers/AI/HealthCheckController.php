<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\AI\Services\EmbeddingService;
use App\Models\Document;

/**
 * Health check endpoint for monitoring tools and load balancers.
 *
 * Checks every critical service - database, Redis, queue, and AI provider.
 * Returns 200 if all critical services are healthy, 503 if any are down.
 * Queue failure is a warning only and does not affect the overall status.
 *
 * Endpoint: GET /api/ai/health
 */
class HealthCheckController extends Controller
{
    /**
     * Run all service checks and return a status report.
     * AI check generates and immediately deletes a real embedding
     * to verify the full pipeline works - not just that the server is reachable.
     */
    public function check(): JsonResponse
    {
        $services = [];
        $healthy = true;

        try {
            DB::connection()->getPdo();
            $services['database'] = 'ok';
        } catch (\Throwable $e) {
            $services['database'] = 'failed: ' . $e->getMessage();
            $healthy = false;
        }

        try {
            Cache::store('redis')->put('health_check', true, 10);
            $services['redis'] = 'ok';
        } catch (\Throwable $e) {
            $services['redis'] = 'failed: ' . $e->getMessage();
            $healthy = false;
        }

        try {
            \Artisan::call('horizon:status');
            $output = trim(\Artisan::output());
            $services['queue'] = str_contains(strtolower($output), 'running') ? 'ok' : 'warning: ' . $output;
        } catch (\Throwable $e) {
            $services['queue'] = 'warning: ' . $e->getMessage();
        }

        try {
            $document = app(EmbeddingService::class)->generateAndStore('health check test');
            $document->delete();
            $services['ai'] = 'ok';
        } catch (\Throwable $e) {
            $services['ai'] = 'failed: ' . $e->getMessage();
            $healthy = false;
        }

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'services' => $services,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }
}
