<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

/**
 * Configures Horizon dashboard access and failure notifications.
 *
 * Restricts /horizon to authorized emails in production.
 * Sends email and Slack alerts when queue workers fail or go down.
 * Notification destinations configured via .env - no hardcoded values.
 */
class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Wire email and Slack notification channels for queue failure alerts.
     * Both destinations read from .env so they can differ per environment.
     */
    public function boot(): void
    {
        parent::boot();

        Horizon::routeMailNotificationsTo(env('HORIZON_ALERT_EMAIL'));
        Horizon::routeSlackNotificationsTo(env('SLACK_ALERTS_WEBHOOK_URL'));
    }

    /**
     * Restrict Horizon dashboard access to authorized emails only.
     * In production only HORIZON_ALERT_EMAIL can view the dashboard.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            return in_array(optional($user)->email, [
                env('HORIZON_ALERT_EMAIL'),
            ]);
        });
    }
}
