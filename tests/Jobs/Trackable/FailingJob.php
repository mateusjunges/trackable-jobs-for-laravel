<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Jobs\Trackable;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class FailingJob.
 */
class FailingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use TrackableAuto;

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->fail(new Exception('This job failed.'));
    }
}
