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
 * Class TestJobUuid
 * @package Junges\TrackableJobs\Tests\Jobs\Trackable
 */
class TestJobUuid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    /**
     * @return string
     */
    public function handle(): string
    {
        TestTime::addHour();

        return 'This is a test job';
    }
}
