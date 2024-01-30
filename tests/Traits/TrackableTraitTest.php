<?php

namespace Junges\TrackableJobs\Tests\Traits;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;
use Junges\TrackableJobs\Enums\TrackedJobStatus;
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

    public function test_job_executes_without_fail(): void
    {
        Event::fake([JobFailed::class]);
        $job = new TestJob();

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        // This is updated by listening to the JobQueued event.
        $this->assertSame(TrackedJobStatus::QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJobStatus::FINISHED, TrackedJob::first()->status);

        Event::assertNotDispatched(JobFailed::class);
    }

    public function test_it_tracks_failed_jobs(): void
    {
        $job = new FailingJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJobStatus::QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJobStatus::FAILED, TrackedJob::first()->status);
    }

    public function test_it_can_get_the_job_output(): void
    {
        $job = new TestJob();

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJobStatus::QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJobStatus::FINISHED, TrackedJob::first()->status);

        $this->assertSame('This is a test job', TrackedJob::first()->output);
    }

    public function test_it_can_get_the_output_for_failed_jobs(): void
    {
        $job = new FailingJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJobStatus::QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJobStatus::FAILED, TrackedJob::first()->status);

        $this->assertSame('This job failed.', TrackedJob::first()->output);
    }

    public function test_job_queued_event_is_send()
    {
        Event::fake([JobQueued::class]);
        $job = new TestJob();

        app(Dispatcher::class)->dispatch($job);

        Event::assertDispatched(JobQueued::class);
    }

    public function test_status_queued_is_updated()
    {
        $job = new TestJob();

        app(Dispatcher::class)->dispatch($job);

        $this->assertSame(TrackedJobStatus::QUEUED, TrackedJob::first()->status);
    }
}
