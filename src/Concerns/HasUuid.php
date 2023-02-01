<?php

namespace Junges\TrackableJobs\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Junges\TrackableJobs\Exceptions\UuidNotConfiguredException;

/** @mixin Model */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (! config('trackable-jobs.using_uuid', false)) {
                return;
            }
            $model->uuid = (string) Str::uuid();
        });
    }

    /** @throws UuidNotConfiguredException */
    public static function findByUuid(string $uuid): ?Model
    {
        if (! config('trackable-jobs.using_uuid', false)) {
            throw UuidNotConfiguredException::youAreNotUsingUuid();
        }

        return static::where('uuid', $uuid)->first();
    }
}
