<?php

declare(strict_types=1);

namespace App\Listeners;

use App\AI\Services\UsageTrackingService;
use App\Events\AiCallCompleted;

/**
 * Writes token usage and cost to the database after every AI call.
 *
 * Listens for AiCallCompleted and calls UsageTrackingService::track().
 * Runs synchronously - writing one DB row is fast enough to not need a queue.
 */
class UsageTrackingListener
{
    public function __construct(
        private UsageTrackingService $usageTracker
    ) {}

    /** Record token usage and cost for this AI call to ai_usage_logs. */
    public function handle(AiCallCompleted $event): void
    {
        $this->usageTracker->track(
            feature: $event->feature,
            provider: $event->provider,
            model: $event->model,
            promptTokens: $event->promptTokens,
            completionTokens: $event->completionTokens,
        );
    }
}
