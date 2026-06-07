<?php

declare(strict_types=1);

namespace App\Listeners;

use App\AI\Services\UsageTrackingService;
use App\Events\AiCallCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends a Slack alert when a single AI call exceeds the cost threshold.
 *
 * Listens for AiCallCompleted events, calculates the call cost and posts
 * a Slack message if the cost exceeds config('ai.cost_alert_threshold').
 * Runs in the background queue so it never slows down the main response.
 */
class CostAlertListener implements ShouldQueue
{
    public function __construct(
        private UsageTrackingService $usageTracker,
    ) {
        $this->costThreshold = (float) config('ai.cost_alert_threshold', 0.01);
    }

    private float $costThreshold;

    /**
     * Calculate the cost of this AI call and alert if it exceeds the threshold.
     * Silently returns if cost is under threshold - no action needed.
     */
    public function handle(AiCallCompleted $event): void
    {
        $cost = $this->usageTracker->calculateCost(
            model: $event->model,
            promptTokens: $event->promptTokens,
            completionTokens: $event->completionTokens,
        );

        if ($cost < $this->costThreshold) {
            return;
        }

        $this->sendSlackAlert($event, $cost);
    }

    /**
     * Post a formatted cost alert to the configured Slack webhook URL.
     * Logs a warning and skips silently if no webhook URL is configured.
     */
    private function sendSlackAlert(AiCallCompleted $event, float $cost): void
    {
        $webhookUrl = config('services.slack.alerts_webhook_url');

        if (! $webhookUrl) {
            Log::warning('Slack webhook URL not configured');

            return;
        }

        Http::post($webhookUrl, [
            'text' => "🚨 *AI Cost Alert*\n" .
                "Feature: `{$event->feature}`\n" .
                "Model: `{$event->model}`\n" .
                "Cost: `\${$cost}`\n" .
                "Tokens: `{$event->promptTokens}` prompt / `{$event->completionTokens}` completion",
        ]);
    }
}
