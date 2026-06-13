<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Cache;

/**
 * Maintains running monthly AI cost and token totals in Redis.
 *
 * Triggered automatically whenever a new AiUsageLog entry is created.
 * Incrementing Redis values is far faster than summing DB rows,
 * making instant budget checks possible without expensive queries.
 * Token total is read by TokenBudgetService to enforce monthly limits.
 */
class AiUsageLogObserver
{
    /**
     * Add this log entry's cost to the current month's Redis total.
     * Key expires at end of month so the counter resets automatically.
     */
    public function created(AiUsageLog $aiUsageLog): void
    {
        $month = now()->format('Y-m');

        $cacheKey = "ai_monthly_cost:{$month}";
        $current = (float) Cache::get($cacheKey, 0.0);
        Cache::put($cacheKey, $current + $aiUsageLog->cost_usd, ttl: now()->endOfMonth());

        $tokenKey = "ai_monthly_tokens:{$month}";
        $currentTokens = (int) Cache::get($tokenKey, 0);
        Cache::put($tokenKey, $currentTokens + $aiUsageLog->total_tokens, ttl: now()->endOfMonth());
    }
}
