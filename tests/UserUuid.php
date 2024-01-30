<?php

namespace Junges\TrackableJobs\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Junges\TrackableJobs\Concerns\HasUuid;

class UserUuid extends Model
{
    use HasUuid;
   protected $fillable = ['name', 'email'];

    public $timestamps = false;
    protected $table = 'test_users_uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $primaryKey = 'uuid';
}
