<?php

namespace Junges\TrackableJobs\Tests\Traits;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\FailingJob;
use Junges\TrackableJobs\Tests\TestCase;
use Junges\TrackableJobs\Tests\Jobs\TestJob;

class TrackableTraitTest extends TestCase
{
    public function test_job_executes_without_fail()
    {
        $job = new TestJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertEquals(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJob::STATUS_FINISHED, TrackedJob::first()->status);

        $this->doesntExpectEvents(JobFailed::class);
    }

    public function test_it_tracks_failed_jobs()
    {
        $job = new FailingJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertEquals(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJob::STATUS_FAILED, TrackedJob::first()->status);
    }

    public function test_it_can_get_the_output_for_failed_jobs()
    {
        $job = new FailingJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertEquals(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJob::STATUS_FAILED, TrackedJob::first()->status);

        $this->assertEquals('This job failed.', TrackedJob::first()->output);
    }
}