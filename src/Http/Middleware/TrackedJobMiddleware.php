<?php

namespace Junges\TrackableJobs\Http\Middleware;

use Throwable;

class TrackedJobMiddleware
{
    public function handle($job, $next)
    {
        $job->trackedJob->markAsStarted();

        try {
            $response = $next($job);

            $job->markAsFinished($response);
        } catch (Throwable $exception) {
            $job->fail($exception);
        }
    }
}