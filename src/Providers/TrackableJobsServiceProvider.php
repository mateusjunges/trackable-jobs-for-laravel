<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Providers;

use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Junges\TrackableJobs\Contracts\TrackableJobContract;
use Junges\TrackableJobs\Listeners\UpdateTrackedJobStatus;
use Junges\TrackableJobs\Models\TrackedJob;

class TrackableJobsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/trackable-jobs.php' => config_path('trackable-jobs.php'),
        ], 'trackable-jobs-assets');

        $publishedAt = time();

        $this->publishes([
            __DIR__.'/../../database/migrations/laravel_trackable_create_tracked_jobs_table.php' => database_path('migrations/'.date('Y_m_d_His', $publishedAt).'_create_tracked_jobs_table.php'),
            __DIR__.'/../../database/migrations/add_queue_column_to_tracked_jobs_table.php' => database_path('migrations/'.date('Y_m_d_His', $publishedAt + 1).'_add_queue_column_to_tracked_jobs_table.php'),
        ], 'trackable-jobs-assets');

        Event::listen(JobQueued::class, UpdateTrackedJobStatus::class);
    }

    public function register()
    {
        $this->app->bind(TrackableJobContract::class, TrackedJob::class);
    }
}
