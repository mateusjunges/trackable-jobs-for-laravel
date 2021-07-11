<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Traits;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use Throwable;

/**
 * Trait Trackable.
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
     *
     * @param mixed ...$args
     */
    public function __construct(...$args)
    {
        if (!count($args) || !$args[0] instanceof Model) {
            throw new InvalidArgumentException('When you use trackable. You must pass first arg model');
        }

        $this->model = $args[0];
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
     *
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $message = $exception->getMessage();

        $this->trackedJob->markAsFailed($message);
    }
}
