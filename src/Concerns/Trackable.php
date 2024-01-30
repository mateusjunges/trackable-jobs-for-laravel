<?php

namespace Junges\TrackableJobs\Concerns;

use Illuminate\Bus\UniqueLock;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\PendingDispatch;
use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use Throwable;

trait Trackable
{
    public ?TrackedJob $trackedJob = null;

    public static bool $shouldBeTracked = true;

    public function __construct()
    {
        if (! static::$shouldBeTracked) {
            return;
        }

        // If this job implements the ShouldBeUnique, and it is a duplicate, laravel
        // will discard the job from the queue. Therefore, there is no need to
        // continue tracking this job, and we can safely return early.
        if ($this instanceof ShouldBeUnique && ! $this->isUniqueJobAndCanAcquireLock()) {
            return;
        }

        $this->trackedJob = TrackedJob::create([
            'trackable_id' => $this->trackableKey(),
            'status' => null,
            'trackable_type' => $this->trackableType(),
            'name' => static::class,
        ]);
    }

    private function isUniqueJobAndCanAcquireLock(): bool
    {
        return (new UniqueLock(Container::getInstance()->make(Cache::class)))->acquire($this);
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
