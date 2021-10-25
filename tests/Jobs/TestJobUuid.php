<?php

namespace Junges\TrackableJobs\Tests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Concerns\Trackable;
use Spatie\TestTime\TestTime;

class TestJobUuid implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Trackable;

    public function handle()
    {
        TestTime::addHour();

        return 'This is a test job';
    }
}
