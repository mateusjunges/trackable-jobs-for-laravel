<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Jobs\Middleware;

use Junges\TrackableJobs\Traits\Trackable;
use Throwable;

/**
 * Class TrackedJobMiddleware.
 */
class TrackedJobMiddleware
{
    /**
     * @param Trackable $job
     * @param $next
     *
     * @return void
     */
    public function handle($job, $next): void
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
