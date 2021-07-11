<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Traits;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\TrackableAuto\FailingJob;
use Junges\TrackableJobs\Tests\Jobs\TrackableAuto\TestJob;
use Junges\TrackableJobs\Tests\TestCase;

/**
 * Class TrackableTraitTest
 * @package Junges\TrackableJobs\Tests\Traits
 */
class TrackableAutoTraitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param $app
     * @return void
     */
    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('trackable-jobs.using_uuid', false);
    }

    /**
     * @return void
     */
    public function test_job_executes_without_fail(): void
    {
        $job = new TestJob();

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FINISHED, TrackedJob::first()->status);

        $this->doesntExpectEvents(JobFailed::class);
    }

    /**
     * @return void
     */
    public function test_it_tracks_failed_jobs(): void
    {
        $job = new FailingJob();

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FAILED, TrackedJob::first()->status);
    }

    /**
     * @return void
     */
    public function test_it_can_get_the_job_output(): void
    {
        $job = new TestJob();

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FINISHED, TrackedJob::first()->status);

        $this->assertSame('This is a test job', TrackedJob::first()->output);
    }

    /**
     * @return void
     */
    public function test_it_can_get_the_output_for_failed_jobs(): void
    {
        $job = new FailingJob();

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FAILED, TrackedJob::first()->status);

        $this->assertSame('This job failed.', TrackedJob::first()->output);
    }
}
