<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Jobs\Trackable;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Traits\Trackable;
use Spatie\TestTime\TestTime;

/**
 * Class TestJob.
 * @package Junges\TrackableJobs\Tests\Jobs\Trackable
 */
class TestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use TrackableAuto;


    /**
     * @return string
     */
    public function handle(): string
    {
        TestTime::addHour();

        return 'This is a test job';
    }
}
