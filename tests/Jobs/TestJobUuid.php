<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Junges\TrackableJobs\TrackableJob;
use Spatie\TestTime\TestTime;

class TestJobUuid extends TrackableJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): string
    {
        TestTime::addHour();

        return 'This is a test job';
    }

    public function trackableType(): ?string
    {
        return 'type';
    }

    public function trackableKey(): ?string
    {
        return 'uuid';
    }
}
