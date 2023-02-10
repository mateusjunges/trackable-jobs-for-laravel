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
        ], 'trackable-jobs-assets');

        $this->publishes([
            __DIR__ . '/../../database/migrations/laravel_trackable_create_tracked_jobs_table.php.stub'
                => database_path('migrations/'.date('Y_m_d_His', time()).'_create_tracked_jobs_table.php'),
        ], 'trackable-jobs-assets');
    }

    public function register()
    {
        $this->app->bind(TrackableJobContract::class, TrackedJob::class);
    }
}
