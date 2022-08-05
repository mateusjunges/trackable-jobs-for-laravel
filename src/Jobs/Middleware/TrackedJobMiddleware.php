<?php

namespace Junges\TrackableJobs\Jobs\Middleware;

class TrackedJobMiddleware
{
    public function handle($job, $next)
    {
        $job->trackedJob->markAsStarted();

        $response = $next($job);

        if ($job->job->isReleased()) {
            $job->trackedJob->markAsRetrying();
        } else {
            $job->trackedJob->markAsFinished($response);
        }
    }
}
