<?php

namespace App\AI\Services;

use Illuminate\Support\Facades\Cache;

class RateLimitingService
{
    public function check(string $feature, string $userId): bool
    {
        $current = Cache::get($this->key($feature, $userId), 0);
        return $current < $this->getLimit($feature)['max'];
    }

    public function increment(string $feature, string $userId): void
    {
        $key = $this->key($feature, $userId);
        $current = Cache::get($key, 0);
        Cache::put($key, $current + 1, $this->getLimit($feature)['ttl']);
    }

    public function remaining(string $feature, string $userId): int
    {
        $current = Cache::get($this->key($feature, $userId), 0);
        return max(0, $this->getLimit($feature)['max'] - $current);
    }

    private function key(string $feature, string $userId): string
    {
        return "ai_rate_limit:{$userId}:{$feature}";
    }

    private function getLimit(string $feature): array
    {
        return config("ai.rate_limits.{$feature}", ['max' => 10, 'ttl' => 3600]);
    }
}
