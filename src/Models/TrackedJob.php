<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Junges\TrackableJobs\Concerns\HasUuid;
use Junges\TrackableJobs\Contracts\TrackableJobContract;

/***
 * Class TrackedJob
 * @package Junges\TrackableJobs\Models
 * @mixin Builder
 */
class TrackedJob extends Model implements TrackableJobContract
{
    use HasUuid;

    const STATUS_QUEUED = 'queued';
    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';
    const STATUS_FAILED = 'failed';

    /**
     * Getting from config.
     *
     * @var string
     */
    protected $table = '';

    /**
     * Getting from config.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'trackable_id',
        'trackable_type',
        'name',
        'status',
        'output',
        'started_at',
        'finished_at',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * TrackedJob constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('trackable-jobs.tables.tracked_jobs', 'tracked_jobs'));

        if (config('trackable-jobs.using_uuid', false)) {
            $this->setKeyType('string');
            $this->primaryKey = 'uuid';
            $this->setIncrementing(false);
        }
    }

    /**
     * @return MorphTo
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo('trackable', 'trackable_type', 'trackable_id');
    }

    /**
     * @return bool
     */
    public function markAsStarted(): bool
    {
        return $this->update([
            'status' => static::STATUS_STARTED,
            'started_at' => now(),
        ]);
    }

    /**
     * @param string|null $message
     * @return bool
     */
    public function markAsFinished(string $message = null): bool
    {
        if ($message) {
            $this->setOutput($message);
        }

        return $this->update([
            'status' => static::STATUS_FINISHED,
            'finished_at' => now(),
        ]);
    }

    /**
     * @param string|null $exception
     * @return bool
     */
    public function markAsFailed(string $exception = null): bool
    {
        if ($exception) {
            $this->setOutput($exception);
        }

        return $this->update([
            'status' => static::STATUS_FAILED,
            'finished_at' => now(),
        ]);
    }

    /**
     * @param string $output
     * @return bool
     */
    public function setOutput(string $output): bool
    {
        return $this->update([
            'output' => $output,
        ]);
    }

    /**
     * Whether the job has already started.
     *
     * @return bool
     */
    public function hasStarted(): bool
    {
        return !empty($this->started_at);
    }

    /**
     * Get the duration of the job, in human diff.
     *
     * @return string
     * @throws Exception
     */
    public function getDurationAttribute(): string
    {
        if (!$this->hasStarted()) {
            return '';
        }

        return ($this->finished_at ?? now())
            ->diffAsCarbonInterval($this->started_at)
            ->forHumans(['short' => true]);
    }
}
