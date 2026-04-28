<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Tests\Models\TypedTrackedJob;
use Junges\TrackableJobs\TrackableJob;

/** @extends TrackableJob<TypedTrackedJob> */
final class TypedTrackedModelJob extends TrackableJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void {}

    public function trackedJobKeyAfterSave(): string|int|null
    {
        $trackedJob = $this->trackedJob();
        $trackedJob->save();

        return $trackedJob->getKey();
    }
}
