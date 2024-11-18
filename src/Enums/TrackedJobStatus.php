<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Enums;

enum TrackedJobStatus: string
{
    case Created = 'created';
    case Queued = 'queued';
    case Retrying = 'retrying';
    case Started = 'started';
    case Finished = 'finished';
    case Failed = 'failed';
}
