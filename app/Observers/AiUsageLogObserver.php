<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Cache;

/**
 * On every new log entry, updates the running monthly cost total in Redis.
 * Enables instant budget checks without slow DB aggregate queries.
 */
class AiUsageLogObserver
{
    /**
     * Update running monthly cost total in Redis cache when a new log is created.
     */
    public function created(AiUsageLog $aiUsageLog): void
    {
        $month = now()->format('Y-m');
        $cacheKey = "ai_monthly_cost:{$month}";

        $current = (float) Cache::get($cacheKey, 0.0);
        Cache::put($cacheKey, $current + $aiUsageLog->cost_usd, ttl: now()->endOfMonth());
    }
}
