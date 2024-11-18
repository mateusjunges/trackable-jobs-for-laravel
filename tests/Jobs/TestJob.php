<?php

namespace Junges\TrackableJobs\Tests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\TrackableJob;
use Spatie\TestTime\TestTime;

class TestJob extends TrackableJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public $output = 'This is a test job.')
    {
        parent::__construct();
    }

    public function handle(): array
    {
        TestTime::addHour();

        return [
            'message' => 'This is a test job',
            'exit_code' => 1,
        ];
    }
}
