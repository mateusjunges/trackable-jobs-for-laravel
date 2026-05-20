<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Jobs\Middleware;

use Junges\TrackableJobs\TrackableJob;

class TrackedJobMiddleware
{
    public function handle(TrackableJob $job, callable $next): void
    {
        $queueJob = $job->job;

        if ($queueJob !== null && $queueJob->attempts() > 1) {
            $job->trackedJob?->markAsRetrying($queueJob->attempts());
        } else {
            $job->trackedJob?->markAsStarted();
        }

        $response = $next($job);

        if ($queueJob !== null && $queueJob->isReleased()) {
            $job->trackedJob?->markAsRetrying($queueJob->attempts());
        } else {
            $job->trackedJob?->markAsFinished($response);
        }
    }
}
