<?php declare(strict_types=1);

namespace Junges\TrackableJobs;

use Junges\TrackableJobs\Contracts\TrackableContract;
use Junges\TrackableJobs\Contracts\TrackableJobContract;
use Junges\TrackableJobs\Enums\TrackedJobStatus;
use Junges\TrackableJobs\Exceptions\TrackableJobsException;
use Junges\TrackableJobs\Jobs\Middleware\TrackedJobMiddleware;
use Junges\TrackableJobs\Models\TrackedJob;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

/**
 * @template TTrackedJob of \Junges\TrackableJobs\Contracts\TrackableJobContract
 * @property-read TTrackedJob $trackedJob
 */
abstract class TrackableJob implements TrackableContract
{
    public string|int|null $trackedJobId = null;

    public function __construct()
    {
        $this->ensureTrackedJobExists();
    }

    public function __get(string $name): mixed
    {
        if ($name === 'trackedJob') {
            return $this->trackedJob();
        }

        trigger_error(sprintf('Undefined property: %s::$%s', static::class, $name), E_USER_NOTICE);

        return null;
    }

    public function __isset(string $name): bool
    {
        if ($name === 'trackedJob') {
            return $this->trackedJobId !== null;
        }

        return false;
    }

    public function trackableKey(): ?string
    {
        return null;
    }

    public function trackableType(): ?string
    {
        return null;
    }

    /** Get the middleware the job should pass through. */
    public function middleware(): array
    {
        return [new TrackedJobMiddleware()];
    }

    /**
     * @return TTrackedJob
     */
    public function trackedJob(): TrackableJobContract
    {
        return $this->ensureTrackedJobExists();
    }

    public function failed(Throwable $exception): void
    {
        $message = $exception->getMessage();

        $this->trackedJob()->markAsFailed($message);
    }

    /**
     * @return class-string<TTrackedJob>
     */
    protected function trackedJobModel(): string
    {
        /** @var class-string<TTrackedJob> $trackedJobModel */
        $trackedJobModel = config('trackable-jobs.model', TrackedJob::class);

        return $trackedJobModel;
    }

    protected function withTrackingContext(callable $callback): mixed
    {
        $resolver = config('trackable-jobs.tracking_context');

        if ($resolver === null) {
            return $callback();
        }

        if (! is_callable($resolver)) {
            throw new TrackableJobsException(
                'Invalid configuration for "trackable-jobs.tracking_context": expected callable|null.'
            );
        }

        return $resolver($this, $callback);
    }

    /**
     * @return TTrackedJob
     */
    protected function ensureTrackedJobExists(): TrackableJobContract
    {
        if ($this->trackedJobId !== null) {
            $backoffMicroseconds = [25_000, 50_000, 100_000];
            $firstException = null;

            for ($attempt = 0; $attempt < count($backoffMicroseconds); $attempt++) {
                try {
                    $trackedJob = $this->withTrackingContext(
                        fn () => $this->trackedJobModel()::query()->useWritePdo()->findOrFail($this->trackedJobId)
                    );
                    assert($trackedJob instanceof TrackableJobContract);

                    return $trackedJob;
                } catch (ModelNotFoundException $exception) {
                    $firstException ??= $exception;
                    usleep($backoffMicroseconds[$attempt]);
                }
            }

            assert($firstException instanceof ModelNotFoundException);

            throw $firstException;
        }

        $trackedJob = $this->withTrackingContext(fn () => $this->trackedJobModel()::create([
            'trackable_id' => $this->trackableKey(),
            'status' => TrackedJobStatus::Created,
            'attempts' => 0,
            'trackable_type' => $this->trackableType(),
            'name' => static::class,
        ]));
        assert($trackedJob instanceof TrackableJobContract);

        $this->trackedJobId = $trackedJob->getKey();

        return $trackedJob;
    }
}
