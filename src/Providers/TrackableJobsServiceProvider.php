<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Providers;

use Illuminate\Support\ServiceProvider;
use Junges\TrackableJobs\Contracts\TrackableJobContract;
use Junges\TrackableJobs\Models\TrackedJob;

/**
 * Class TrackableJobsServiceProvider
 * @package Junges\TrackableJobs\Providers
 */
class TrackableJobsServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/trackable-jobs.php' => config_path('trackable-jobs.php'),
        ], 'trackable-jobs-config');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(TrackableJobContract::class, TrackedJob::class);
    }
}
