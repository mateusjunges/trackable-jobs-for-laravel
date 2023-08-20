<?php

namespace Junges\TrackableJobs\Listeners;

use Illuminate\Queue\Events\JobQueued;

class QueueTrackedJob
{
    public function handle(JobQueued $event): void
    {
        if (isset($event->job->trackedJob))
        {
            $event->job->trackedJob->markAsQueued($event->id);
        }
    }
}