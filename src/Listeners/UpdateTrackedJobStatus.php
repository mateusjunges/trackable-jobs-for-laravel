<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Listeners;

use Illuminate\Queue\Events\JobQueued;
use Junges\TrackableJobs\Contracts\TrackableJobContract;
use Junges\TrackableJobs\TrackableJob;

final class UpdateTrackedJobStatus
{
    public function handle(JobQueued $event): void
    {
        if (! $event->job instanceof TrackableJob) {
            return;
        }

        $trackedJob = $event->job->trackedJob();
        assert($trackedJob instanceof TrackableJobContract);

        $trackedJob->markAsQueued($event->id);

        if (method_exists($trackedJob, 'setQueue') && isset($event->queue)) {
            $trackedJob->setQueue($event->queue);
        }
    }
}
