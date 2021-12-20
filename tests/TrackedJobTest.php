<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Junges\TrackableJobs\Exceptions\UuidNotConfiguredException;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\FailingJob;
use Junges\TrackableJobs\Tests\Jobs\TestJob;
use Spatie\TestTime\TestTime;

class TrackedJobTest extends TestCase
{
    use RefreshDatabase;

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('trackable-jobs.using_uuid', false);
    }

    public function test_it_can_get_the_correct_morph()
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

    public function test_it_can_get_the_correct_morph_for_failed_jobs()
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

    public function test_it_can_get_the_correct_job_duration()
    {
        TestTime::freeze();

        $job = new TestJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame('1h', TrackedJob::first()->duration);
    }

    public function test_it_throws_exception_if_finding_by_uuid()
    {
        $this->expectException(UuidNotConfiguredException::class);

        TrackedJob::findByUuid(Str::uuid());
    }

    public function test_it_can_prune_models()
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
}
