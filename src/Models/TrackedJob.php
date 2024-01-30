<?php

namespace Junges\TrackableJobs\Models;

use Database\Factories\TrackedJobFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Junges\TrackableJobs\Concerns\HasUuid;
use Junges\TrackableJobs\Contracts\TrackableJobContract;
use Junges\TrackableJobs\Enums\TrackedJobStatus;

/**
 * @package Junges\TrackableJobs\Models
 * @property string|null uuid
 * @property int trackable_id
 * @property string trackable_type
 * @property string name
 * @property string status
 * @property string|null output
 * @property \Illuminate\Support\Carbon|null started_at
 * @property \Illuminate\Support\Carbon|null finished_at
 * @mixin Builder
 */
class TrackedJob extends Model implements TrackableJobContract
{
    use HasFactory;
    use HasUuid;
    use Prunable;

    protected $table = '';
    protected $keyType = 'int';

    protected $fillable = [
        'uuid',
        'trackable_id',
        'trackable_type',
        'name',
        'job_id',
        'status',
        'attempts',
        'output',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'attempts' => 'integer',
        'status' => TrackedJobStatus::class,
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

    /** Determine which tracked jobs should be pruned. */
    public function prunable(): Builder
    {
        if (is_null(config('trackable-jobs.prunable_after'))) {
            return static::query()->where('id', null);
        }

        $query = static::where('created_at', '<=', now()->subDays(config('trackable-jobs.prunable_after')));
        assert($query instanceof Builder);

        return $query;
    }

    /** Return the model related to the tracked job. */
    public function trackable(): MorphTo
    {
        return $this->morphTo('trackable', 'trackable_type', 'trackable_id');
    }

    /** Mark the job as started. */
    public function markAsStarted(): bool
    {
        return $this->update([
            'status' => TrackedJobStatus::STARTED->value,
            'started_at' => now(),
        ]);
    }

    public function markAsQueued(string|int|null $jobId = null): bool
    {
        return $this->update([
            'status' => TrackedJobStatus::QUEUED->value,
            'job_id' => $jobId,
        ]);
    }

    public function markAsRetrying(int $attempts): bool
    {
        return $this->update([
            'status' => TrackedJobStatus::RETRYING->value,
            'attempts' => $attempts,
        ]);
    }

    public function markAsFinished(string $message = null): bool
    {
        if ($message) {
            $this->setOutput($message);
        }

        return $this->update([
            'status' => TrackedJobStatus::FINISHED->value,
            'finished_at' => now(),
        ]);
    }

    /** Mark the job as finished with error. */
    public function markAsFailed(string $exception = null): bool
    {
        if ($exception) {
            $this->setOutput($exception);
        }

        return $this->update([
            'status' => TrackedJobStatus::FAILED->value,
            'finished_at' => now(),
        ]);
    }

    /** Saves the output of the job. */
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

    /** Get the duration of the job, in human diff. */
    public function duration(): Attribute
    {
        return Attribute::make(
            get: function () : string {
                if (! $this->hasStarted()) {
                    return '';
                }

                return ($this->finished_at ?? now())
                    ->diffAsCarbonInterval($this->started_at)
                    ->forHumans(['short' => true]);
            }
        );
    }

    protected static function newFactory(): Factory
    {
        return new TrackedJobFactory();
    }
}
