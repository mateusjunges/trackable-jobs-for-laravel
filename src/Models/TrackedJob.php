<?php

namespace Junges\TrackableJobs\Models;

use Database\Factories\TrackedJobFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Junges\TrackableJobs\Concerns\HasUuid;
use Junges\TrackableJobs\Contracts\TrackableJobContract;

/**
 * @package Junges\TrackableJobs\Models
 * @property string|null $uuid
 * @property int $trackable_id
 * @property string $trackable_type
 * @property string $name
 * @property string $status
 * @property string|null $output
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $finished_at
 * @mixin Builder
 */
class TrackedJob extends Model implements TrackableJobContract
{
    use HasFactory;
    use HasUuid;
    use Prunable;

    const STATUS_QUEUED = 'queued';
    const STATUS_RETRYING = 'retrying';
    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';
    const STATUS_FAILED = 'failed';

    const STATUSES = [
        self::STATUS_QUEUED,
        self::STATUS_RETRYING,
        self::STATUS_STARTED,
        self::STATUS_FINISHED,
        self::STATUS_FAILED,
    ];

    protected $table = '';
    protected $keyType = 'int';

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

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

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

    public function prunable(): Builder
    {
        if (is_null(config('trackable-jobs.prunable_after'))) {
            return static::query()->where('id', null);
        }

        $query = static::where('created_at', '<=', now()->subDays(config('trackable-jobs.prunable_after')));
        assert($query instanceof Builder);

        return $query;
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo('trackable', 'trackable_type', 'trackable_id');
    }

    public function markAsStarted(): bool
    {
        return $this->update([
            'status' => static::STATUS_STARTED,
            'started_at' => now(),
        ]);
    }

    public function markAsQueued(): bool
    {
        return $this->update([
            'status' => static::STATUS_QUEUED,
        ]);
    }

    public function markAsRetrying(): bool
    {
        return $this->update([
            'status' => static::STATUS_RETRYING,
        ]);
    }

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

    public function setOutput(string $output): bool
    {
        return $this->update([
            'output' => $output,
        ]);
    }

    /** Whether the job has already started. */
    public function hasStarted(): bool
    {
        return ! empty($this->started_at);
    }

    /**
     * Get the duration of the job, in human diff.
     *
     * @throws \Exception
     */
    public function getDurationAttribute(): string
    {
        if (! $this->hasStarted()) {
            return '';
        }

        return ($this->finished_at ?? now())
            ->diffAsCarbonInterval($this->started_at)
            ->forHumans(['short' => true]);
    }

    protected static function newFactory(): Factory
    {
        return new TrackedJobFactory();
    }
}
