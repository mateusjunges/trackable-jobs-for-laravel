<?php

namespace Junges\TrackableJobs\Traits;

use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use Throwable;

trait Trackable
{
    public $model;

    public $trackedJob;

    public function __construct($model)
    {
        $this->model = $model;

        $this->trackedJob = TrackedJob::create([
            'trackable_id'   => $this->model->id,
            'trackable_type' => get_class($this->model),
            'name'           => class_basename(static::class),
        ]);
    }

    public function middleware()
    {
        return [new TrackedJobMiddleware()];
    }

    public function failed(Throwable $exception)
    {
        $message = $exception->getMessage();

        $this->trackedJob->markAsFailed($message);
    }
}
