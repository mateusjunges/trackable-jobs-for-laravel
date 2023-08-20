<?php

namespace Junges\TrackableJobs\Providers;


use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Junges\TrackableJobs\Contracts\TrackableJobContract;
use Junges\TrackableJobs\Models\TrackedJob;

class TrackableJobsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/trackable-jobs.php' => config_path('trackable-jobs.php'),
        ], 'trackable-jobs-config');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Event::listen(
            \Illuminate\Queue\Events\JobQueued::class,
            \Junges\TrackableJobs\Listeners\QueueTrackedJob::class
        );
    }

    public function register(): void
    {
        $this->app->bind(TrackableJobContract::class, TrackedJob::class);
    }
}
