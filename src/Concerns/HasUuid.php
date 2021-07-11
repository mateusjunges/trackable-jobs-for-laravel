<?php

declare(strict_types=1);

namespace Junges\TrackableJobs\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Junges\TrackableJobs\Exceptions\UuidNotConfiguredException;

/**
 * Trait HasUuid.
 * @package Junges\TrackableJobs\Concerns
 */
trait HasUuid
{
    /**
     * @return void
     */
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (config('trackable-jobs.using_uuid', false)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    /**
     * @param string $uuid
     *
     * @return Model|null
     * @throws UuidNotConfiguredException
     */
    public static function findByUuid(string $uuid): ?Model
    {
        if (!config('trackable-jobs.using_uuid', false)) {
            throw UuidNotConfiguredException::youAreNotUsingUuid();
        }

        return static::where('uuid', $uuid)->first();
    }
}
