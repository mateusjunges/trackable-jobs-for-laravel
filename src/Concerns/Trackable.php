<?php

namespace Junges\TrackableJobs\Concerns;

use Illuminate\Database\Eloquent\Model;
use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use Throwable;

trait Trackable
{
    public ?Model $model;

    public TrackedJob $trackedJob;

    public function __construct($model)
    {
        $this->model = $model;

        $this->trackedJob = TrackedJob::create([
            'trackable_id' => $this->model->id ?? $this->model->uuid,
            'trackable_type' => $this->model->getMorphClass(),
            'name' => class_basename(static::class),
        ]);
    }

    public function middleware(): array
    {
        return [new TrackedJobMiddleware()];
    }

    public function failed(Throwable $exception)
    {
        $message = $exception->getMessage();

        $this->trackedJob->markAsFailed($message);
    }
}
