<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Junges\TrackableJobs\Exceptions\UuidNotConfiguredException;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\FailingJob;
use Junges\TrackableJobs\Tests\Jobs\TestJob;
use Junges\TrackableJobs\Tests\Jobs\TestJobWithoutModel;
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

    public function test_it_can_get_the_correct_morph_when_using_custom_morph_map()
    {
        Relation::morphMap([
            'test-morph' => User::class,
        ]);

        $job = new TestJob($this->user);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $this->assertSame(TrackedJob::STATUS_QUEUED, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJob::STATUS_FINISHED, TrackedJob::first()->status);

        $this->assertSame('test-morph', TrackedJob::first()->trackable_type);

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

    public function test_it_will_not_prune_if_prunable_config_is_null()
    {
        TestTime::freeze();

        TrackedJob::factory(10)->create();

        TestTime::addDays(40);

        config()->set('trackable-jobs.prunable_after');

        $this->assertEquals(0, (new TrackedJob)->prunable()->count());
    }

    public function test_i_can_disable_tracking()
    {
        TestJob::dispatchWithoutTracking($this->user, false);

        $this->assertCount(0, TrackedJob::all());
    }

    public function test_i_can_dispatch_one_job_without_tracking_and_the_next_with_tracking()
    {
        TestJob::dispatchWithoutTracking(User::first());

        $this->assertCount(0, TrackedJob::all());

        TestJob::dispatch(User::find(2));

        $this->assertCount(1, TrackedJob::all());

        $this->artisan('queue:work --once')->assertExitCode(0);
        $this->artisan('queue:work --once')->assertExitCode(0);

        /** @var TrackedJob $tracked */
        $tracked = TrackedJob::first();

        $this->assertEquals('This is a test job', $tracked->output);
    }

    public function test_i_can_track_jobs_without_models()
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
}
