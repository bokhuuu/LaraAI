<?php

namespace App\AI\Services;

use Illuminate\Support\Facades\Cache;

/**
 * RateLimitingService
 *
 * Controls AI call frequency per user per feature.
 * Uses Redis cache with TTL for automatic counter reset.
 *
 * Limits configured in config/ai.php rate_limits section.
 * TEMPLATE USAGE: Call check() before AI call, increment() after success.
 * Add new features to config/ai.php rate_limits array.
 */
class RateLimitingService
{
    /** Check if user is under their rate limit for this feature. */
    public function check(string $feature, string $userId): bool
    {
        $current = Cache::get($this->key($feature, $userId), 0);
        return $current < $this->getLimit($feature)['max'];
    }

    /** Increment usage counter after successful AI call. */
    public function increment(string $feature, string $userId): void
    {
        $key = $this->key($feature, $userId);
        $ttl = $this->getLimit($feature)['ttl'];

        if (!Cache::has($key)) {
            Cache::put($key, 1, $ttl);
        } else {
            Cache::increment($key);
        }
    }

    /** Get remaining calls before rate limit is reached. */
    public function remaining(string $feature, string $userId): int
    {
        $current = Cache::get($this->key($feature, $userId), 0);
        return max(0, $this->getLimit($feature)['max'] - $current);
    }

    /** Build unique cache key per user per feature. */
    private function key(string $feature, string $userId): string
    {
        return "ai_rate_limit:{$userId}:{$feature}";
    }

    /** Get limit config for feature. Falls back to 10 calls/hour if not configured. */
    private function getLimit(string $feature): array
    {
        return config("ai.rate_limits.{$feature}", ['max' => 10, 'ttl' => 3600]);
    }
}
