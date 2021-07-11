<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Traits\Trackable;

/**
 * Class FailingJob
 * @package Junges\TrackableJobs\Tests\Jobs
 */
class FailingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->fail(new Exception('This job failed.'));
    }
}
