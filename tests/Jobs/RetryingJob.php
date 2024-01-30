<?php

namespace Junges\TrackableJobs\Tests\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Concerns\Trackable;

final class RetryingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Trackable;

    public int $tries = 3;

    public function handle(): void
    {
        throw new Exception('This job fails, it will be retried 3 times.');
    }
}
