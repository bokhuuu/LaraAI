<?php

namespace App\Providers;

use App\Events\AiCallCompleted;
use App\Listeners\CostAlertListener;
use App\Listeners\UsageTrackingListener;
use App\Models\AiUsageLog;
use App\Observers\AiUsageLogObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wires events, listeners and observers for the AI layer.
 *
 * Registers:
 * - AiCallCompleted → UsageTrackingListener (logs token usage to DB)
 * - AiCallCompleted → CostAlertListener (sends Slack alert if cost exceeds threshold)
 * - AiUsageLog → AiUsageLogObserver (maintains running monthly cost in Redis)
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Register event listeners and model observers.
     * Both listeners respond to the same AiCallCompleted event independently.
     */
    public function boot(): void
    {
        Event::listen(
            AiCallCompleted::class,
            UsageTrackingListener::class,
        );

        Event::listen(
            AiCallCompleted::class,
            CostAlertListener::class,
        );

        AiUsageLog::observe(AiUsageLogObserver::class);
    }
}
