<?php

namespace Junges\TrackableJobs\Tests\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Contracts\TrackableContract;
use Junges\TrackableJobs\Tests\User;
use Junges\TrackableJobs\TrackableJob;

class FailingJob extends TrackableJob implements ShouldQueue, TrackableContract
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly User $user)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        throw new Exception('This job failed.');
    }

    public function trackableKey(): string
    {
        return (string) $this->user->id;
    }

    public function trackableType(): ?string
    {
        return $this->user->getMorphClass();
    }
}
