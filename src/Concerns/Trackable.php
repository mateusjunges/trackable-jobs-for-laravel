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

    public function __construct(
        public ?Model $trackable = null,
        private bool $shouldBeTracked = true
    )
    {
        if (!$shouldBeTracked)
        {
            $this->shouldBeTracked = false;
            return;
        }

        $this->trackedJob = TrackedJob::create([
            'trackable_id' => $this->trackable ? $this->trackable->id ?? $this->trackable->uuid : null,
            'trackable_type' => $this->trackable?->getMorphClass(),
            'name' => static::class,
            'status' => null,
        ]);
    }

    /** Get the middleware the job should pass through. */
    public function middleware(): array
    {
        return [new TrackedJobMiddleware()];
    }

    /**
     * Determines whether the job should be tracked or not.
     *
     * @return bool
     */
    public function shouldBeTracked(): bool
    {
        return $this->shouldBeTracked;
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
        $parameters = (new ReflectionClass(self::class))->getConstructor()->getParameters();

        if (count($parameters) === 1 && !count($arguments))
        {
            $arguments = [false];
        }

        if (count($parameters) > 1 && count($arguments) === 1)
        {
            $arguments = [...$arguments, false];
        }

        $job = new static(...$arguments);

        return new PendingDispatch($job);
    }
}
