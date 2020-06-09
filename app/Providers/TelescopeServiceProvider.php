<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

class TelescopeServiceProvider extends \Laravel\Telescope\TelescopeServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('telescope.ui-mode') == 'dark') {
            Telescope::night();
        }

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if (\App::environment(['local', 'staging'])) {
                return true;
            }

            return false;
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        if ($this->app->isLocal()) {
            return;
        }

        Telescope::hideRequestParameters(['_token', 'jwt', 'password']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
            'Authorization',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', function ($user) {
            // If needed, add your IP (or office IPs) to allow on non-local environments
            return \in_array(
                $user->email,
                explode('|', config('telescope.auth_emails')),
                true
            );
        });
    }
}
