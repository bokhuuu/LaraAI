<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class HealthCheckController extends Controller
{
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
            Prism::text()
                ->using(Provider::from(config('ai.providers.default')), config('ai.models.text'))
                ->withPrompt('ping')
                ->asText();
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
