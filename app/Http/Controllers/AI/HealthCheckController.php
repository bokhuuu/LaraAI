<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * HealthCheckController
 *
 * Checks connectivity of all critical services.
 * Returns JSON with status of: database, redis, queue, ai provider.
 *
 * Endpoint: GET /api/ai/health
 * Healthy response: HTTP 200
 * Unhealthy response: HTTP 503
 *
 * TEMPLATE USAGE: Add additional service checks as needed.
 * Used by monitoring tools and load balancers.
 */
class HealthCheckController extends Controller
{
    /**
     * Run all service health checks and return status report.
     * Queue check is a warning only - does not affect overall health status.
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
            $aiUrl = config('ai.providers.default') === 'ollama'
                ? 'http://localhost:11434'
                : 'https://openrouter.ai/api/v1/models';

            $response = \Illuminate\Support\Facades\Http::timeout(3)->get($aiUrl);
            $services['ai'] = $response->successful() ? 'ok' : 'failed: unreachable';
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
