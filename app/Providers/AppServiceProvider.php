<?php

namespace App\Providers;

use App\Events\AiCallCompleted;
use App\Listeners\CostAlertListener;
use App\Listeners\UsageTrackingListener;
use App\Models\AiUsageLog;
use App\Observers\AiUsageLogObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
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
