<?php

namespace Junges\TrackableJobs\Jobs\Middleware;

use Closure;

class TrackedJobMiddleware
{
    public function handle(object $job, Closure $next): void
    {
//        echo 'Attempts # ' . $job->job->attempts() . PHP_EOL;
//        echo 'Job ID # ' . $job->job->getJobId() . PHP_EOL;

        if (!$job->shouldBeTracked()) // why would you use this package if you don't track?
        {
            $next($job);
            return;
        }

        if ($job->job->attempts() > 1)
        {
            $job->trackedJob->markAsRetrying($job->job->attempts());
        } else
        {
            $job->trackedJob->markAsStarted();
        }

        $response = $next($job);

        if ($job->job->isReleased())
        {
            $job->trackedJob->markAsRetrying();
        } else
        {
            $job->trackedJob->markAsFinished($response);
        }
    }
}
