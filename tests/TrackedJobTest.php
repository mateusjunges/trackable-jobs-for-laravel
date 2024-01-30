<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Junges\TrackableJobs\Enums\TrackedJobStatus;
use Junges\TrackableJobs\Exceptions\UuidNotConfiguredException;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\FailingJob;
use Junges\TrackableJobs\Tests\Jobs\RetryingJob;
use Junges\TrackableJobs\Tests\Jobs\TestJob;
use Junges\TrackableJobs\Tests\Jobs\TestJobWithoutModel;
use Spatie\TestTime\TestTime;

class TrackedJobTest extends TestCase
{
    use RefreshDatabase;

    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('trackable-jobs.using_uuid', false);
    }

    public function test_it_can_get_the_correct_morph_for_failed_jobs(): void
    {
        $job = new FailingJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJobStatus::QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJobStatus::FAILED, TrackedJob::first()->status);

        $this->assertIsObject(TrackedJob::first()->trackable);

        $this->assertSame($this->user->id, TrackedJob::first()->trackable->id);

        $this->assertSame($this->user->name, TrackedJob::first()->trackable->name);
    }

    public function test_it_can_get_the_correct_job_duration(): void
    {
        TestTime::freeze();

        $job = new TestJob();

        app(Dispatcher::class)->dispatch($job);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame('1h', TrackedJob::first()->duration);
    }

    public function test_it_throws_exception_if_finding_by_uuid(): void
    {
        $this->expectException(UuidNotConfiguredException::class);

        TrackedJob::findByUuid(Str::uuid());
    }

    public function test_it_can_prune_models(): void
    {
        TestTime::freeze();

        TrackedJob::factory(10)->create();

        TestTime::addDays(10);

        TrackedJob::factory(10)->create();

        TestTime::addDays(25);

        TrackedJob::factory(10)->create();

        TestTime::addDays(5);

        config()->set('trackable-jobs.prunable_after', 30);

        $this->assertEquals(20, (new TrackedJob)->prunable()->count());
    }

    public function test_it_will_not_prune_if_prunable_config_is_null(): void
    {
        TestTime::freeze();

        TrackedJob::factory(10)->create();

        TestTime::addDays(40);

        config()->set('trackable-jobs.prunable_after');

        $this->assertEquals(0, (new TrackedJob)->prunable()->count());
    }

    public function test_it_can_disable_tracking(): void
    {
        TestJob::dispatchWithoutTracking();

        $this->assertCount(0, TrackedJob::all());
    }

    public function test_it_can_dispatch_one_job_without_tracking_and_the_next_with_tracking(): void
    {
        TestJob::dispatchWithoutTracking();

        $this->assertCount(0, TrackedJob::all());

        TestJob::dispatch();

        $this->assertCount(1, TrackedJob::all());

        $this->artisan('queue:work --once')->assertExitCode(0);
        $this->artisan('queue:work --once')->assertExitCode(0);

        /** @var TrackedJob $tracked */
        $tracked = TrackedJob::first();

        $this->assertEquals('This is a test job', $tracked->output);
    }

    public function test_it_can_track_jobs_without_models(): void
    {
        TestJobWithoutModel::dispatchWithoutTracking();

        $this->assertCount(0, TrackedJob::all());

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertCount(0, TrackedJob::all());

        TestJobWithoutModel::dispatch();

        $this->assertCount(1, TrackedJob::all());

        $this->artisan('queue:work --once')->assertExitCode(0);

        /** @var TrackedJob $tracked */
        $tracked = TrackedJob::first();

        $this->assertEquals('This is a test job without models.', $tracked->output);
    }

    public function test_retry_job_with_attempts_increase_and_it_fails_after_max_attempts()
    {
        $job = new RetryingJob();

        app(Dispatcher::class)->dispatch($job);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJobStatus::STARTED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJobStatus::RETRYING, TrackedJob::first()->status);
        $this->assertEquals(2, TrackedJob::first()->attempts);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJobStatus::FAILED, TrackedJob::first()->status);
    }
}
