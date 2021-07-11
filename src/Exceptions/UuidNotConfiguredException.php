<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Exceptions;

use Exception;

/**
 * Class UuidNotConfiguredException.
 */
class UuidNotConfiguredException extends Exception
{
    public static function youAreNotUsingUuid(): self
    {
        return new static('To use this method, configure this package to use Uuid.');
    }
}
