<?php

namespace Junges\TrackableJobs\Jobs\Middleware;

class TrackedJobMiddleware
{
    public function handle(mixed $job, callable $next)
    {
        if (!$job->shouldBeTracked()) // why would you use this package if you don't track?
        {
            return $next($job);
        }

        if ($job->job->attempts() > 1)
        {
            $job->trackedJob->markAsRetrying($job->job->attempts());
        } else
        {
            $job->trackedJob->markAsStarted($job->job->getJobId());
        }

        $response = $next($job);

        if ($job->job->isReleased())
        {
            $job->trackedJob->markAsRetrying($job->job->attempts());
        } else
        {
            $job->trackedJob->markAsFinished($response);
        }
    }
}
