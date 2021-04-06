<?php

namespace Junges\TrackableJobs\Jobs\Middleware;

use Throwable;

class TrackedJobMiddleware
{
    public function handle($job, $next)
    {
        $job->trackedJob->markAsStarted();

        try {
            $response = $next($job);

            $job->trackedJob->markAsFinished($response);
        } catch (Throwable $exception) {
            $job->fail($exception);
        }
    }
}