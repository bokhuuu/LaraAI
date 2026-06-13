<?php

namespace App\AI\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Cache;

/**
 * Circuit breaker that blocks AI calls when monthly token budget is exceeded.
 *
 * Checks total tokens consumed this month against the configured limit.
 * Uses Redis cache for fast lookups - falls back to DB query if cache is cold.
 * Budget and enabled flag configured in config/ai.php under 'token_budget'.
 *
 * Call check() before every AI call. If it returns false, throw and block.
 * Token counts are tracked automatically via AiUsageLogObserver.
 */
class TokenBudgetService
{
    /**
     * Check if monthly token budget has not been exceeded.
     * Returns true if the call is allowed, false if budget is exhausted.
     * Always returns true if budget checking is disabled in config.
     */
    public function check(): bool
    {
        if (! config('ai.token_budget.enabled', true)) {
            return true;
        }

        $used = $this->getMonthlyTokens();
        $limit = config('ai.token_budget.monthly_limit', 1000000);

        return $used < $limit;
    }

    /**
     * Return how many tokens are left in the monthly budget.
     * Returns null if budget checking is disabled.
     */
    public function remaining(): ?int
    {
        if (! config('ai.token_budget.enabled', true)) {
            return null;
        }

        $limit = config('ai.token_budget.monthly_limit', 1000000);

        return max(0, $limit - $this->getMonthlyTokens());
    }

    /**
     * Get total tokens consumed this month.
     * Reads from Redis cache first - falls back to DB sum if cache is cold.
     * Cache is warm in production because AiUsageLogObserver updates it on every log entry.
     */
    public function getMonthlyTokens(): int
    {
        $month = now()->format('Y-m');
        $cacheKey = "ai_monthly_tokens:{$month}";

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return (int) $cached;
        }

        $total = AiUsageLog::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_tokens');

        Cache::put($cacheKey, $total, ttl: now()->endOfMonth());

        return (int) $total;
    }
}
