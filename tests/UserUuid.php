<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Class User.
 *
 * @mixin Builder
 */
class UserUuid extends Authenticatable
{
    use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'test_users_uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $primaryKey = 'uuid';

    public static function boot()
    {
        parent::boot();

        static::creating(function (UserUuid $model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public static function findByUuid(string $uuid): ?UserUuid
    {
        return static::where('uuid', $uuid)->first();
    }
}
