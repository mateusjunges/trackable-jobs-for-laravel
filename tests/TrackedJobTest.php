<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Junges\TrackableJobs\Exceptions\UuidNotConfiguredException;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\Trackable\FailingJob;
use Junges\TrackableJobs\Tests\Jobs\Trackable\TestJob;
use Spatie\TestTime\TestTime;

/**
 * Class TrackedJobTest
 * @package Junges\TrackableJobs\Tests
 */
class TrackedJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param $app
     */
    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('trackable-jobs.using_uuid', false);
    }

    /**
     * @return void
     */
    public function test_it_can_get_the_correct_morph(): void
    {
        $job = new TestJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FINISHED, TrackedJob::first()->status);

        $this->assertIsObject(TrackedJob::first()->trackable);

        $this->assertSame($this->user->id, TrackedJob::first()->trackable->id);

        $this->assertSame($this->user->name, TrackedJob::first()->trackable->name);
    }

    /**
     * @return void
     */
    public function test_it_can_get_the_correct_morph_for_failed_jobs(): void
    {
        $job = new FailingJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FAILED, TrackedJob::first()->status);

        $this->assertIsObject(TrackedJob::first()->trackable);

        $this->assertSame($this->user->id, TrackedJob::first()->trackable->id);

        $this->assertSame($this->user->name, TrackedJob::first()->trackable->name);
    }

    /**
     * @return void
     */
    public function test_it_can_get_the_correct_job_duration(): void
    {
        TestTime::freeze();

        $job = new TestJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame('1h', TrackedJob::first()->duration);
    }

    /**
     * @return void
     * @throws UuidNotConfiguredException
     */
    public function test_it_throws_exception_if_finding_by_uuid(): void
    {
        $this->expectException(UuidNotConfiguredException::class);

        TrackedJob::findByUuid(Str::uuid()->toString());
    }
}
