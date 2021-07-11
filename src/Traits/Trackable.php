<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Traits;

use Illuminate\Database\Eloquent\Model;
use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use Throwable;

/**
 * Trait Trackable
 * @package Junges\TrackableJobs\Traits
 */
trait Trackable
{
    /**
     * @var Model|null
     */
    public ?Model $model;

    /**
     * @var TrackedJob|Model
     */
    public TrackedJob $trackedJob;

    /**
     * Trackable constructor.
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;

        $this->trackedJob = TrackedJob::create([
            'trackable_id'   => $this->model->id ?? $this->model->uuid,
            'trackable_type' => get_class($this->model),
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
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $message = $exception->getMessage();

        $this->trackedJob->markAsFailed($message);
    }
}
