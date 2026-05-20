<?php declare(strict_types=1);

namespace Junges\TrackableJobs;

use Illuminate\Queue\InteractsWithQueue;
use Junges\TrackableJobs\Contracts\TrackableContract;
use Junges\TrackableJobs\Enums\TrackedJobStatus;
use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use Throwable;

abstract class TrackableJob implements TrackableContract
{
    use InteractsWithQueue;

    public ?TrackedJob $trackedJob = null;

    public function __construct()
    {
        $this->trackedJob = TrackedJob::create([
            'trackable_id' => $this->trackableKey(),
            'status' => TrackedJobStatus::Created,
            'attempts' => 0,
            'trackable_type' => $this->trackableType(),
            'name' => static::class,
        ]);
    }

    public function trackableKey(): ?string
    {
        return null;
    }

    public function trackableType(): ?string
    {
        return null;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new TrackedJobMiddleware()];
    }

    public function failed(Throwable $exception): void
    {
        $message = $exception->getMessage();

        $this->trackedJob->markAsFailed($message);
    }
}
