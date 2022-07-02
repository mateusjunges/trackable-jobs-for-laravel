<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Junges\TrackableJobs\Models\TrackedJob;
use Junges\TrackableJobs\Tests\Jobs\TestJob;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_i_can_dispatch_jobs_without_tracking()
    {
        $this->assertCount(0, TrackedJob::all());

        dispatchWithoutTracking(new TestJob(User::query()->first(), false));

        $this->assertCount(0, TrackedJob::all());

        $this->artisan('queue:work --once')->assertExitCode(0);

        dispatchWithoutTracking(TestJob::class, User::query()->first(), false);

        $this->assertCount(0, TrackedJob::all());
    }
}