<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Context;
use Junges\TrackableJobs\TrackableJob;

class ContextAwareTestJob extends TrackableJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): array
    {
        return [
            'trackable_job_id' => Context::get('trackable_job_id'),
        ];
    }
}
