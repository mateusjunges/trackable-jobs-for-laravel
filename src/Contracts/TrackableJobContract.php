<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Interface TrackableJobContract
 * @package Junges\TrackableJobs\Contracts
 */
interface TrackableJobContract
{
    /**
     * @return MorphTo
     */
    public function trackable(): MorphTo;

    /**
     * @return bool
     */
    public function markAsStarted(): bool;

    /**
     * @param string|null $message
     * @return bool
     */
    public function markAsFinished(string $message = null): bool;

    /**
     * @param string|null $exception
     * @return bool
     */
    public function markAsFailed(string $exception = null): bool;

    /**
     * @param string $output
     * @return bool
     */
    public function setOutput(string $output): bool;
}
