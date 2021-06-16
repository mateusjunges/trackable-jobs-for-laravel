<?php

namespace Junges\TrackableJobs\Tests\Traits;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\FailingJob;
use Junges\TrackableJobs\Tests\Jobs\TestJob;
use Junges\TrackableJobs\Tests\TestCase;

class TrackableTraitTest extends TestCase
{
    use RefreshDatabase;

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('trackable-jobs.using_uuid', false);
    }

    public function test_job_executes_without_fail()
    {
        $job = new TestJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FINISHED, TrackedJob::first()->status);

        $this->doesntExpectEvents(JobFailed::class);
    }

    public function test_it_tracks_failed_jobs()
    {
        $job = new FailingJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FAILED, TrackedJob::first()->status);
    }

    public function test_it_can_get_the_job_output()
    {
        $job = new TestJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FINISHED, TrackedJob::first()->status);

        $this->assertSame('This is a test job', TrackedJob::first()->output);
    }

    public function test_it_can_get_the_output_for_failed_jobs()
    {
        $job = new FailingJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FAILED, TrackedJob::first()->status);

        $this->assertSame('This job failed.', TrackedJob::first()->output);
    }
}
