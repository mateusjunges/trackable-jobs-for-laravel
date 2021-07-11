<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Traits;

use Illuminate\Database\Eloquent\Model;
use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use Throwable;

/**
 * Trait TrackableWithoutModel
 * @package Junges\TrackableJobs\Traits
 */
trait TrackableAuto
{
    /**
     * @var TrackedJob|Model
     */
    public TrackedJob $trackedJob;

    /**
     * Trackable constructor.
     */
    public function __construct()
    {
        $this->trackedJob = TrackedJob::create([
            'trackable_id'   => $this->getTrackableId(),
            'trackable_type' => static::class,
            'name'           => class_basename(static::class),
        ]);
    }

    /**
     * @return TrackedJobMiddleware[]
     */
    public function middleware(): array
    {
        return [new TrackedJobMiddleware()];
    }

    /**
     * @param Throwable $exception
     *
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $this->trackedJob->markAsFailed(
            $exception->getMessage()
        );
    }

    /**
     * Return id for trackable.
     *
     * @return int
     */
    protected function getTrackableId(): int
    {
        // Note: null + 1 = 1
        return TrackedJob::max('trackable_id') + 1;
    }
}
