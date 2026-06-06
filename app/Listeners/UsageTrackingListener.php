<?php

declare(strict_types=1);

namespace App\Listeners;

use App\AI\Services\UsageTrackingService;
use App\Events\AiCallCompleted;

/**
 * Listens for AiCallCompleted events and tracks token usage and cost.
 * Runs synchronously - writing a DB row is fast enough to not need a queue.
 */
class UsageTrackingListener
{
    public function __construct(
        private UsageTrackingService $usageTracker
    ) {}

    /**
     * Handle the event.
     */
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
