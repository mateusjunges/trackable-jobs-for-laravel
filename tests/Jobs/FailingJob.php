<?php

namespace Junges\TrackableJobs\Tests\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Concerns\Trackable;
use Junges\TrackableJobs\Tests\User;

class FailingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Trackable {
        __construct as __baseConstruct;
    }

    public function __construct(public readonly User $user)
    {
        $this->__baseConstruct();
    }

    public function handle()
    {
        $this->fail(new Exception('This job failed.'));
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
