<?php

namespace Junges\TrackableJobs\Exceptions;

class UuidNotConfiguredException extends TrackableJobsException
{
    public static function youAreNotUsingUuid(): self
    {
        return new static('To use this method, configure this package to use Uuid.');
    }
}
