<?php

namespace Junges\TrackableJobs\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/***
 * Class TrackedJob
 * @package Junges\TrackableJobs\Models
 * @mixin Builder
 */
class TrackedJob extends Model
{
    const STATUS_STARTED = "started";
    const STATUS_FINISHED = "finished";
    const STATUS_FAILED = "failed";

    protected $table = "";

    protected $fillable = [
        'trackable_id',
        'trackable_type',
        'name',
        'status',
        'output',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config("trackable-jobs.tables.tracked_jobs", "tracked_jobs"));
    }

    public function trackable() : MorphTo
    {
        return $this->morphTo('trackable');
    }

    public function markAsStarted() : bool
    {
        return $this->update([
            'status' => static::STATUS_STARTED,
            'started_at' => now(),
        ]);
    }

    public function markAsFinished(string $message = "") : bool
    {
        $this->setOutput($message);

        return $this->update([
            'status' => static::STATUS_FINISHED,
            'finished_at' => now(),
        ]);
    }

    public function markAsFailed(string $exception = "") : bool
    {
        $this->setOutput($exception);

        return $this->update([
            'status' => static::STATUS_FAILED,
            'finished_at' => now(),
        ]);
    }

    public function setOutput(string $output) : bool
    {
        return $this->update([
            'output' => $output
        ]);
    }


}