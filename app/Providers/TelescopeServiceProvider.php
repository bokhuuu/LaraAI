<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

/**
 * Configures Telescope debugging dashboard behavior per environment.
 *
 * Local: records everything for full visibility during development.
 * Production: records only exceptions, failures, and monitored tags.
 * Sensitive request data (cookies, tokens) stripped in production automatically.
 */
class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Set up Telescope filters - limit what gets recorded in production
     * to avoid filling the DB and exposing sensitive data.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal ||
                $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    /**
     * Strip cookies and CSRF tokens from logged requests in production.
     * Skipped in local environment where full request data is useful for debugging.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Restrict Telescope dashboard access in production.
     * Add authorized emails to the array to grant access.
     * Everyone can access it in local environment.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function (User $user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}
