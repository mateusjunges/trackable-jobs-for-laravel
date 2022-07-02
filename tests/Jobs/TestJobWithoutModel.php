<?php

namespace Junges\TrackableJobs\Tests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\Concerns\Trackable;
use Spatie\TestTime\TestTime;

class TestJobWithoutModel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Trackable {
        __construct as __baseConstruct;
    }

    public function __construct(bool $shouldBeTracked = true)
    {
        $this->__baseConstruct(null, $shouldBeTracked);
    }

    public function handle()
    {
        TestTime::addHour();

        return 'This is a test job without models.';
    }
}
