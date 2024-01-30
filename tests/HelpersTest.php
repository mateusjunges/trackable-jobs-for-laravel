<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\TestJob;
use Junges\TrackableJobs\Tests\Jobs\TestJobWithoutModel;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_dispatch_jobs_without_tracking(): void
    {
        Bus::fake();

        $this->assertCount(0, TrackedJob::all());

        dispatchWithoutTracking(TestJob::class);

        Bus::assertDispatched(TestJob::class);

        $this->assertCount(0, TrackedJob::all());

        $this->artisan('queue:work --once')->assertExitCode(0);

        dispatchWithoutTracking(TestJob::class, User::query()->first(), false);

        Bus::assertDispatched(TestJob::class);

        $this->assertCount(0, TrackedJob::all());

        dispatchWithoutTracking(TestJobWithoutModel::class);

        Bus::assertDispatched(TestJob::class);

        $this->assertCount(0, TrackedJob::all());

        Bus::assertDispatched(TestJobWithoutModel::class);
    }
}
