<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Junges\TrackableJobs\Exceptions\UuidNotConfiguredException;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\Trackable\TestJob;
use Spatie\TestTime\TestTime;

/**
 * Class TrackedJobUsingUuidTest.
 */
class TrackedJobUsingUuidTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_it_stores_jobs_using_uuid(): void
    {
        TestTime::freeze();

        $job = new TestJob($this->userUuid);

        app(Dispatcher::class)->dispatch($job);

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertCount(1, TrackedJob::all());

        $this->assertTrue(
            (bool) preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
                TrackedJob::first()->uuid
            )
        );

        $job = TrackedJob::first();

        $this->assertEquals(TrackedJob::STATUS_FINISHED, $job->status);

        $this->assertEquals('1h', $job->duration);
    }

    /**
     * @return void
     */
    public function test_get_duration_returns_an_empty_string_if_the_job_has_not_started(): void
    {
        $job = new TestJob($this->userUuid);

        app(Dispatcher::class)->dispatch($job);

        $this->assertCount(1, TrackedJob::all());

        $job = TrackedJob::first();

        $this->assertEquals('', $job->duration);

        $this->assertNull($job->finished_at);
    }

    /**
     * @throws UuidNotConfiguredException
     *
     * @return void
     */
    public function test_it_can_find_tracked_jobs_by_uuid(): void
    {
        $job = new TestJob($this->userUuid);

        app(Dispatcher::class)->dispatch($job);

        TestTime::addHour();

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertCount(1, TrackedJob::all());

        $uuid = TrackedJob::first()->uuid;

        $this->assertEquals(TrackedJob::first(), TrackedJob::findByUuid($uuid));
    }

    /**
     * @throws UuidNotConfiguredException
     *
     * @return void
     */
    public function test_it_stores_the_model_uuid(): void
    {
        $job = new TestJob($this->userUuid);

        app(Dispatcher::class)->dispatch($job);

        TestTime::addHour();

        $this->artisan('queue:work --once')->assertExitCode(0);

        $this->assertCount(1, TrackedJob::all());

        $uuid = TrackedJob::first()->uuid;

        $this->assertEquals(TrackedJob::first(), TrackedJob::findByUuid($uuid));

        $this->assertEquals($this->userUuid->uuid, TrackedJob::first()->trackable_id);
    }
}
