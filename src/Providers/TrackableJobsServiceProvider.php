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
            __DIR__ . '/../../config/trackable-jobs.php' => config_path('trackable-jobs.php'),
        ], 'trackable-jobs-config');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        Event::listen(
            \Illuminate\Queue\Events\JobQueued::class,
            \Junges\TrackableJobs\Listeners\QueueTrackedJob::class
        );

        // Injection of trackedJob into the payload
//        \Illuminate\Queue\Queue::createPayloadUsing(function ($connection, $queue, $payload) {
//
//            $jobDataCommand = $payload['data']['command'];
//            if (isset($jobDataCommand->trackable))
//            {
//                $trackedJob = TrackedJob::create([
//                    'trackable_id' => $jobDataCommand->trackable ? $jobDataCommand->trackable->id ?? $jobDataCommand->trackable->uuid : null,
//                    'trackable_type' => $jobDataCommand->trackable?->getMorphClass(),
//                    'job_id' => $payload['id'],
//                    'name' => get_class($jobDataCommand),
//                    'name' => $payload['displayName'],
//                ]);
//
//                return ['trackedJobId' => $trackedJob->id];
//            }
//
//            return [];
//        });
    }

    public function register(): void
    {
        $this->app->bind(TrackableJobContract::class, TrackedJob::class);
    }
}
