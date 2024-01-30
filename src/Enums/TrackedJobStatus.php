<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Enums;

enum TrackedJobStatus: string
{
    case QUEUED = 'queued';
    case RETRYING = 'retrying';
    case STARTED = 'started';
    case FINISHED = 'finished';
    case FAILED = 'failed';
}
