<?php

declare(strict_types=1);

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

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(function (UserUuid $model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    /**
     * @param string $uuid
     * @return UserUuid|null
     */
    public static function findByUuid(string $uuid): ?UserUuid
    {
        return static::where('uuid', $uuid)->first();
    }
}
