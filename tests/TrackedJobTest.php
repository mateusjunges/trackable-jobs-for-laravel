<?php declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\Test;
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

        $this->assertSame(TrackedJobStatus::Queued, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame(TrackedJobStatus::Failed, TrackedJob::first()->status);

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

    public function test_it_creates_the_job_with_the_correct_defaults(): void
    {
        $job = new TestJob();

        $this->assertDatabaseHas(TrackedJob::class, [
            'status' => TrackedJobStatus::Created,
            'attempts' => 0,
            'name' => get_class($job),
            'trackable_type' => null,
            'trackable_id' => null,
        ]);
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

    public function test_retry_job_with_attempts_increase_and_it_fails_after_max_attempts()
    {
        $job = new RetryingJob();

        app(Dispatcher::class)->dispatch($job);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJobStatus::Started, TrackedJob::first()->status);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJobStatus::Retrying, TrackedJob::first()->status);
        $this->assertEquals(2, TrackedJob::first()->attempts);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJobStatus::Failed, TrackedJob::first()->status);
    }

    #[Test]
    public function it_can_track_jobs_without_related_models(): void
    {
        $job = new TestJobWithoutModel();

        app(Dispatcher::class)->dispatch($job);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertEquals(TrackedJobStatus::Finished, TrackedJob::first()->status);
    }
}
