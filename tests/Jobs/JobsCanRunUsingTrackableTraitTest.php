<?php

namespace Junges\TrackableJobs\Tests\Jobs;

use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\TestCase;
use Junges\TrackableJobs\Tests\TestJob;

class JobsCanRunUsingTrackableTraitTest extends TestCase
{
    public function test_job_executes_without_fail()
    {
        $job = (new TestJob($this->user))->handle();

        $this->assertSame($job, 'This is a test job');
        $this->assertCount(1, TrackedJob::all());
        $this->assertSame(TrackedJob::STATUS_FINISHED, TrackedJob::first()->status);
    }
}