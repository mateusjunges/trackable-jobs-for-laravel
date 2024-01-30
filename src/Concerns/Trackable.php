<?php

namespace Junges\TrackableJobs\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use ReflectionClass;
use Throwable;

trait Trackable
{
    public ?TrackedJob $trackedJob = null;

    public static bool $shouldBeTracked = true;

    public function __construct()
    {
        if (!static::$shouldBeTracked) {
            return;
        }

        $this->trackedJob = TrackedJob::create([
            'trackable_id' => $this->trackableKey(),
            'trackable_type' => $this->trackableType(),
            'name' => static::class,
        ]);
    }

    protected function trackableKey(): ?string
    {
        return null;
    }

    protected function trackableType(): ?string
    {
        return null;
    }

    /** Get the middleware the job should pass through. */
    public function middleware(): array
    {
        return [new TrackedJobMiddleware()];
    }

    /** Determines whether the job should be tracked or not. */
    public function shouldBeTracked(): bool
    {
        return static::$shouldBeTracked;
    }

    public function failed(Throwable $exception): void
    {
        $message = $exception->getMessage();

        $this->trackedJob->markAsFailed($message);
    }

    /**
     * Dispatches the job without tracking.
     *
     * @param ...$arguments
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public static function dispatchWithoutTracking(...$arguments): PendingDispatch
    {
        static::$shouldBeTracked = false;

        $job = new static(...$arguments);

        static::$shouldBeTracked = true;

        return new PendingDispatch($job);
    }
}
