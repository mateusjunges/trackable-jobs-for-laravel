<?php

namespace Junges\TrackableJobs\Jobs\Middleware;

/** @property-read \Illuminate\Contracts\Queue\Job $job */
class TrackedJobMiddleware
{
    public function handle(mixed $job, callable $next): void
    {
        if (! $job->shouldBeTracked()) {
            $next($job);

            return;
        }

        if ($job->job->attempts() > 1) {
            $job->trackedJob->markAsRetrying($job->job->attempts());
        } else {
            $job->trackedJob->markAsStarted();
        }

        $response = $next($job);

        if ($job->job->isReleased()) {
            $job->trackedJob->markAsRetrying($this->job->attempts());
        } else {
            $job->trackedJob->markAsFinished($response);
        }
    }
}
