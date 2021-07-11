<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Jobs\TrackableAuto;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Traits\TrackableAuto;

/**
 * Class FailingJob.
 * @package Junges\TrackableJobs\Tests\Jobs\TrackableAuto
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
