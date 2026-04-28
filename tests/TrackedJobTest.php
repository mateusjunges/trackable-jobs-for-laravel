<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Context;
use Junges\TrackableJobs\Enums\TrackedJobStatus;
use Junges\TrackableJobs\Exceptions\TrackableJobsException;
use Junges\TrackableJobs\Exceptions\UuidNotConfiguredException;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\ContextAwareTestJob;
use Junges\TrackableJobs\Tests\Jobs\FailingJob;
use Junges\TrackableJobs\Tests\Jobs\RetryingJob;
use Junges\TrackableJobs\Tests\Jobs\TestJob;
use Junges\TrackableJobs\Tests\Jobs\TestJobWithoutModel;
use Junges\TrackableJobs\Tests\Jobs\TypedTrackedModelJob;
use Junges\TrackableJobs\Tests\Models\TypedTrackedJob;
use Junges\TrackableJobs\Tests\Support\Fakes\RetryingWriterAwareTrackedJobFake;
use Junges\TrackableJobs\Tests\Support\Fakes\WriterAwareTrackedJobFake;
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
        $job->trackedJob();

        $this->assertDatabaseHas(TrackedJob::class, [
            'status' => TrackedJobStatus::Created,
            'attempts' => 0,
            'name' => get_class($job),
            'trackable_type' => null,
            'trackable_id' => null,
        ]);
    }

    public function test_it_does_not_serialize_tracked_job_model_into_payload(): void
    {
        $job = new TestJob();

        $serializedJob = serialize($job);

        $this->assertStringNotContainsString(
            'Junges\\TrackableJobs\\Models\\TrackedJob',
            $serializedJob
        );
    }

    public function test_it_does_not_fail_during_unserialize_when_tracked_job_row_is_missing(): void
    {
        $job = new TestJob();
        $job->trackedJob();
        $trackedJobId = $job->trackedJobId;
        $serializedJob = serialize($job);

        TrackedJob::query()->findOrFail($trackedJobId)->delete();

        $restoredJob = unserialize($serializedJob);

        $this->assertInstanceOf(TestJob::class, $restoredJob);
        $this->assertSame($trackedJobId, $restoredJob->trackedJobId);
    }

    public function test_it_throws_model_not_found_only_when_tracked_job_is_explicitly_resolved(): void
    {
        $job = new TestJob();
        $job->trackedJob();
        $trackedJobId = $job->trackedJobId;
        $serializedJob = serialize($job);

        TrackedJob::query()->findOrFail($trackedJobId)->delete();

        $restoredJob = unserialize($serializedJob);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [Junges\\TrackableJobs\\Models\\TrackedJob]');

        $restoredJob->trackedJob();
    }

    public function test_it_adds_trackable_job_id_to_context_and_restores_context_after_execution(): void
    {
        Context::add('trackable_job_id', 'outer');

        $job = new ContextAwareTestJob();
        $trackedJobId = $job->trackedJobId;

        app(Dispatcher::class)->dispatch($job);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertSame('outer', Context::get('trackable_job_id'));
        $this->assertEquals(
            $trackedJobId,
            TrackedJob::firstOrFail()->output['trackable_job_id']
        );
    }

    public function test_it_supports_typed_tracked_job_models(): void
    {
        config()->set('trackable-jobs.model', TypedTrackedJob::class);

        $job = new TypedTrackedModelJob();
        $trackedJob = $job->trackedJob();

        $this->assertInstanceOf(TypedTrackedJob::class, $trackedJob);
        $this->assertSame($trackedJob->getKey(), $job->trackedJobKeyAfterSave());
    }

    public function test_default_tracking_context_is_unchanged_when_null(): void
    {
        config()->set('trackable-jobs.tracking_context', null);

        $job = new TestJob();

        $this->assertNotNull($job->trackedJobId);
        $this->assertCount(1, TrackedJob::all());
    }

    public function test_tracking_context_resolver_wraps_create_and_find(): void
    {
        $invocations = 0;

        config()->set('trackable-jobs.tracking_context', function ($job, callable $callback) use (&$invocations) {
            $invocations++;

            return $callback();
        });

        $job = new TestJob();
        $this->assertSame(1, $invocations);

        $job->trackedJob();
        $this->assertSame(2, $invocations);
    }

    public function test_it_throws_when_tracking_context_config_is_not_callable(): void
    {
        config()->set('trackable-jobs.tracking_context', 'not-callable');

        $this->expectException(TrackableJobsException::class);
        $this->expectExceptionMessage('Invalid configuration for "trackable-jobs.tracking_context": expected callable|null.');

        new TestJob();
    }

    public function test_it_uses_write_pdo_when_resolving_existing_tracked_job_id(): void
    {
        WriterAwareTrackedJobFake::$lastBuilder = null;

        $job = new class extends \Junges\TrackableJobs\TrackableJob {
            public function __construct()
            {
            }

            protected function trackedJobModel(): string
            {
                return WriterAwareTrackedJobFake::class;
            }
        };

        $job->trackedJobId = 'tracked-job-uuid';
        $job->trackedJob();

        $builder = WriterAwareTrackedJobFake::$lastBuilder;
        $this->assertNotNull($builder);
        $this->assertTrue($builder->useWritePdoCalled);
        $this->assertSame('tracked-job-uuid', $builder->lastFindOrFailId);
    }

    public function test_it_retries_when_existing_tracked_job_is_temporarily_not_visible(): void
    {
        RetryingWriterAwareTrackedJobFake::$lastBuilder = null;
        RetryingWriterAwareTrackedJobFake::$failuresBeforeSuccess = 2;

        $job = new class extends \Junges\TrackableJobs\TrackableJob {
            public function __construct()
            {
            }

            protected function trackedJobModel(): string
            {
                return RetryingWriterAwareTrackedJobFake::class;
            }
        };

        $job->trackedJobId = 'tracked-job-uuid';
        $job->trackedJob();

        $builder = RetryingWriterAwareTrackedJobFake::$lastBuilder;
        $this->assertNotNull($builder);
        $this->assertTrue($builder->useWritePdoCalled);
        $this->assertSame(3, $builder->findOrFailCalls);
    }

    public function test_it_throws_original_exception_when_existing_tracked_job_stays_missing_after_retries(): void
    {
        RetryingWriterAwareTrackedJobFake::$lastBuilder = null;
        RetryingWriterAwareTrackedJobFake::$failuresBeforeSuccess = 99;

        $job = new class extends \Junges\TrackableJobs\TrackableJob {
            public function __construct()
            {
            }

            protected function trackedJobModel(): string
            {
                return RetryingWriterAwareTrackedJobFake::class;
            }
        };

        $job->trackedJobId = 'tracked-job-uuid';

        try {
            $job->trackedJob();
            $this->fail('Expected ModelNotFoundException was not thrown.');
        } catch (ModelNotFoundException $exception) {
            $builder = RetryingWriterAwareTrackedJobFake::$lastBuilder;
            $this->assertNotNull($builder);
            $this->assertTrue($builder->useWritePdoCalled);
            $this->assertSame(3, $builder->findOrFailCalls);
            $this->assertStringContainsString(RetryingWriterAwareTrackedJobFake::class, $exception->getMessage());
        }
    }

    public function test_it_throws_exception_if_finding_by_uuid(): void
    {
        $this->expectException(UuidNotConfiguredException::class);

        TrackedJob::findByUuid(Str::uuid()->toString());
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

    #[Test]
    public function it_tracks_queue_name_when_job_is_dispatched(): void
    {
        $job = (new TestJob())->onQueue('custom-queue');

        app(Dispatcher::class)->dispatch($job);

        $trackedJob = TrackedJob::first();

        $this->assertNotNull($trackedJob);
        $this->assertEquals('custom-queue', $trackedJob->queue);
        $this->assertEquals(TrackedJobStatus::Queued, $trackedJob->status);
    }
}
