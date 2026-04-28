<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Support\Fakes;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Junges\TrackableJobs\Contracts\TrackableJobContract;

final class RetryingWriterAwareTrackedJobFake implements TrackableJobContract
{
    public static int $failuresBeforeSuccess = 0;

    public static ?RetryingWriterAwareTrackedJobBuilderFake $lastBuilder = null;

    public static function query(): RetryingWriterAwareTrackedJobBuilderFake
    {
        if (static::$lastBuilder === null) {
            static::$lastBuilder = new RetryingWriterAwareTrackedJobBuilderFake();
        }

        return static::$lastBuilder;
    }

    public function trackable(): MorphTo
    {
        throw new \BadMethodCallException('Not used in test.');
    }

    public function markAsQueued(string|int|null $jobId = null): bool
    {
        return true;
    }

    public function markAsStarted(): bool
    {
        return true;
    }

    public function markAsFinished(?string $message = null): bool
    {
        return true;
    }

    public function markAsRetrying(int $attempts): bool
    {
        return true;
    }

    public function markAsFailed(?string $exception = null): bool
    {
        return true;
    }

    public function setOutput(string $output): bool
    {
        return true;
    }
}
