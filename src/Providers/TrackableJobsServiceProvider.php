<?php

namespace Junges\TrackableJobs\Providers;

use Illuminate\Support\ServiceProvider;
use Junges\TrackableJobs\Contracts\TrackableJobContract;
use Junges\TrackableJobs\Models\TrackedJob;

class TrackableJobsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/trackable-jobs.php' => config_path('trackable-jobs.php'),
        ], 'trackable-jobs-config');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    public function register()
    {
        $this->app->bind(TrackableJobContract::class, TrackedJob::class);
    }
}
